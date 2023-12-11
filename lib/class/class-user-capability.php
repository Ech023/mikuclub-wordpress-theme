<?php

namespace mikuclub;

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

    /**
     * 禁止黑名单用户访问
     * @return void
     */
    public static function prevent_blocked_user()
    {
        //如果有登陆
        if (is_user_logged_in())
        {
            
            //获取缓存
            $is_blocked_user = Session_Cache::get(Session_Cache::IS_BLOCKED_USER);
            //如果缓存不存在
            if ($is_blocked_user === null)
            {
                //检测是否是黑名单用户
                $is_blocked_user = !static::is_regular_user();
                //设置新缓存
                Session_Cache::set(Session_Cache::IS_BLOCKED_USER, $is_blocked_user);
                
            }

            //如果是黑名单
            if ($is_blocked_user)
            {
                //自动跳转到第三方域名
                $redirect_site =  'https://www.mikuclub.net';
                wp_redirect($redirect_site);
                exit;
            }
        }
    }

    /**
     * 禁止未登陆用户访问 (默认跳转回首页)
     * @param string $location
     * @return void
     */
    public static function prevent_not_logged_user($location = '')
    {
        if (!is_user_logged_in())
        {
            if (empty($location))
            {
                $location = get_site_url();
            }
            wp_redirect($location);
            exit;
        }
    }

    /**
     * 禁止非管理员用户访问 (默认跳转回首页)
     * @param string $location
     * @return void
     */
    public static function prevent_not_admin_user($location = '')
    {
        //如果未登陆 或者 不是管理员
        if (!is_user_logged_in() || !static::is_admin())
        {
            if (empty($location))
            {
                $location = get_site_url();
            }
            wp_redirect($location);
            exit;
        }
    }
}
