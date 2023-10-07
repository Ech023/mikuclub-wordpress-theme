<?php

namespace mikuclub;

/**
 * 会话缓存系统
 */
class Session_Cache
{
    //私信未读数
    const USER_PRIVATE_MESSAGE_UNREAD_COUNT = 'user_private_message_unread_count';
    //评论回复未读数
    const USER_COMMENT_REPLY_UNREAD_COUNT = 'user_comment_replay_unread_count';
    //论坛回复未读数
    const USER_FORUM_NOTIFICATION_UNREAD_COUNT = 'user_forum_notification_unread_count';
    //判断是否是正常用户
    const IS_BLOCKED_USER = 'is_blocked_user';

    /**
     * 获取会话缓存
     *
     * @param string $key
     * @return mixed|null 如果不存在则返回NULL
     */
    public static function get($key)
    {
        return $_SESSION[$key] ?? null;
    }

    /**
     * 设置会话缓存
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * 删除会话缓存
     *
     * @param string $key
     * @return void
     */
    public static function delete($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * 删除所有自定义的会话缓存
     *
     * @return void
     */
    public static function delete_all()
    {

        $array_key = [
            static::USER_PRIVATE_MESSAGE_UNREAD_COUNT,
            static::USER_COMMENT_REPLY_UNREAD_COUNT,
            static::USER_FORUM_NOTIFICATION_UNREAD_COUNT,
            static::IS_BLOCKED_USER,
        ];

        foreach ($array_key as $key)
        {
            unset($_SESSION[$key]);
        }
    }
}
