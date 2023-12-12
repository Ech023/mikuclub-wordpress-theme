<?php

namespace mikuclub;

use Exception;
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
    //限制文章类型
    $query_vars[Post_Query::POST_TYPE] = Post_Type::POST;

    //禁止pagename参数
    unset($query_vars[Post_Query::PAGENAME]);




    /*设置默认的文章显示数量*/
    $query_vars[Post_Query::POSTS_PER_PAGE] = Config::POST_LIST_LENGTH;
    //$query_vars['orderby']        = 'modified'; //使用最后修改时间作为默认排序

    //如果存在自定义 分类变量
    $custom_cat = $query_vars[Post_Query::CUSTOM_CAT] ?? 0;
    if ($custom_cat)
    {
        //设置分类ID
        $query_vars[Post_Query::CAT] = $custom_cat;
    }

    //如果有设置自定义排序
    $custom_orderby = $query_vars[Post_Query::CUSTOM_ORDERBY] ?? 'modified';
    if ($custom_orderby)
    {
        //如果是默认的可选排序
        if (in_array($custom_orderby, ['modified', 'date', 'post__in']))
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

    $custom_post_array_down_type = $query_vars[Post_Query::CUSTOM_POST_ARRAY_DOWN_TYPE] ?? [];
    //有设置下载过滤数组
    if (is_array($custom_post_array_down_type) && count($custom_post_array_down_type) > 0)
    {
        // 如果还不存在, 初始化 META_QUERY数组 
        $query_vars[Post_Query::META_QUERY] = $query_vars[Post_Query::META_QUERY] ?? [];

        $sub_meta_query = [
            'relation' => 'OR',
        ];
        foreach ($custom_post_array_down_type as $type)
        {
            $sub_meta_query[] =  [
                'key' => Post_Meta::POST_ARRAY_DOWN_TYPE,
                'value'   => $type,
                'compare' => 'LIKE',
            ];
        }

        $query_vars[Post_Query::META_QUERY][] = $sub_meta_query;
    }

    //如果有收藏文章参数
    $custom_only_post_favorite = $query_vars[Post_Query::CUSTOM_ONLY_POST_FAVORITE] ?? 0;
    if (intval($custom_only_post_favorite))
    {
        //只获取收藏的文章
        $query_vars[Post_Query::POST__IN] =  get_user_favorite() ?: [1]; //如果用户没有收藏, 使用一个不存在的ID用来过滤列表
    }

    //如果有限定关注的作者
    $custom_only_user_followed = $query_vars[Post_Query::CUSTOM_ONLY_AUTHOR_FOLLOWED] ?? 0;
    if (intval($custom_only_user_followed))
    {
        //只获取收藏的文章
        $query_vars[Post_Query::AUTHOR__IN] =  get_user_followed() ?: [2]; //如果用户没有关注的作者, 使用一个不存在的ID用来过滤列表
    }

    //如果要过滤黑名单作者
    $custom_only_not_user_user_black_list = $query_vars[Post_Query::CUSTOM_ONLY_NOT_USER_BLACK_LIST] ?? 0;
    if (intval($custom_only_not_user_user_black_list))
    {

        //如果黑名单不是空的
        $user_black_list = get_user_black_list(get_current_user_id());
        if (count($user_black_list) > 0)
        {
            //排除黑名单用户
            $query_vars[Post_Query::AUTHOR__NOT_IN] =  $user_black_list;
            //禁用缓存
            $query_vars[Post_Query::CUSTOM_NO_CACHE] = 1;
        }
    }

    //如果页面参数是 作者页
    if ($page_type === Page_Type::AUTHOR)
    {
        // $custom_search = $query_vars[Post_Query::CUSTOM_SEARCH] ?? '';
        // //如果有自定义搜索
        // if ($custom_search)
        // {
        //     //替换默认搜索
        //     $query_vars[Post_Query::SEARCH] = $custom_search;
        // }
    }

    //如果页面参数是搜索页
    if ($page_type === Page_Type::SEARCH)
    {
        // $query_vars[Post_Query::POST_TYPE] = Post_Type::POST;
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
 * 变相禁用默认主查询
 * 
 * @param WP_Query $wp_query
 * @return void
 */
function disable_main_wp_query($wp_query)
{
}

/**
 * 修改WORDPRESS主查询的变量
 * @deprecated 旧版
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
