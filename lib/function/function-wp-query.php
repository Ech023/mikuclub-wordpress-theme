<?php

namespace mikuclub;

use mikuclub\constant\Category;
use mikuclub\constant\Config;
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
 * 修正文章列表的请求参数
 *
 * @param array<string, mixed> $query_vars
 * @return array<string, mixed>
 */
function set_post_list_query_vars($query_vars)
{
    //获取页面参数
    $page_type = $query_vars[Post_Query::CUSTOM_PAGE_TYPE] ?? Page_Type::get_current_type();

    //排除置顶文章
    $query_vars[Post_Query::IGNORE_STICKY_POSTS] = 1;

    /*设置默认的文章显示数量*/
    $query_vars[Post_Query::POSTS_PER_PAGE] = Config::POST_LIST_LENGTH;
    //$query_vars['orderby']        = 'modified'; //使用最后修改时间作为默认排序


    //如果有设置自定义排序
    $custom_orderby = $query_vars[Post_Query::CUSTOM_ORDERBY] ?? 'modified';
    if ($custom_orderby)
    {
        //如果是默认的可选排序
        if (in_array($custom_orderby, ['modified', 'date']))
        {
            $query_vars[Post_Query::ORDERBY] = $custom_orderby;
        }
        else
        {
            //确保存在
            $query_vars[Post_Query::META_QUERY] = $query_vars[Post_Query::META_QUERY] ?? [];
            $query_vars[Post_Query::META_QUERY][] = [
                'key' => $custom_orderby,
                'compare' => 'EXISTS',
                'type'    => 'NUMERIC',
            ];
            $query_vars[Post_Query::ORDERBY]  = [$custom_orderby => 'DESC'];
        }
    }

    //如果有设置自定义日期范围
    $custom_order_data_range = $query_vars[Post_Query::CUSTOM_ORDER_DATA_RANGE] ?? '';
    //有设置自定义日期范围
    if ($custom_order_data_range)
    {
        $query_vars[Post_Query::DATE_QUERY] =  [
            [
                'column' => 'post_modified',
                'after' => '-' . $custom_order_data_range . ' days',
            ]
        ];
    }

    $custom_down_type = $query_vars[Post_Query::CUSTOM_DOWN_TYPE] ?? '';
    //有设置下载过滤
    if ($custom_down_type)
    {
        //通过下载方式数组进行过滤
        $query_vars[Post_Query::META_QUERY] = $query_vars[Post_Query::META_QUERY] ?? [];
        $query_vars[Post_Query::META_QUERY][] = [
            'key' => Post_Meta::POST_ARRAY_DOWN_TYPE,
            'value'   => $custom_down_type,
            'compare' => 'LIKE',
        ];
    }


    //如果页面参数是 作者页
    if ($page_type === Page_Type::AUTHOR)
    {
        $custom_search = $query_vars[Post_Query::CUSTOM_SEARCH] ?? '';
        //如果有自定义搜索
        if ($custom_search)
        {
            //替换默认搜索
            $query_vars[Post_Query::SEARCH] = $custom_search;
        }
    }

    //如果页面参数是搜索页
    if ($page_type === Page_Type::SEARCH)
    {
        $query_vars[Post_Query::POST_TYPE] = Post_Type::POST;
    }

    //如果页面参数是 主页
    if ($page_type === Page_Type::HOME)
    {
        //自动排除成人分类
        $query_vars[Post_Query::CAT] = Category::NO_ADULT_CATEGORY;
    }
    //如果不是主页 并且用户未登陆 
    else if (!is_user_logged_in())
    {
        //获取现有cat查询参数
        $cat = $query_vars[Post_Query::CAT] ?? '';
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

        $query_vars[Post_Query::CAT] = $cat;
    }

    return $query_vars;
}


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
        //修正请求参数
        $array_query_vars = set_post_list_query_vars($wp_query->query);
        foreach ($array_query_vars as $key => $var)
        {
            set_query_var($key, $var);
        }

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


/**
 * 获取主查询实例里的用到的请求参数数组, 并且排除无数值的参数
 *
 * @return array<string,mixed>
 */
function get_array_main_query_vars_filtered()
{

    global $wp_query;

    $array_query_var_filtered = array_filter($wp_query->query_vars, function ($value)
    {
        $result = true;
        //过滤空字符串
        if ($value === '')
        {
            $result = false;
        }
        //过滤空数组
        else if (is_array($value) && empty($value))
        {
            $result = false;
        }
        //过滤 bool数值
        if (is_bool($value))
        {
            $result = false;
        }

        return $result;
    });

    return $array_query_var_filtered;
}
