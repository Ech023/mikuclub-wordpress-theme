<?php

namespace mikuclub;

/**
 * 论坛相关的函数
 */

/**
 * 获取论坛通知统计数
 * @return int
 * @global $wpdb
 */
function get_user_forum_notification_unread_count()
{
    global $wpdb;

    $result = 0;

    $user_id = get_current_user_id();

    if ($user_id)
    {

        $result = Session_Cache::get(Session_Cache::USER_FORUM_NOTIFICATION_UNREAD_COUNT);
        //如果缓存不存在
        if ($result === null)
        {

            //消息类型
            $itemtype = 'alert';
            //统计数量
            $row_count = 100;

           
            $query = " SELECT COUNT(*)  FROM  mm_wpforo_activity WHERE itemtype = '{$itemtype}' AND userid = {$user_id} ORDER BY id DESC LIMIT 0 , {$row_count}";

            $result = intval($wpdb->get_var($query));
            //设置缓存
            Session_Cache::set(Session_Cache::USER_COMMENT_REPLY_UNREAD_COUNT, $result);
        }
    }

    return intval($result);
}
