<?php

namespace mikuclub;

use mikuclub\constant\Comment_Meta;
use WP_Comment;
use WP_Error;
use WP_REST_Request;














/**
 * 新建评论时触发
 * 添加自定义评论元数据
 * 如果评论需要@作者, 添加meta通知
 * 获取评论的回复的父评论id, 父评论作者id和最顶层的父评论id
 *
 * @param int $comment_id
 * @param WP_Comment $commentdata
 * 
 * @return void
 */
function action_on_insert_comment($comment_id, $commentdata)
{

    $post_id = intval($commentdata->comment_post_ID);


    //增加用户评论数统计
    add_user_comment_count(intval($commentdata->user_id));
    //更新文章评论数统计
    update_post_comments($post_id);

    //清空该文章的所有评论缓存
    delete_comment_file_cache($comment_id, $post_id);


    //如果勾选了通知作者 并且是一级评论
    if (isset($_POST['notify_author']) && intval($commentdata->comment_parent) === 0)
    {

        //添加原文章作者id数据, 设置为作者未读评论
        $post_author_id = get_post_field('post_author', $post_id);
        update_comment_meta($comment_id, Comment_Meta::COMMENT_PARENT_USER_ID, $post_author_id);
        update_comment_meta($comment_id, Comment_Meta::COMMENT_PARENT_USER_READ, 0);
    }
    //如果是二级评论 (正在回复另外一个评论)
    else if ($commentdata->comment_parent > 0)
    {

        $parent_comment = get_comment($commentdata->comment_parent);
        $parent_user_id = $parent_comment->user_id;
        $parent_comment_id = intval($parent_comment->comment_ID);

        //添加父评论ID
        update_comment_meta($comment_id, 'parent_comment_id', $parent_comment_id);
        //添加父评论作者ID
        update_comment_meta($comment_id, Comment_Meta::COMMENT_PARENT_USER_ID, $parent_user_id);
        //通知父评论作者 未读回复
        update_comment_meta($comment_id, Comment_Meta::COMMENT_PARENT_USER_READ, 0);
        //更新评论回复数
        update_comment_reply_count($parent_comment_id);
        //更新子评论ID数组
        update_array_children_comment_id($parent_comment_id);


        //如果父评论还有他自己的父评论
        while ($parent_comment->comment_parent > 0)
        {
            $parent_comment = get_comment($parent_comment->comment_parent);
        }
        //获取顶级父评论的id
        $top_parent_comment_id = $parent_comment->comment_ID;
        //添加顶级父评论的ID
        update_comment_meta($comment_id, 'top_parent_comment_id', $top_parent_comment_id);
    }
}

//默认+rest api添加评论 都会触发
add_action('wp_insert_comment', 'mikuclub\action_on_insert_comment', 10, 2);


/**
 * 通过REST API添加新评论前进行用户资格检测
 *
 * @param array<string,mixed> $prepared_comment The prepared comment data for `wp_insert_comment`.
 * @param WP_REST_Request $request          The current request.
 * @return array<string,mixed>|WP_Error 将要插入的评论 或者 WP_Error 错误
 */
function action_on_rest_preprocess_comment($prepared_comment, $request)
{

    $comment_post_id =  $prepared_comment['comment_post_ID'] ?? null;
    //如果相关联的文章id存在
    if ($comment_post_id)
    {
        $user_id = get_current_user_id();
        $post_author_id = intval(get_post_field('post_author', $comment_post_id));

        //如果当前用户是封禁用户
        if (current_user_is_regular() === false)
        {
            $prepared_comment = new WP_Error(400, __FUNCTION__ . ' : 该账号已被封禁',  '无法发送 该账号已被封禁');
        }
        //如果评论人已经被文章作者拉黑
        else if (in_user_black_list($post_author_id, $user_id))
        {
            $prepared_comment = new WP_Error(400, __FUNCTION__ . ' : 你已被投稿作者拉黑',  '无法发送 你已被投稿作者拉黑');
        }
    }

    return $prepared_comment;
}
add_filter('rest_preprocess_comment', 'mikuclub\action_on_rest_preprocess_comment', 10, 2);


/**
 * @param string $comment_content
 * @param int $comment_post_id
 * @param int $comment_parent
 *
 * @return My_Comment_Model | mixed
 */
function insert_custom_comment($comment_content, $comment_post_id, $comment_parent = 0)
{


    $user = wp_get_current_user();

    $result = false;

    $args = [
        'comment_author' => $user->display_name,
        'comment_author_email' => $user->user_email,
        'comment_author_url' => '',
        'comment_content' => $comment_content,
        'comment_parent' => $comment_parent,
        'comment_post_ID' => $comment_post_id,
        'user_id' => $user->ID,

    ];

    //获取文章作者ID
    $post_author_id = intval(get_post_field('post_author', $comment_post_id));
    //如果评论人已经被文章作者拉黑
    if (in_user_black_list($post_author_id, $user->ID))
    {
        return new WP_Error(400, __FUNCTION__ . ' : 你已被投稿作者拉黑',  '无法发送 你已被投稿作者拉黑');
    }

    $result = wp_new_comment($args, true);
    //$result = wp_handle_comment_submission($args);

    if ($result && !is_wp_error($result))
    {
        $result = new My_Comment_Model(get_comment($result));
    }


    return $result;
}
