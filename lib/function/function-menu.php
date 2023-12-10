<?php

namespace mikuclub;

use mikuclub\constant\Expired;
use mikuclub\constant\Site_Menu;

/**
 * 网站菜单栏目 和 底部链接相关函数
 */


/**
 * 获取顶部左菜单
 * @return string 菜单html列表
 */
function get_top_left_menu()
{

    $meta_key = File_Cache::SITE_MENU . '_' . Site_Menu::TOP_LEFT_MENU;

    $result = File_Cache::get_cache_meta_with_callback($meta_key, '', Expired::EXP_1_HOUR, function ()
    {
        $result = wp_nav_menu([
            'theme_location' => Site_Menu::TOP_LEFT_MENU,
            'menu_class' => 'navbar-nav',
            'container' => '',
            'fallback_cb' => 'WP_Bootstrap_Navwalker::fallback',
            'walker' => new WP_Bootstrap_Navwalker(),
            'echo' => false,
        ]);

        return $result;
    });

    return $result;
}

/**
 * 获取网站主菜单
 * @return string 菜单html列表
 */
function get_main_menu()
{
    $meta_key = File_Cache::SITE_MENU . '_' . Site_Menu::MAIN_MENU;

    $result = File_Cache::get_cache_meta_with_callback($meta_key, '', Expired::EXP_1_HOUR, function ()
    {
        $result = wp_nav_menu([
            'theme_location' => Site_Menu::MAIN_MENU,
            'menu_class' => 'navbar-nav flex-fill flex-wrap',
            'depth' => 2,
            'container' => '',
            'fallback_cb' => 'WP_Bootstrap_Navwalker::fallback',
            'walker' => new WP_Bootstrap_Navwalker(),
            'echo' => false,
        ]);
        return $result;
    });

    return $result;
}


/**
 * 获取网站主菜单 手机版
 * @return string 菜单html列表
 */
function get_main_phone_menu()
{
    $meta_key = File_Cache::SITE_MENU . '_' . Site_Menu::LEFT_SIDE_MENU;

    $result = File_Cache::get_cache_meta_with_callback($meta_key, '', Expired::EXP_1_HOUR, function ()
    {
        $result = wp_nav_menu([
            'theme_location' => Site_Menu::LEFT_SIDE_MENU,
            'menu_class' => 'navbar-nav flex-fill flex-wrap',
            'depth' => 2,
            'container' => '',
            'fallback_cb' => 'WP_Bootstrap_Navwalker::fallback',
            'walker' => new WP_Bootstrap_Navwalker(),
            'echo' => false,
        ]);
        return $result;
    });

    return $result;
}

/**
 * 获取底部菜单
 * @return string 菜单html列表
 */
function get_bottom_menu()
{
    $meta_key = File_Cache::SITE_MENU . '_' . Site_Menu::BOTTOM_MENU;

    $result = File_Cache::get_cache_meta_with_callback($meta_key, '', Expired::EXP_1_HOUR, function ()
    {
        $result = wp_nav_menu([
            'theme_location' => Site_Menu::BOTTOM_MENU,
            'menu_class' => 'nav',
            'container' => '',
            'fallback_cb' => 'WP_Bootstrap_Navwalker::fallback',
            'walker' => new WP_Bootstrap_Navwalker(),
            'echo' => false,
        ]);
        return $result;
    });

    return $result;
}



/**
 *输出友情链接列表
 * @return string 友情链接 html 代码
 */
function get_friends_links()
{

    //获取缓存
    $links_list_html = File_Cache::get_cache_meta_with_callback(
        File_Cache::SITE_FRIEND_LINK,
        '',
        Expired::EXP_1_DAY,
        function ()
        {
            //获取链接列表
            $links_list = get_bookmarks();

            $links_list_html = <<<HTML
                <li class="nav-item">
                    <a class="nav-link disabled" href="#">友情链接: </a> 
                </li>
HTML;

            $links_list_html = array_reduce($links_list, function (string $carry, object $link)
            {
                $carry .= <<< HTML
                    <li class="nav-item">
                        <a class="nav-link" title="{$link->link_name}" href="{$link->link_url}" target="_blank">
                            {$link->link_name}
                        </a> 
                    </li>
HTML;
                return $carry;
            }, $links_list_html);

            //最终输出
            $links_list_html = <<<HTML
                <div class="friends-link my-2">
                    <ul class="nav">
                        {$links_list_html}
                    </ul>
                </div>
HTML;

            return $links_list_html;
        }
    );

    return $links_list_html;
}
