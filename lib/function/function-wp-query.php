<?php

namespace mikuclub;

use mikuclub\constant\Category;
use mikuclub\constant\Expired;
use mikuclub\constant\Option_Meta;
use mikuclub\constant\Page_Type;
use mikuclub\constant\Post_Meta;
use mikuclub\Post_Query;
use mikuclub\constant\Post_Status;
use mikuclub\constant\Post_Type;
use WP_Query;


/*
影响WORDPRESS主查询相关的函数
*/



/**
 * 修改WORDPRESS主查询的变量
 *
 * @param WP_Query $wp_query
 * @return void
 */
function set_wp_query_custom_query_var($wp_query)
{

    //只在主查询中生效
    if ($wp_query->is_main_query())
    {
        //排除置顶文章
        set_query_var(Post_Query::IGNORE_STICKY_POSTS, 1);

        //修改默认排序
        /*
		if ( ! get_query_var( Post_Query::ORDERBY ) ) {
			//使用最后修改时间作为默认排序
			set_query_var( Post_Query::ORDERBY, 'modified' );
		}*/

        //如果存在自定义 分类变量
        $main_cat =  get_query_var(Post_Query::CUSTOM_MAIN_CAT);
        $sub_cat =  get_query_var(Post_Query::CUSTOM_SUB_CAT);
        if ($main_cat)
        {
            //替换默认查询分类ID
            set_query_var(Post_Query::CAT, $main_cat);
        }
        else if ($sub_cat)
        {
            //替换默认查询分类ID
            set_query_var(Post_Query::CAT, $sub_cat);
        }

        //不是内容页
        if (!is_page())
        {
            $custom_orderby = get_query_var(Post_Query::CUSTOM_ORDERBY);

            //有自定义排序变量 的时候
            if ($custom_orderby)
            {
                set_query_var(Post_Query::META_KEY, $custom_orderby);
                set_query_var(Post_Query::ORDERBY, 'meta_value_num');
            }

            //如果有设置自定义日期范围
            $custom_order_data_range = get_query_var(Post_Query::CUSTOM_ORDER_DATA_RANGE);
            if ($custom_order_data_range)
            {

                set_query_var(
                    Post_Query::DATE_QUERY,
                    [
                        [
                            'after' => $custom_order_data_range . ' month ago'
                        ]
                    ]
                );
            }
        }

        //如果是作者页
        if (is_author())
        {
            $custom_search = get_query_var(Post_Query::CUSTOM_SEARCH);
            //如果有自定义搜索
            if ($custom_search)
            {
                //替换默认搜索
                set_query_var(Post_Query::SEARCH, $custom_search);
            }
        }


        //如果是搜索页
        if (is_search())
        {
            //限制搜索结果的文章类型
            set_query_var(Post_Query::POST_TYPE, Post_Type::POST);
        }

        //如果是主页
        if (is_home())
        {
            $page_type = get_query_var(Post_Query::CUSTOM_PAGE_TYPE);
            //如果未说明类型 或者 类型为主页 移除成人区分类
            if (empty($page_type) || $page_type === Page_Type::HOME)
            {
                set_query_var(Post_Query::CAT, Category::NO_ADULT_CATEGORY);
            }
        }

        //如果用户未登陆 并且不是主页和内容页 
        if (!is_user_logged_in() && !is_home() && !is_singular())
        {
            //获取现有cat查询参数
            $cat = get_query_var(Post_Query::CAT);
            //如果不是空, 在结尾追加
            if ($cat)
            {
                $cat .= ',' . Category::NO_ADULT_CATEGORY;
            }
            //否则重新设置
            else
            {
                $cat = Category::NO_ADULT_CATEGORY;
            }

            set_query_var(Post_Query::CAT, $cat);
        }
    }
}
