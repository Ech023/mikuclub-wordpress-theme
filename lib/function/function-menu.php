<?php

namespace mikuclub;

use mikuclub\constant\Expired;
use WP_Bootstrap_Navwalker;

/**
 * 网站菜单栏目 和 底部链接相关函数
 */


/**
 * 获取顶部左菜单
 * @return string 菜单html列表
 */
function get_top_left_menu()
{

    $menu_item_list = wp_nav_menu([
        'theme_location' => 'top_left_menu',
        'menu_class' => 'navbar-nav',
        'container' => '',
        'fallback_cb' => 'WP_Bootstrap_Navwalker::fallback',
        'walker' => new WP_Bootstrap_Navwalker(),
        'echo' => false,
    ]);

    return $menu_item_list;
}

/**
 * 获取顶主菜单
 * @return string 菜单html列表
 */
function get_main_menu()
{

    $menu_item_list = wp_nav_menu([
        'theme_location' => 'nav',
        'echo' => false,
        'container' => '',
        'depth' => 2,
        'menu_class' => 'navbar-nav flex-fill flex-wrap',
        'fallback_cb' => 'WP_Bootstrap_Navwalker::fallback',
        'walker' => new WP_Bootstrap_Navwalker(),
    ]);

    return $menu_item_list;
}


/**
 * 获取底部菜单
 * @return string 菜单html列表
 */
function get_bottom_menu()
{

    $menu_item_list = wp_nav_menu([
        'theme_location' => 'bottom_menu',
        'menu_class' => 'nav',
        'container' => '',
        'fallback_cb' => 'WP_Bootstrap_Navwalker::fallback',
        'walker' => new WP_Bootstrap_Navwalker(),
        'echo' => false,
    ]);

    return $menu_item_list;
}



/**
 *输出友情链接列表
 * @return string 友情链接 html 代码
 */
function get_friends_links()
{


    //获取缓存
    $links_list_html = File_Cache::get_cache_meta(File_Cache::SITE_FRIEND_LINK, '', Expired::EXP_1_DAY);
    //如果缓存无效
    if (empty($links_list_html))
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
            <ul class="nav">
                {$links_list_html}
            </ul>
HTML;

        File_Cache::set_cache_meta(File_Cache::SITE_FRIEND_LINK, '', $links_list_html);
    }

    return $links_list_html;
}
