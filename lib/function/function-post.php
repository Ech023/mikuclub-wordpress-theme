<?php

namespace mikuclub;

use mikuclub\constant\Category;
use mikuclub\constant\Config;
use mikuclub\constant\Expired;
use mikuclub\constant\Post_Meta;
use mikuclub\constant\Web_Domain;
use WP_Term;

/*
文章相关的函数
*/

/**
 * 获取文章点击数
 *
 * @param int $post_id
 * @return int 点击数
 */
function get_post_views($post_id)
{

    $result = get_post_meta($post_id, Post_Meta::POST_VIEWS, true) ?: 0;

    return intval($result);
}


/**
 * 增加文章点击数
 *
 * @param int $post_id
 * @param int|null $view_number 需要增加的点击数
 *
 * @return int 新的点击数
 */
function add_post_views($post_id, $view_number = null)
{

    //获取点击数
    $views = get_post_views($post_id);

    //如果未指定, 重设为1
    $view_number = $view_number ?: 1;

    $views += $view_number;

    update_post_meta($post_id, Post_Meta::POST_VIEWS, $views);

    return $views;
}


/**
 * 获取文章点赞数
 *
 * @param int $post_id
 *
 * @return int
 */
function get_post_like($post_id)
{

    $result = get_post_meta($post_id, Post_Meta::POST_LIKE, true) ?: 0;

    return intval($result);
}


/**
 * 增加文章点赞次数
 *
 * @param int $post_id
 *
 * @return int 新的点赞数
 */
function add_post_like($post_id)
{

    //获取点赞数
    $count = get_post_like($post_id);
    //点赞数+1
    $count++;
    //保存新点赞数
    update_post_meta($post_id, Post_Meta::POST_LIKE, $count);

    //增加用户点赞数 (只对登陆用户有效)
    add_user_like_count(get_current_user_id());

    return $count;
}



/**
 * 减少文章点赞次数
 *
 * @param int $post_id
 * @return int 新的点赞数
 */
function delete_post_like($post_id)
{

    //获取点赞数
    $count = get_post_like($post_id);

    $count--;
    //如果点赞数为负 大于 0
    if ($count < 0)
    {
        $count = 0;
    }

    //保存新点赞数
    update_post_meta($post_id, Post_Meta::POST_LIKE, $count);

    //减少用户点赞数 (只对登陆用户有效)
    delete_user_like_count(get_current_user_id());

    return $count;
}

/**
 * 获取文章差评数
 *
 * @param int $post_id
 * @return int
 */
function get_post_unlike($post_id)
{
    $count_unlike = get_post_meta($post_id, Post_Meta::POST_UNLIKE, true) ?: 0;

    return intval($count_unlike);
}


/**
 * 增加文章差评次数
 *
 * @param int $post_id
 * @return int 新的差评数
 */
function add_post_unlike($post_id)
{

    //获取点赞数
    $count = get_post_unlike($post_id);
    //点赞数+1
    $count++;

    //保存新点赞数
    update_post_meta($post_id, Post_Meta::POST_UNLIKE, $count);

    //增加用户评价数 (只对登陆用户有效)
    add_user_like_count(get_current_user_id());

    return $count;
}


/**
 * 减少文章差评次数
 *
 * @param int $post_id
 * @return int 新的差评数
 */
function delete_post_unlike($post_id)
{

    //获取差评数
    $count = get_post_unlike($post_id);
    $count--;

    //如果数值为负数, 重置为0
    if ($count < 0)
    {
        $count = 0;
    }

    //保存新差评数
    update_post_meta($post_id, Post_Meta::POST_UNLIKE, $count);

    //减少用户评价数 (只对登陆用户有效)
    delete_user_like_count(get_current_user_id());

    return $count;
}


/**
 * 获取文章失效次数
 *
 * @param int $post_id
 * @return int 失效次数
 */
function get_post_fail_times($post_id)
{
    $fail_times = get_post_meta($post_id, Post_Meta::POST_FAIL_TIME, true) ?: 0;

    return intval($fail_times);
}


/**
 * 增加文章失效次数
 *
 * @param int $post_id
 * @return int
 */
function add_post_fail_times($post_id)
{
    $fail_times = get_post_fail_times($post_id);

    $fail_times++;

    update_post_meta($post_id, Post_Meta::POST_FAIL_TIME, $fail_times);

    return $fail_times;
}

/**
 * 更新文章失效次数
 *
 * @param int $post_id
 * @param int $value
 *
 * @return int
 */
function update_post_fail_times($post_id, $value)
{
    update_post_meta($post_id, Post_Meta::POST_FAIL_TIME, $value);

    return $value;
}


/**
 * 获取文章评论数
 *
 * @param int $post_id
 * @return int
 */
function get_post_comments($post_id = 0)
{
    $result = 0;
    if ($post_id)
    {
        $result = get_comments_number($post_id);
    }
    return intval($result);
}

/**
 * 更新文章评论数元数据
 * @param int $post_id
 * @return void
 */
function update_post_comments($post_id = 0)
{
    $comment_count = get_comments_number($post_id);
    update_post_meta($post_id, Post_Meta::POST_COMMENT_COUNT, $comment_count);
}



/**
 * 获取文章分享数
 *
 * @param int $post_id
 * @return int
 */
function get_post_shares($post_id)
{
    $count = get_post_meta($post_id, Post_Meta::POST_SHARE_COUNT, true) ?: 0;

    return intval($count);
}

/**
 * 增加文章分享次数
 *
 * @param int $post_id
 * @return int 新的分享数
 */
function add_post_shares($post_id)
{

    //获取分享数
    $count = get_post_shares($post_id);
    //分享数+1
    $count++;

    //保存新分享数
    update_post_meta($post_id, Post_Meta::POST_SHARE_COUNT, $count);

    return $count;
}

/**
 * 获取文章收藏数
 *
 * @param int $post_id
 *
 * @return int
 */
function get_post_favorites($post_id)
{
    $count = get_post_meta($post_id, Post_Meta::POST_FAVORITE_COUNT, true) ?: 0;

    return $count;
}

/**
 * 增加文章收藏次数
 *
 * @param int $post_id
 *
 * @return int 新的收藏数
 */
function add_post_favorites($post_id)
{

    //获取
    $count = get_post_favorites($post_id);
    //+1
    $count++;

    //保存
    update_post_meta($post_id, Post_Meta::POST_FAVORITE_COUNT, $count);

    return $count;
}

/**
 * 减少文章收藏次数
 *
 * @param int $post_id
 *
 * @return int 新的收藏数
 */
function delete_post_favorites($post_id)
{

    //获取
    $count = get_post_favorites($post_id);
    $count--;

    //如果收藏数 是负数, 重置为0
    if ($count < 0)
    {
        $count = 0;
    }

    //保存
    update_post_meta($post_id, Post_Meta::POST_FAVORITE_COUNT, $count);

    return $count;
}



/**
 * 获取文章分类ID数组
 *
 * @param int $post_id
 * @return int[]
 */
function get_post_category_ids($post_id)
{

    $result = [];

    if ($post_id)
    {
        $result = get_post_meta($post_id, Post_Meta::POST_CATS, true);

        //如果数值为空 重新计算分类数组
        if (empty($result))
        {
            $result = set_post_category_ids($post_id);
        }
    }

    return $result;
}


/**
 * 设置文章分类ID数组
 *
 * @param int $post_id
 * @return int[]
 */
function set_post_category_ids($post_id)
{

    //获取文章所属分类对象数组
    $categories = get_the_category($post_id);
    //提取分类ID数组
    $result = array_map(function (WP_Term $element)
    {
        return $element->term_id;
    }, $categories);

    update_post_meta($post_id, Post_Meta::POST_CATS, $result);

    return $result;
}

/**
 * 获取文章主分类id
 *
 * @param int $post_id
 * @return int
 */
function get_post_main_cat_id($post_id)
{

    //获取主分类
    $main_cat_id = get_post_meta($post_id, Post_Meta::POST_MAIN_CAT, true);
    //如果主分类未设置
    if (empty($main_cat_id))
    {
        //调用set来设置主分类
        $main_cat_id = set_post_main_cat_id($post_id);
    }

    return intval($main_cat_id);
}


/**
 * 设置文章主分类id
 *
 * @param int $post_id
 * @return int
 */
function set_post_main_cat_id($post_id)
{

    $main_cat_id = 0;

    //获取文章所属分类数组
    $categories = get_the_category($post_id);

    //提取主分类数组
    $array_main_category = array_filter($categories, function (WP_Term $category)
    {
        return $category->parent === 0;
    });

    //如果主分类数组存在
    if (count($array_main_category) > 0)
    {
        //获取第一个元素
        $main_cat_id = $array_main_category[0]->term_id;

        //更新主分类ID
        update_post_meta($post_id, Post_Meta::POST_MAIN_CAT, $main_cat_id);
    }

    return $main_cat_id;
}

/**
 * 获取文章子分类id
 *
 * @param int $post_id
 * @return int
 */
function get_post_sub_cat_id($post_id)
{

    //获取子分类
    $sub_cat_id = get_post_meta($post_id, Post_Meta::POST_SUB_CAT, true);
    //如果子分类未设置
    if (empty($sub_cat_id))
    {
        //调用set来设置子分类
        $sub_cat_id = set_post_sub_cat_id($post_id);
    }

    return intval($sub_cat_id);
}


/**
 * 设置文章子分类id
 * 如果没有子分类, 设置为0
 *
 * @param int $post_id
 * @return int
 */
function set_post_sub_cat_id($post_id)
{

    $sub_cat_id = 0;

    //获取文章所属分类数组
    $categories = get_the_category($post_id);

    //提取所有有子分类的父分类ID
    $array_parent_category_id = array_map(function (WP_Term $category)
    {
        return $category->parent;
    }, $categories);

    //提取所有分类ID
    $array_category_id = array_map(function (WP_Term $category)
    {
        return $category->term_id;
    }, $categories);

    //移除所有父分类ID
    $array_category_id = array_filter($array_category_id, function ($category_id) use ($array_parent_category_id)
    {
        return in_array($category_id, $array_parent_category_id) === false;
    });

    //如果存在子分类
    if (count($array_category_id) > 0)
    {
        //使用第一个元素
        $sub_cat_id = $array_category_id[0];
    }

    //储存
    update_post_meta($post_id, Post_Meta::POST_SUB_CAT, $sub_cat_id);

    return $sub_cat_id;
}






/*
=======================================================0
*/

/**
 * 获取从当前 到 特定时间 之间新发布的文章数量,
 * 可以指定天数 或者 时间字符串
 *
 * @param int|string $date 指定天数 或者 时间字符串
 * @return int 文章数量
 * 
 * @global $wpdb
 */
function get_new_post_count($date)
{
    global $wpdb;

    //键名
    $meta_key = 'new_post_count' . $date;

    //从缓存列表获取
    $count = File_Cache::get_cache_meta_with_callback($meta_key, '', Expired::EXP_6_HOURS, function () use ($wpdb, $date)
    {
        //如果是天数
        if (is_numeric($date))
        {
            //计算过去时间节点
            $date_node = date(Config::DATE_FORMAT_MYSQL, strtotime("now - {$date} days"));
        }
        //如果是时间字符串
        else if (strtotime($date))
        {
            $date_node = $date;
        }
        //否则设置默认
        else
        {
            $date_node = date(Config::DATE_FORMAT_MYSQL);
        }

        //查询规定时间内 新发布的文章数量
        $query = <<<SQL

            SELECT 
                COUNT(*) 
            FROM 
                {$wpdb->posts} 
            WHERE 
                post_status='publish' 
            AND 
                post_type='post' 
            AND 
                post_date > %s
SQL;

        //安全格式化SQL
        $query = $wpdb->prepare($query, $date_node);
        $result = $wpdb->get_var($query);

        return intval($result);
    });


    return intval($count);
}
