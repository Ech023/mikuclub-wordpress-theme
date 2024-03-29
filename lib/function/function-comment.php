<?php

namespace mikuclub;

use Exception;
use mikuclub\constant\Comment_Meta;
use mikuclub\constant\Config;
use mikuclub\constant\Expired;
use mikuclub\constant\Post_Meta;
use mikuclub\User_Capability;
use WP_Comment;
use WP_Comment_Query;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * 评论相关的函数
 */

/**
 * 获取评论真实的IP
 *
 * @param string $comment_author_ip
 * @return string
 */
function get_comment_author_real_ip($comment_author_ip)
{
    $user_ip = '';
    if (!empty($_SERVER['X_FORWARDED_FOR']))
    {
        $user_ip = sanitize_text_field($_SERVER['X_FORWARDED_FOR']);
    }
    else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
    {
        $user_ip = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
    }
    else if (!empty($_SERVER['REMOTE_ADDR']))
    {
        $user_ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
    }

    if ($user_ip)
    {
        $user_ip = preg_replace('/[^0-9a-f:\., ]/si', '', $user_ip);
        $user_ip = trim($user_ip);
    }

    return $user_ip ?: $comment_author_ip;
}


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
    $count = 0;
    if ($comment_id)
    {

        $args = [
            'status' => 'approve',
            'count' => true,
            'parent' => $comment_id
        ];

        //查询评论数
        $count = get_comments($args);
        update_comment_meta($comment_id, Comment_Meta::COMMENT_REPLIES_COUNT, $count);
    }
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
    $array_children_comment_id = [];
    if ($comment_id)
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
    }
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
    $count--;
    // //如果评论点赞数大于0
    // if ($count > 0)
    // {
    //     $count--;
    // }
    // else
    // {
    //     $count = 0;
    // }

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

    $result = File_Cache::get_cache_meta_with_callback(
        $cache_key,
        File_Cache::DIR_COMMENTS . DIRECTORY_SEPARATOR . $post_id,
        Expired::EXP_1_DAY,
        function () use ($post_id, $offset, $number)
        {
            $result = [];

            //如果有置顶评论
            $sticky_comment_list = get_sticky_comment_list($post_id);
            //高点赞评论列表
            $array_top_like_comment_list = get_top_like_comment_list($post_id);
            //合并置顶+高点赞评论
            $array_top_like_comment_list = array_merge($sticky_comment_list, $array_top_like_comment_list);

            //需要从主列表中排除的评论ID数组
            $array_exclude_id = array_map(function (My_Comment_Model $comment)
            {
                return $comment->comment_id;
            }, $array_top_like_comment_list);


            // //如果是第一页评论
            // if (empty($offset))
            // {
            //     //修正剩余要请求的评论数量
            //     $number -= count($array_exclude_id);
            // }

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
            // $comment_query = new WP_Comment_Query($args_normal_comment);
            // $comments = $comment_query->get_comments();

            $result = array_map(function (WP_Comment $comment)
            {
                return new My_Comment_Model($comment);
            }, $comments);

            //如果是第一页评论
            //添加 高点赞的评论到列表头部
            if (empty($offset))
            {
                $result = array_merge($array_top_like_comment_list, $result);
            }

            return array_values($result);
        }
    );

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

    $cache_key = implode('_', [
        File_Cache::TOP_LIKE_COMMENT_LIST,
        $post_id,
        $number
    ]);

    $result = File_Cache::get_cache_meta_with_callback(
        $cache_key,
        File_Cache::DIR_COMMENTS . DIRECTORY_SEPARATOR . $post_id,
        Expired::EXP_1_DAY,
        function () use ($post_id, $number)
        {

            $args_comment = [
                'post_id' => $post_id,
                'status' => 'approve',
                'type' => 'comment',
                'number' => $number,
                'hierarchical' => 'threaded',

                //根据点赞数进行排序
                'meta_query' => [
                    // [
                    //     'key' => Comment_Meta::COMMENT_LIKES,
                    //     'value'   => 1,
                    //     'compare' => '>=',
                    //     'type'    => 'NUMERIC',
                    // ]

                    //用来兼容可能存在没有点赞的子评论
                    'relation' => 'OR',
                    'comment_likes' =>
                    [
                        'key' => Comment_Meta::COMMENT_LIKES,
                        // 'value'   => 1,
                        // 'compare' => '>=',
                        'compare' => 'EXISTS',
                        'type' => 'NUMERIC',
                    ],
                    'comment_not_have_likes' =>
                    [
                        'key' => Comment_Meta::COMMENT_LIKES,
                        'compare' => 'NOT EXISTS',
                        'type' => 'NUMERIC',
                        'value' => ''
                    ],
                ],
                //根据点赞数排序, 没有点赞数 则用id排序
                'orderby' => [
                    'comment_not_have_likes' => 'DESC', 'comment_ID' => 'DESC'
                    // 'comment_likes' => 'DESC', 'comment_ID' => 'DESC'
                ],
            ];

            $comments = get_comments($args_comment);

            $result = array_map(function (WP_Comment $comment)
            {
                return new My_Comment_Model($comment);
            }, $comments);

            return $result;
        }
    );

    return $result;
}


/**
 * 获取回复我的评论
 *
 * @param int $paged
 * @param int $number_per_page
 *
 * @return My_Comment_Reply_Model[]
 */
function get_comment_reply_list($paged = 1, $number_per_page = Config::NUMBER_COMMENT_REPLY_PER_PAGE)
{

    $result = [];

    $user_id = get_current_user_id();

    if ($user_id)
    {
        $cache_key = implode('_', [
            File_Cache::USER_COMMENT_RELY_LIST,
            $paged,
            $number_per_page
        ]);
        $result = File_Cache::get_cache_meta_with_callback($cache_key, File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $user_id, Expired::EXP_10_MINUTE, function () use ($paged, $number_per_page, $user_id)
        {

            $args = [
                'paged' => $paged,
                'meta_key' => Comment_Meta::COMMENT_PARENT_USER_ID,
                'meta_value' => $user_id,
                'status' => 'approve',
                'number' => $number_per_page,
            ];

            $query_result = get_comments($args);

            $result = array_map(function (WP_Comment $wp_comment)
            {
                return new My_Comment_Reply_Model($wp_comment);
            }, $query_result);

            //遍历结果
            foreach ($result as $comment)
            {
                //如果评论回复还是未读
                if (!$comment->comment_parent_user_read)
                {
                    //把所有请求过的评论更新为已读
                    update_comment_parent_user_as_read($comment->comment_id);
                }
            }

            return $result;
        });
    }

    return $result;
}


/**
 * 获取文章的置顶评论列表, 如果存在的话
 *
 * @param int $post_id
 * @return My_Comment_Model[]
 */
function get_sticky_comment_list($post_id)
{

    $cache_key = implode('_', [
        File_Cache::STICKY_COMMENT,
        $post_id
    ]);

    $result = File_Cache::get_cache_meta_with_callback(
        $cache_key,
        File_Cache::DIR_COMMENTS . DIRECTORY_SEPARATOR . $post_id,
        Expired::EXP_1_DAY,
        function () use ($post_id)
        {
            $result = [];

            //如果有置顶评论
            $sticky_comment_id = get_post_meta($post_id, Post_Meta::POST_STICKY_COMMENT_ID, true);

            if ($sticky_comment_id)
            {
                $wp_comment_object = get_comment($sticky_comment_id);
                if ($wp_comment_object)
                {
                    $comment = new My_Comment_Model($wp_comment_object);
                    $comment->comment_is_sticky = true;
                    $result[] = $comment;
                }
            }

            return $result;
        }
    );

    return $result;
}


/**
 * 置顶评论
 *
 * @param int $comment_id
 * @return bool
 */
function add_sticky_comment($comment_id)
{

    //当前登陆用户ID
    $user_id = get_current_user_id();

    //获取评论主体
    $comment =  get_comment($comment_id);
    if (empty($comment))
    {
        throw new Empty_Exception('评论');
    }

    //获取文章作者ID
    $post_id = intval($comment->comment_post_ID);
    $post_author_id = intval(get_post_field('post_author', $post_id));


    //检测权限, 只有管理员 和 文章作者 有权限置顶评论
    if (
        //如果是管理员
        User_Capability::is_admin()
        ||
        //如果是文章作者
        $user_id === $post_author_id
    )
    {

        //更新文章的置顶文章ID
        update_post_meta($post_id, Post_Meta::POST_STICKY_COMMENT_ID, $comment_id);

        //清空该文章的所有评论缓存
        delete_comment_file_cache($comment_id, $post_id);
    }
    else
    {
        throw new Exception('无权操作');
    }

    return true;
}

/**
 * 取消置顶评论
 *
 * @param int $comment_id
 * @return bool
 */
function delete_sticky_comment($comment_id)
{

    //当前登陆用户ID
    $user_id = get_current_user_id();

    //获取评论主体
    $comment =  get_comment($comment_id);
    if (empty($comment))
    {
        throw new Empty_Exception('评论');
    }

    //获取文章作者ID
    $post_id = intval($comment->comment_post_ID);
    $post_author_id = intval(get_post_field('post_author', $post_id));

    //检测权限, 只有管理员 和 文章作者 有权限置顶评论
    if (
        //如果是管理员
        User_Capability::is_admin()
        ||
        //如果是文章作者
        $user_id === $post_author_id
    )
    {

        //更新文章的置顶文章ID
        delete_post_meta($post_id, Post_Meta::POST_STICKY_COMMENT_ID);

        //清空该文章的所有评论缓存
        delete_comment_file_cache($comment_id, $post_id);
    }
    else
    {
        throw new Exception('无权操作');
    }

    return true;
}

/**
 * 删除评论
 *
 * @param int $comment_id
 * @return bool
 * @throws Exception
 */
function delete_comment($comment_id)
{

    //当前登陆用户ID
    $user_id = get_current_user_id();

    //获取评论主体
    $comment =  get_comment($comment_id);
    if (empty($comment))
    {
        throw new Empty_Exception('评论');
    }

    //获取文章作者ID
    $post_id = intval($comment->comment_post_ID);
    $post_author_id = intval(get_post_field('post_author', $post_id));

    //评论人ID
    $author_id  = intval($comment->user_id);

    //检测时候是高级用户和当前文章的作者
    $is_premium_user_and_post_author = User_Capability::is_premium_user() && $user_id === $post_author_id;

    //检测权限, 只有管理员 和 高级作者 和 评论作者自己 有权限删除评论
    if (
        //如果是管理员
        User_Capability::is_admin()
        ||
        //如果是高级用户和文章的作者ID
        $is_premium_user_and_post_author
        ||
        //如果是评论人本ID
        $user_id === $author_id
    )
    {
        //如果是文章作者就只把评论移到回收站, 其他情况 完全删除
        $result = wp_delete_comment($comment_id, $is_premium_user_and_post_author ? false : true);
        if ($result === false)
        {
            throw new Exception('删除失败');
        }

        //更新父评论的子评论总数
        update_comment_reply_count(intval($comment->comment_parent));
        //更新子评论ID数组
        update_array_children_comment_id(intval($comment->comment_parent));

        //清空该文章的所有评论缓存
        delete_comment_file_cache($comment_id, $post_id);
    }
    else
    {
        throw new Exception('无权操作');
    }

    return $result;
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


    //清空该文章的所有评论缓存
    File_Cache::delete_comment_cache_meta_by_post_id($post_id);
}


/**
 * 给新评论 添加自定义评论元数据
 * 如果评论需要@作者, 添加meta通知
 * 获取评论的回复的父评论id, 父评论作者id和最顶层的父评论id
 * 使用 $_REQUEST['notify_author'] 来判断是否需要通知作者
 *
 * @param int $comment_id
 * @param WP_Comment $commentdata
 * 
 * @return void
 */
function add_custom_comment_meta($comment_id, $commentdata)
{
    //根据 notify_author 参数, 来判断是否需要通知文章作者
    $notify_author = isset($_REQUEST['notify_author']) ? true : false;
    //文章ID
    $post_id = intval($commentdata->comment_post_ID);
    //文章作者ID
    $post_author_id = intval(get_post_field('post_author', $post_id));

    //评论发送人ID
    $user_id = intval($commentdata->user_id);
    //父评论ID
    $comment_parent_id = intval($commentdata->comment_parent);

    //增加用户评论数统计
    add_user_comment_count($user_id);
    //更新文章评论数统计
    update_post_comments($post_id);
    //增加用户的积分
    User_Point::add_point($user_id, User_Point::POINT_FOR_NEW_COMMENT);

    //如果勾选了通知作者 并且是一级评论
    if ($notify_author && $comment_parent_id === 0)
    {
        //通知文章作者 有未读评论 
        update_comment_meta($comment_id, Comment_Meta::COMMENT_PARENT_USER_ID, $post_author_id);
        update_comment_meta($comment_id, Comment_Meta::COMMENT_PARENT_USER_READ, 0);
    }
    //如果是子评论 (正在回复另外一个评论)
    else if ($comment_parent_id > 0)
    {
        //获取对应的父评论
        $comment_parent = get_comment($comment_parent_id);
        if ($comment_parent)
        {
            //父评论ID
            $comment_parent_id = intval($comment_parent->comment_ID);
            //对应的父评论发送人ID
            $comment_parent_user_id = $comment_parent->user_id;

            //添加父评论ID
            update_comment_meta($comment_id, Comment_Meta::COMMENT_PARENT_ID, $comment_parent_id);
            //添加父评论作者ID
            update_comment_meta($comment_id, Comment_Meta::COMMENT_PARENT_USER_ID, $comment_parent_user_id);
            //通知父评论作者 未读回复
            update_comment_meta($comment_id, Comment_Meta::COMMENT_PARENT_USER_READ, 0);

            //更新父评论的子评论总数
            update_comment_reply_count($comment_parent_id);
            //更新子评论ID数组
            update_array_children_comment_id($comment_parent_id);


            //递归获取父评论的父评论
            while (intval($comment_parent->comment_parent) > 0)
            {
                $comment_parent = get_comment(intval($comment_parent->comment_parent));
            }
            //获取顶级父评论的id
            $top_parent_comment_id = intval($comment_parent->comment_ID);
            //添加顶级父评论的ID
            update_comment_meta($comment_id, Comment_Meta::TOP_COMMENT_PARENT_ID, $top_parent_comment_id);
        }
    }

    //清空该文章的所有评论缓存
    delete_comment_file_cache($comment_id, $post_id);
}

/**
 * 在插入评论之前 进行权限检测
 *
 * @param array<string,mixed> $prepared_comment The prepared comment data for `wp_insert_comment`.
 * @param WP_REST_Request $request          The current request.
 * @return array<string,mixed>|WP_Error 将要插入的评论 或者 WP_Error 错误
 */
function check_pre_insert_comment($prepared_comment, $request)
{

    $user_id = get_current_user_id();

    $comment_post_id =  isset($prepared_comment['comment_post_ID']) ? intval($prepared_comment['comment_post_ID']) : null;

    //如果相关联的文章id存在
    if ($comment_post_id)
    {

        $post_author_id = intval(get_post_field('post_author', $comment_post_id));

        //如果当前用户是封禁用户
        if (User_Capability::is_regular_user() === false)
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

/**
 * 插入评论
 * 
 * @param string $comment_content
 * @param int $comment_post_id
 * @param int $comment_parent
 *
 * @return My_Comment_Model|WP_Error
 */
function insert_comment($comment_content, $comment_post_id, $comment_parent = 0)
{

    $user = wp_get_current_user();

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
        $result = new WP_Error(400, __FUNCTION__ . ' : 你已被投稿作者拉黑',  '无法发送 你已被投稿作者拉黑');
    }
    else
    {
        $result = wp_new_comment($args, true);
        //$result = wp_handle_comment_submission($args);

        if ($result && !is_wp_error($result))
        {
            $result = new My_Comment_Model(get_comment($result));
        }
        else if ($result === false)
        {
            $result = new WP_Error(500, __FUNCTION__ . ' : 评论发送失败',  '无法发送 原因未知');
        }
    }

    return $result;
}

/**
 * 通过api删除评论的时候 触发的动作
 * 清空该评论相关的缓存
 * 
 * @param WP_Comment       $comment  The deleted comment data.
 * @param WP_REST_Response $response The response returned from the API.
 * @param WP_REST_Request  $request  The request sent to the API.
 * @return void
 */
function action_on_rest_delete_comment($comment, $response, $request)
{
    delete_comment_file_cache(intval($comment->comment_ID), intval($comment->comment_post_ID));

    //更新父评论的子评论总数
    update_comment_reply_count(intval($comment->comment_parent));
    //更新子评论ID数组
    update_array_children_comment_id(intval($comment->comment_parent));
}
