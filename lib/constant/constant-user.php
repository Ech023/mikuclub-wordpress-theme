<?php

namespace mikuclub\constant;

/**
 * 用户的元数据 键名
 */
class User_Meta
{
    //用户头像ID
    const USER_AVATAR = 'mm_user_avatar';
    //用户发送的评论数
    const USER_COMMENT_COUNT = 'user_comment_count';
    //用户发送的好评/差评数
    const USER_LIKE_COUNT = 'my_rating_count';



    //用户收藏列表
    const USER_FAVORITE_POST_LIST = 'favorite_post';
    //用户关注列表
    const USER_FOLLOW_LIST = 'followed_users';
    //用户黑名单列表
    const USER_BLACK_LIST = 'user_black_list';

    //关注用户的人数
    const USER_FANS_COUNT = 'users_fans_count';
    //拉黑用户的人数
    const USER_BLACKED_COUNT = 'user_blacked_count';

    //特殊键名 用来识别 通过官方API上传用户头像的请求
    const ACTION_UPDATE_AVATAR_BY_API = 'action_update_avatar';
}

/**
 * 用户权限 常量
 */
class User_Capability
{

    //编辑主题的权限
    const EDIT_THEMES = 'edit_themes';
    //查看文章的权限
    const READ = 'read';
    //后台编辑权限
    const MANAGE_OPTIONS = 'manage_options';
    //公开文章的权限
    const PUBLISH_POSTS = 'publish_posts';
    //编辑文章的权限
    const EDIT_POSTS = 'edit_posts';

    /**
     * 检测当前用户是否是管理员
     * @return boolean
     */
    public static function is_admin()
    {
        return current_user_can(static::MANAGE_OPTIONS);
    }

    

    /**
     * 检测当前用户是否是高级作者用户
     * @return bool
     */
    public static function is_premium_user()
    {
        return current_user_can(static::PUBLISH_POSTS);
    }

     /**
     * 检测当前用户是否是正常用户
     * @return bool
     */
    public static function is_regular_user()
    {
        return current_user_can(static::READ);
    }

}
