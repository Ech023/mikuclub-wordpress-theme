<?php

namespace mikuclub;

use mikuclub\constant\Comment_Meta;
use mikuclub\constant\Config;
use WP_Error;

/**
 * 私信和回复相关的函数
 */



/**
 * 获取用户收到的未读私信数量
 * @return int 数量
 * @global $wpdb
 */
function get_user_private_message_unread_count()
{
    global $wpdb;

    $result = 0;

    $user_id = get_current_user_id();

    if ($user_id)
    {
        $result = Session_Cache::get(Session_Cache::USER_PRIVATE_MESSAGE_UNREAD_COUNT);
        //如果缓存不存在
        if ($result === null)
        {

            $query = <<<SQL
				SELECT COUNT(*) FROM mm_message WHERE recipient_id = {$user_id} AND status = 0
SQL;
            $result = intval($wpdb->get_var($query));
            //设置缓存
            Session_Cache::set(Session_Cache::USER_PRIVATE_MESSAGE_UNREAD_COUNT, $result);
        }
    }

    return intval($result);
}


/**
 * 获取用户未读的评论回复数量
 *
 * @return int
 */
function get_user_comment_reply_unread_count()
{

    $result = 0;

    $user_id = get_current_user_id();

    if ($user_id)
    {
        $result = Session_Cache::get(Session_Cache::USER_COMMENT_REPLY_UNREAD_COUNT);
        //如果缓存不存在
        if ($result === null)
        {

            $args = [
                'status'     => 'approve',
                'count'      => true,
                'meta_query' => [
                    'meta_query' => [
                        'relation' => 'AND',
                        [
                            'key'     => Comment_Meta::COMMENT_PARENT_USER_ID,
                            'value'   => $user_id,
                            'compare' => '=',
                            'type'    => 'NUMERIC',
                        ],
                        [
                            'key'     => Comment_Meta::COMMENT_PARENT_USER_READ,
                            'value'   => 0,
                            'compare' => '=',
                            'type'    => 'NUMERIC',
                        ],
                    ],
                ],
            ];

            $result = get_comments($args);
            //设置缓存
            Session_Cache::set(Session_Cache::USER_COMMENT_REPLY_UNREAD_COUNT, $result);
        }
    }

    return intval($result);
}

/**
 * 获取用户未读消息总数
 * @return int;
 */
function get_user_total_unread_count()
{

    return
        get_user_private_message_unread_count() + get_user_comment_reply_unread_count() + get_user_forum_notification_unread_count();
}
