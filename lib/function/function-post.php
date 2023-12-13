<?php

namespace mikuclub;

use mikuclub\constant\Category;
use mikuclub\constant\Config;
use mikuclub\constant\Download_Link_Type;
use mikuclub\constant\Expired;
use mikuclub\constant\Option_Meta;
use mikuclub\constant\Post_Meta;
use mikuclub\constant\Post_Status;
use mikuclub\constant\Post_Submit_Source;
use WP_Error;
use WP_Post;
use WP_REST_Request;
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
function get_post_array_cat_id($post_id)
{

    $result = [];

    if ($post_id)
    {
        $result = get_post_meta($post_id, Post_Meta::POST_CATS, true);

        //如果数值为空 重新计算分类数组
        if (empty($result))
        {
            $result = set_post_array_cat_id($post_id);
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
function set_post_array_cat_id($post_id)
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
    $array_main_category = array_values(array_filter($categories, function (WP_Term $category)
    {
        return $category->parent === 0;
    }));

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
    $array_category_id = array_values(array_filter($array_category_id, function ($category_id) use ($array_parent_category_id)
    {
        return in_array($category_id, $array_parent_category_id) === false;
    }));

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




/**
 * 从旧版文章里提取出下载地址
 *
 * @param int $post_id
 *
 * @return array<string, string>
 */
function get_down_link_from_old_post($post_id)
{

    $result = [];

    //获取文章内容
    $post_content = get_post_field('post_content', $post_id);

    //尝试搜索文章内容
    $index = stripos($post_content, '<a class="dl" href="');
    //如果搜索到了下载1地址
    if ($index !== false)
    {

        //提取下载1的地址
        $download = substr($post_content, $index + (strlen('<a class="dl" href="')));
        $result[Post_Meta::POST_DOWN] = stristr($download, '"', true);

        //捕捉密码1位置
        $index_pw = stripos($post_content, '<span class="passw">');
        if ($index_pw !== false)
        {
            //提取密码1
            $password_text = substr($post_content, $index_pw + (strlen('<span class="passw">')));
            $result[Post_Meta::POST_PASSWORD] = stristr($password_text, '<', true);
        }

        //搜索第二个下载地址
        $index2 = stripos($download, '<a class="dl" href="');
        //如果搜索到了下载2地址
        if ($index2 !== false)
        {
            //提取下载2的地址
            $download2 = substr($download, $index2 + (strlen('<a class="dl" href="')));
            $result[Post_Meta::POST_DOWN2] = stristr($download2, "\"", true);

            //捕捉密码2位置
            $index_pw2 = stripos($password_text ?? '', '<span class="passw">');
            if ($index_pw2 != false)
            {
                //提取密码2
                $password_text2 = substr($password_text, $index_pw2 + (strlen('<span class="passw">')));
                $result[Post_Meta::POST_PASSWORD2] = stristr($password_text2, '<', true);
            }
        }
    }

    return $result;
}




/**
 *通过REST API创建文章的时候触发
 *为REST插入的新文章添加初始META数据
 *
 * @param WP_Post $post Inserted or updated post object.
 * @param WP_REST_Request $request Request object.
 * @return void
 **/
function add_custom_post_meta_on_rest_post($post, $request)
{
    //如果参数里有meta键名
    $meta = $request['meta'] ?? null;

    //有元数据数组
    if ($meta)
    {
        //批量初始化文章元数据
        //未在REST INSERT POST里使用到
        $array_post_meta_key_to_create = [
            Post_Meta::POST_SOURCE,
            Post_Meta::POST_VIDEO,
        ];
        foreach ($array_post_meta_key_to_create as $meta_key)
        {
            if (empty(metadata_exists('post', $post->ID, Post_Meta::POST_SOURCE)))
            {
                update_post_meta($post->ID, $meta_key, '');
            }
        }

        //批量更新文章元数据
        $array_post_meta_key_to_update = [
            Post_Meta::POST_SOURCE_NAME,
            //Post_Meta::POST_SOURCE, 未使用
            Post_Meta::POST_DOWN,
            Post_Meta::POST_DOWN2,
            Post_Meta::POST_PASSWORD,
            Post_Meta::POST_PASSWORD2,
            Post_Meta::POST_UNZIP_PASSWORD,
            Post_Meta::POST_UNZIP_PASSWORD2,
            // Post_Meta::POST_BAIDU_FAST_LINK, //已失效
            Post_Meta::POST_BILIBILI,
            //Post_Meta::POST_VIDEO, 未使用

        ];

        foreach ($array_post_meta_key_to_update as $meta_key)
        {
            $meta_value = $meta[$meta_key] ?? '';
            update_post_meta($post->ID, $meta_key, $meta_value);
        }


        //如果有图片数组数据
        $array_preview_id = $meta[Post_Meta::POST_PREVIEWS] ?? null;
        if (is_array($array_preview_id) && count($array_preview_id) > 0)
        {
            //确保图片ID数组为整数
            $array_preview_id = array_map(function ($preview_id)
            {
                return intval($preview_id);
            }, $array_preview_id);


            //如果图片数组只有一个图片id, 只提取第一个元素
            if (count($array_preview_id) === 1)
            {
                $previews = $array_preview_id[0];
            }
            else
            {
                $previews = $array_preview_id;
            }

            update_post_meta($post->ID, Post_Meta::POST_PREVIEWS, $previews);
            //更新相关图片附件的父文章id
            update_post_parent($array_preview_id, $post->ID);
        }

        //提交来源
        update_post_meta($post->ID, Post_Meta::POST_SUBMIT_SOURCE, Post_Submit_Source::APP);

        //调用后续文章提交动作 来设置 额外元数据 和发布
        post_submit_action($post->ID);
    }
}


/**
 * 用表单新建文章和更新文章后的动作
 *
 * @param int $post_id
 * @return void
 */
function post_submit_action($post_id)
{
    global $wpdb;

    //更新所有大小版本的图片地址
    Post_Image::update_all_array_image_src($post_id);
    //更新缩微图ID
    Post_Image::set_thumbnail_id($post_id);
    //更新缩微图图片地址
    Post_Image::set_thumbnail_src($post_id);

    //更新主要分类
    set_post_main_cat_id($post_id);
    //更新具体子分类
    set_post_sub_cat_id($post_id);
    //更新分类ID数组
    set_post_array_cat_id($post_id);
    //更新文章下载类型数据
    set_post_array_down_type($post_id);


    $down = get_post_meta($post_id, Post_Meta::POST_DOWN, true);
    $down2 = get_post_meta($post_id, Post_Meta::POST_DOWN2, true);
    //如果没有下载地址, 设置fail_time =-1, 关闭下载失效反馈功能
    if (empty($down) && empty($down2))
    {
        update_post_fail_times($post_id, -1);
    }
    //否则 重置失效次数为0
    else
    {
        update_post_fail_times($post_id, 0);
    }


    //如果是高级用户
    if (User_Capability::is_premium_user())
    {
        //直接发布文章
        post_publish_action($post_id);
    } //如果不是高级用户, 让文章变成待审核状态
    else
    {
        //更改文章状态
        update_post_status($post_id, Post_Status::PENDING);
    }

    //判断文章是否已经公开过
    // $first_published = get_post_meta($post_id, Post_Meta::POST_IS_FIRST_PUBLISHED, true);

    //获取文章所属分类ID数组
    // $array_id_category = get_post_array_cat_id($post_id);

    //如果是动漫区或者视频区或者从未公开过
    // if (
    //     in_array(Category::ANIME, $array_id_category) ||
    //     in_array(Category::VIDEO, $array_id_category) ||
    //     empty($first_published)
    // )
    // {
    //     //更新创建时间+状态
    //     $time = current_time('mysql');

    //     $wpdb->update(
    //         $wpdb->posts,
    //         [
    //             'post_date' => $time,
    //             'post_date_gmt' => get_gmt_from_date($time),
    //         ],
    //         [
    //             'ID' => $post_id,
    //         ],
    //         [
    //             '%s',
    //             '%s',
    //         ],
    //         [
    //             '%d',
    //         ]
    //     );
    // }

    //清空bilibili视频缓存信息
    Bilibili_Video::delete_video_meta($post_id);
    //清空文章相关的缓存
    File_Cache::delete_post_cache_meta_by_post_id($post_id);
}





/**
 * 文章发布动作
 *
 * @param int $post_id
 * @return void
 */
function post_publish_action($post_id)
{

    $is_first_published = get_post_meta($post_id, Post_Meta::POST_IS_FIRST_PUBLISHED, true);

    //如果是第一次发布
    if (empty($is_first_published))
    {

        //添加到微博待同步列表
        Weibo_Share::add_post_to_weibo_sync($post_id);

        //设置发布过的标识
        update_post_meta($post_id, Post_Meta::POST_IS_FIRST_PUBLISHED, 1);


        $post_author_id = intval(get_post_field('post_author', $post_id));
        //添加 point奖励
        mycred_add(
            'publishing_content',
            $post_author_id,
            1000,
            '发布投稿 %link_with_title%',
            $post_id,
            ['ref_type' => 'post'],
            'mycred_default'
        );
    }

    //发布文章
    wp_publish_post($post_id);
}




/**
 * 更改文章状态
 *
 * @param int $post_id
 * @param string $post_status
 * @return void
 */
function update_post_status($post_id, $post_status)
{

    global $wpdb;

    // //判断文章是否已经公开过
    // $first_published = get_post_meta($post_id, Post_Meta::POST_IS_FIRST_PUBLISHED, true);

    // //获取文章所属分类ID数组
    // $array_id_category = get_post_array_cat_id($post_id);
    // //如果是动漫区或者视频区或者从未公开过
    // if (
    //     in_array(Category::ANIME, $array_id_category) ||
    //     in_array(Category::VIDEO, $array_id_category) ||
    //     empty($first_published)
    // )
    // {
    //     //更新创建时间+状态
    //     $time = current_time('mysql');

    //     $wpdb->update(
    //         $wpdb->posts,
    //         [
    //             'post_status' => $post_status,
    //             'post_date' => $time,
    //             'post_date_gmt' => get_gmt_from_date($time),
    //         ],
    //         [
    //             'ID' => $post_id,
    //         ],
    //         [
    //             '%s',
    //             '%s',
    //             '%s',
    //         ],
    //         [
    //             '%d',
    //         ]
    //     );
    // }
    // //如果不是动漫区
    // else
    // {
    //只更新状态
    $wpdb->update(
        $wpdb->posts,
        [
            'post_status' => $post_status,
        ],
        [
            'ID' => $post_id,
        ],
        [
            '%s',
        ],
        [
            '%d',
        ]
    );
    // }
}


/**
 * 驳回待审文章
 *
 * @param int $post_id
 * @param string $reject_cause 驳回原因
 * @return void
 */
function reject_post($post_id, $reject_cause = '')
{

    $user_id = intval(get_post_field('post_author', $post_id));
    $post_title = get_post_field('post_title', $post_id);

    //更新文章标题+更新状态为草稿
    $time = current_time('mysql');
    wp_update_post(
        [
            'ID' => $post_id,
            'post_title' => $reject_cause . ' ' . $post_title,
            'post_status' => 'draft',
            //'post_date' => $time,
            //'post_date_gmt' => get_gmt_from_date($time)
        ]
    );


    //创建私信通知
    $message_content = '您的投稿 《 ' . $post_title . ' 》( ' . get_permalink($post_id) . ' ) 已被退回, 退回原因: ' . $reject_cause . ' .  您可以通过投稿管理页面重新编辑该稿件.';
    //发送系统私信
    send_private_message($user_id, $message_content, 0, true);
    //发送退稿邮件
    send_email_reject_post($post_id, $reject_cause);
}

/**
 * 把文章转为草稿
 *
 * @param int $post_id
 * @return boolean|WP_Error
 */
function draft_post($post_id)
{
    $result = false;

    $author_id = intval(get_post_field('post_author', $post_id));
    //如果不是管理员 并且 不是用户自己的投稿
    if (!User_Capability::is_admin() && get_current_user_id() !== $author_id)
    {
        $result = new WP_Error(401, __FUNCTION__ . ' : 无权进行该项操作');
    }
    else
    {
        $result = wp_update_post(
            [
                'ID' => $post_id,
                'post_status' => 'draft',
            ]
        );

        if (is_numeric($result))
        {
            $result = true;
        }
    }

    return $result;
}


/**
 * 更改图片附件关联的父文章ID
 *
 * @param int[] $array_post_id
 * @param int $post_parent_id
 * @return void
 * 
 * @global $wpdb
 */
function update_post_parent($array_post_id, $post_parent_id)
{

    global $wpdb;

    $string_array_post_id = implode(',', $array_post_id);

    $query = <<<SQL
        UPDATE
            {$wpdb->posts}
        SET 
            post_parent = {$post_parent_id}
        WHERE
            ID IN {$string_array_post_id}
SQL;

    $wpdb->query($query);


    // $wpdb->update(
    //     $wpdb->posts,
    //     [
    //         'post_parent' => $post_parent_id,
    //     ],
    //     [
    //         'ID' => $post_id,
    //     ],
    //     [
    //         '%d',
    //     ],
    //     [
    //         '%d',
    //     ]
    // );
}

/**
 * 更新文章的创建时间
 *
 * @param int $post_id
 * @return bool|WP_Error
 */
function update_post_date($post_id)
{
    $result = false;

    if ($post_id)
    {

        $time = current_time('mysql');
        //更新文章的创建时间
        $result = wp_update_post(
            [
                'ID' => $post_id,
                'post_date' => $time,
                'post_date_gmt' => get_gmt_from_date($time)
            ]
        );
        if (is_numeric($result))
        {
            $result = true;
        }
    }

    return $result;
}



/**
 * 添加文章置顶
 *
 * @param int $post_id
 * @return bool
 * */
function add_sticky_posts($post_id)
{
    $result = false;

    //置顶文章列表最大长度
    $max_sticky_post_length = 300;

    if ($post_id)
    {
        //获取置顶文章id数组
        $array_sticky_post_id = get_option(Option_Meta::STICKY_POSTS);

        if (count($array_sticky_post_id) > $max_sticky_post_length)
        {
            //只保存前200个ID
            $array_sticky_post_id = array_slice($array_sticky_post_id, 0, $max_sticky_post_length);
        }

        //如果id未曾置顶
        if (!in_array($post_id, $array_sticky_post_id))
        {
            //添加id到数组头部
            array_unshift($array_sticky_post_id, $post_id);
        }

        $result = update_option(Option_Meta::STICKY_POSTS, $array_sticky_post_id);
    }

    return $result;
}


/**
 * 移除文章置顶
 *
 * @param int $post_id
 * @return bool
 * */
function delete_sticky_posts($post_id)
{

    //获取置顶文章id数组
    $sticky_posts = get_option(Option_Meta::STICKY_POSTS);

    //搜索元素在数组中的位置
    $index = array_search($post_id, $sticky_posts);
    //如果成功找到
    if ($index !== false)
    {
        //根据位置删除该元素
        unset($sticky_posts[$index]);
    }

    //更新数组
    return update_option(Option_Meta::STICKY_POSTS, $sticky_posts);
}

/**
 * 检测是否是置顶文章
 *
 * @param int $post_id
 * @return bool
 */
function is_sticky_post($post_id)
{

    $is_sticky = false;

    //获取置顶文章id数组
    $sticky_posts = get_option(Option_Meta::STICKY_POSTS);
    if (is_array($sticky_posts) && in_array($post_id, $sticky_posts))
    {
        $is_sticky = true;
    }

    return $is_sticky;
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


/**
 * 设置文章下载类型数组元数据
 *
 * @param int $post_id
 * @return string
 */
function set_post_array_down_type($post_id)
{
    $result = [];

    //获取文章的下载地址
    $down = get_post_meta($post_id, Post_Meta::POST_DOWN, true) ?: '';
    $down2 = get_post_meta($post_id, Post_Meta::POST_DOWN2, true) ?: '';
    $post_content = get_post_field('post_content', $post_id);

    foreach ([$down, $down2] as $down_link)
    {
        //如果能识别出下载地址的类型
        $type_down = Download_Link_Type::get_type_by_link($down_link);
        if ($type_down)
        {
            //储存到下载类型数组内
            $result[] = $type_down;
        }
    }

    //检测描述里是否包含了字符串
    $pattern = "/magnet:\?xt=urn:[a-z0-9]+:[a-z0-9]+:[a-z0-9]+:[^&\s]+/";
    // 使用正则表达式进行匹配
    if (preg_match($pattern, $down . $down2 . $post_content))
    {
        $result[] = Download_Link_Type::MAGNET;
    }


    $result = implode(",", $result);

    //保存数组元数据
    update_post_meta($post_id, Post_Meta::POST_ARRAY_DOWN_TYPE, $result);

    return $result;
}
