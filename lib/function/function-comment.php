<?php

namespace mikuclub;

use mikuclub\constant\Comment_Meta;
use mikuclub\constant\Config;
use mikuclub\constant\Expired;
use WP_Comment;
use WP_Error;
use WP_REST_Request;

/**
 * 评论相关的函数
 */

/**
 * 获取评论子回复数量
 *
 * @param int $comment_id
 * @return int 回复数
 */
function get_comment_reply_count($comment_id)
{

    $count = get_comment_meta($comment_id, Comment_Meta::COMMENT_REPLIES_COUNT, true);
    //如果键值不存在
    if (empty($count))
    {
        //重新计算
        $count = update_comment_reply_count($comment_id);
    }

    return intval($count);
}

/**
 * 重新获取评论回复数
 *
 * @param int $comment_id
 *
 * @return int 回复数
 */
function update_comment_reply_count($comment_id)
{

    $args = [
        'status' => 'approve',
        'count' => true,
        'parent' => $comment_id
    ];

    //查询评论数
    $count = get_comments($args);
    update_comment_meta($comment_id, Comment_Meta::COMMENT_REPLIES_COUNT, $count);

    return intval($count);
}

/**
 * 获取 评论回复 ID 数组
 *
 * @param int $comment_id
 *
 * @return int[] 子评论id数组
 */
function get_array_children_comment_id($comment_id)
{

    $array_children_comment_id = get_comment_meta($comment_id, Comment_Meta::ARRAY_CHILDREN_COMMENT_ID, true);
    //如果键值不存在
    if (empty($array_children_comment_id))
    {
        //重新计算
        $array_children_comment_id = update_array_children_comment_id($comment_id);
    }

    return $array_children_comment_id;
}

/**
 * 更新 评论回复 ID 数组
 *
 * @param int $comment_id
 *
 * @return int[] 子评论id数组
 */
function update_array_children_comment_id($comment_id)
{

    $args = [
        'parent' => $comment_id,
        'hierarchical' => 'flat', //包括间接回复 , 添加到列表结尾
        'fields' => 'ids',
        'status' => 'approve',
    ];

    $array_children_comment_id = get_comments($args);
    $array_children_comment_id = array_map(function ($id)
    {
        return intval($id);
    }, $array_children_comment_id);

    update_comment_meta($comment_id, Comment_Meta::ARRAY_CHILDREN_COMMENT_ID, $array_children_comment_id);

    return $array_children_comment_id;
}





/**
 * 更新子评论回复 的 阅读状态为 已读
 *
 * @param int $comment_id
 * 
 * @return void
 */
function update_comment_parent_user_as_read($comment_id)
{
    update_comment_meta($comment_id, Comment_Meta::COMMENT_PARENT_USER_READ, 1, 0);
}


/**
 * 获取评论点赞次数
 *
 * @param int $comment_id
 * @return int 点赞次数
 */
function get_comment_like($comment_id)
{
    $count = get_comment_meta($comment_id, Comment_Meta::COMMENT_LIKES, true);;
    if (empty($count))
    {
        $count = 0;
    }

    return intval($count);
}

/**
 * 增加评论点赞数
 *
 * @param int $comment_id
 * @return int
 */
function add_comment_like($comment_id)
{
    $count = get_comment_like($comment_id);
    $count++;
    update_comment_meta($comment_id, Comment_Meta::COMMENT_LIKES, $count);

    return $count;
}

/**
 * 减少评论点赞数
 *
 * @param int $comment_id
 * @return int
 */
function delete_comment_like($comment_id)
{
    $count = get_comment_like($comment_id);
    //如果评论点赞数大于0
    if ($count > 0)
    {
        $count--;
    }
    else
    {
        $count = 0;
    }

    update_comment_meta($comment_id, Comment_Meta::COMMENT_LIKES, $count);

    return $count;
}


/**
 * 获取文章的评论列表
 *
 * @param int $post_id
 * @param int $offset
 * @param int $number
 *
 * @return My_Comment_Model[]
 */
function get_comment_list($post_id, $offset, $number = Config::NUMBER_COMMENT_PER_PAGE)
{
    $cache_key = implode('_', [
        File_Cache::COMMENT_LIST,
        $post_id,
        $offset,
        $number
    ]);
    $result = File_Cache::get_cache_meta($cache_key, File_Cache::DIR_COMMENTS . DIRECTORY_SEPARATOR . $post_id, Expired::EXP_30_MINUTE);

    //如果缓存不存在
    if (empty($result))
    {

        //高点赞评论列表
        $array_top_like_comment_list = [];
        //需要从主列表中排除的评论ID数组
        $array_exclude_id = [];

        //如果是第一页评论
        //添加 高点赞的评论到列表头部
        if (empty($offset))
        {
            $array_top_like_comment_list = get_top_like_comment_list($post_id);
            $array_exclude_id = array_map(function (My_Comment_Model $comment)
            {
                return $comment->comment_id;
            }, $array_top_like_comment_list);
        }

        $args_normal_comment = [
            'comment__not_in' => $array_exclude_id,
            'post_id' => $post_id,
            'offset' => $offset,
            'status' => 'approve',
            'type' => 'comment',
            'number' => $number,
            'hierarchical' => 'threaded',
            'orderby' => [
                'comment_ID' => 'DESC'
            ],
        ];

        $comments = get_comments($args_normal_comment);
        $array_comment_list = array_map(function (WP_Comment $comment)
        {
            return new My_Comment_Model($comment);
        }, $comments);

        $result = array_merge($array_top_like_comment_list, $array_comment_list);

        File_Cache::set_cache_meta($cache_key, File_Cache::DIR_COMMENTS . DIRECTORY_SEPARATOR . $post_id, $result);
    }

    return $result;
}

/**
 * 获取高赞评论列表
 *
 * @param int $post_id
 * @param int $number
 * @return My_Comment_Model[]
 */
function get_top_like_comment_list($post_id, $number = Config::NUMBER_TOP_LIKE_COMMENT_PER_PAGE)
{

    $args_comment = [
        'post_id' => $post_id,
        'status' => 'approve',
        'type' => 'comment',
        'number' => $number,
        'hierarchical' => 'threaded',

        //根据点赞数进行排序
        'meta_query' => [
            'relation' => 'OR',
            'comment_likes' =>
            [
                'key' => Comment_Meta::COMMENT_LIKES,
                'value'   => 1,
                'compare' => '>=',
                'type' => 'NUMERIC',
            ],
            'comment_not_likes' =>
            [
                'key' => Comment_Meta::COMMENT_LIKES,
                'compare' => 'NOT EXISTS',
                'type' => 'NUMERIC',
                'value' => ''
            ],
        ],
        //根据点赞数排序, 没有点赞数 则用id排序
        'orderby' => [
            'comment_not_likes' => 'DESC', 'comment_ID' => 'DESC'
        ],


    ];

    $comments = get_comments($args_comment);

    $result = array_map(function (WP_Comment $comment)
    {
        return new My_Comment_Model($comment);
    }, $comments);

    return $result;
}


/**
 * 获取回复我的评论
 *
 * @param int $paged
 * @param int $number_per_page
 *
 * @return My_Comment_Reply[]
 */
function get_comment_reply_list($paged = 1, $number_per_page = Config::NUMBER_COMMENT_REPLY_PER_PAGE)
{

    $comment_reply_list = [];

    $user_id = get_current_user_id();

    if ($user_id)
    {

        $args = [
            'paged' => $paged,
            'meta_key' => Comment_Meta::COMMENT_PARENT_USER_ID,
            'meta_value' => $user_id,
            'status' => 'approve',
            'number' => $number_per_page,
        ];

        $results = get_comments($args);

        $comment_replies = array_map(function (WP_Comment $wp_comment)
        {
            return new My_Comment_Reply($wp_comment);
        }, $results);

        //遍历结果
        foreach ($comment_replies as $comment)
        {
            //如果评论回复还是未读
            if ($comment->comment_parent_user_read)
            {
                //把所有请求过的评论更新为已读
                update_comment_parent_user_as_read($comment->comment_id);
            }
        }
    }

    return $comment_reply_list;
}

/**
 * 清空文章的评论缓存
 *
 * @param int $comment_id
 * @param int|null $post_id 如果post_id 存在就不使用 $comment_id
 * @return void
 */
function delete_comment_file_cache($comment_id, $post_id = null)
{
    //如果文章ID参数不存在
    if ($comment_id && empty($post_id))
    {
        //重新通过评论获取文章ID
        $comment = get_comment($comment_id);
        if ($comment)
        {
            $post_id = intval($comment->comment_post_ID);
        }
    }


    if ($post_id)
    {
        //清空该文章的所有评论缓存
        File_Cache::delete_directory(File_Cache::DIR_COMMENTS . DIRECTORY_SEPARATOR . $post_id);
    }
}
