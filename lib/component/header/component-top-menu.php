<?php

namespace mikuclub;

use mikuclub\constant\Message_Type;





/**
 * 在页面上输出和BODY头部菜单的组件
 * @return string
 */
function print_top_menu_bar_component()
{
    //网站名称
    // $site_name = get_option('blogname');


    $top_left_menu = get_top_left_menu();

    //如果不是搜索页
    $top_center_search_input = !is_search() ? <<<HTML

    <form class="input-group input-group-sm top_menu_bar_search_form mx-auto" style="max-width: 500px;">
            <input type="text" class="form-control" placeholder="搜索" name="search" autocomplete="off">

            <button class="btn btn-sm btn-miku px-4" type="submit">
                <i class="fa-solid fa-search"></i>
            </button>
    </form>
       

HTML : '';

    $top_right_menu = print_top_right_menu_component();

    $output = <<<HTML

        <div class="top-menu-bar border-bottom py-2 px-3 px-md-4">
            <div class=" row  justify-content-end align-items-center">
                
                <!--顶部左侧菜单栏-->
                <div class="col-auto d-none d-md-block pe-1">
                    <nav class="navbar navbar-expand small py-0">
                        {$top_left_menu}
                    </nav>
                </div>

                <!-- 顶部中侧菜单栏-->
                <div class="col px-md-1">
                    {$top_center_search_input}
                </div>

                <div class="m-0 d-none d-md-block d-xl-none"></div>

                <!--顶部右侧菜单栏-->
                <div class="col-auto ms-md-auto ps-1">
                    {$top_right_menu}
                </div>

               
            </div>
        </div>
HTML;

    return $output;
}


/**
 * 顶部右上角菜单组件
 * @return string
 */
function print_top_right_menu_component()
{

    //当前用户ID
    $user_id = get_current_user_id();
    //网站主页地址
    $home = get_home_url();

    $menu_items = '';

    if (empty($user_id))
    {
        $login_url = wp_login_url();

        $menu_items = <<<HTML

            <li class="nav-item">
                <a class="sign btn btn-sm btn-miku px-md-4 px-2" href="{$login_url}" title="登录/注册">
                    <i class="fa-solid fa-sign-in-alt"></i> 登录 / 注册 
                </a>
            </li>

HTML;
    }
    else
    {
        /* 个人中心 */
        $print_user_avatar = print_user_avatar(get_my_user_avatar($user_id), 30);
        $display_name = get_the_author_meta('display_name', $user_id);
        $user_points = get_user_points($user_id);
        $user_level = get_user_level($user_id);
        $author_page_href = get_author_posts_url($user_id);

        $logout_url = wp_logout_url();

        $menu_items = <<<HTML

        <li class="user-profile with-sub-menu nav-item dropdown me-2 me-md-0 ">
            <a class="user-img nav-link small" href="{$home}/user_profile" title="用户信息">
                {$print_user_avatar}
            </a>
            <div class="dropdown-menu" style="max-width: 200px;">

                <div class="dropdown-item-text text-truncate fw-bold small" >
                    {$display_name}
                </div>
                <div class="dropdown-item-text text-nowrap small">
                    积分 {$user_points}
                </div>
                <div class="dropdown-item-text text-nowrap small">
                    等级 {$user_level} 
                </div>

                <div class="dropdown-divider"></div>

                <a class="user-profile dropdown-item small" href="{$home}/user_profile" title="用户信息">
                    用户信息
                </a>
                <a class="dropdown-item small" href="{$author_page_href}" title="个人空间">
                    个人空间
                </a>

                <div class="dropdown-divider"></div>

                <a class="dropdown-item small" href="{$logout_url}" title="退出账号">
                    退出账号
                </a>
            </div>
        </li>

HTML;

        /* 消息中心 */
        $message_count = get_user_total_unread_count() ?: '';
        $message_center_class   = $message_count ? 'text-miku' : '';

        $private_message_page_link = add_query_arg('type', Message_Type::PRIVATE_MESSAGE, $home . '/message');
        $replay_comment_page_link = add_query_arg('type', Message_Type::COMMENT_REPLY, $home . '/message');
        $forum_page_link = add_query_arg('show_notification', 1, $home . '/forums');

        $user_private_message_unread_count = get_user_private_message_unread_count() ?: '';
        $user_comment_reply_unread_count =  get_user_comment_reply_unread_count() ?: '';
        $user_forum_notification_unread_count = get_user_forum_notification_unread_count() ?: '';

        $menu_items .= <<<HTML

            <li class="nav-item d-none d-md-block">
                <a class="nav-link" href="{$home}/char" title="签到">
                    签到
                </a>
            </li>
            <li class="message-center with-sub-menu nav-item dropdown me-2 me-md-0 d-none d-md-block">
                <a class="{$message_center_class} nav-link" href="{$private_message_page_link}" title="消息中心" target="_blank">
                    <i class="fa-solid fa-envelope d-md-none"></i>
                    <span class="d-none d-md-inline">消息</span>
                    <span class="message_count">{$message_count}</span>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{$private_message_page_link}" title="私信" target="_blank">
                        <span>我的私信</span>
                        <span>
                            {$user_private_message_unread_count}
                        </span>
                    </a>
                    <a class="dropdown-item" href="{$replay_comment_page_link}" target="_blank">
                        <span>评论回复</span>
                        <span>
                            {$user_comment_reply_unread_count}
                        </span>
                    </a>
                    <a class="dropdown-item" href="{$forum_page_link}" target="_blank">
                        <span>论坛回帖</span>
                        <span>
                           {$user_forum_notification_unread_count}
                        </span>
                    </a>
                </div>
            </li>

HTML;


        /* 收藏夹, 历史, 稿件管理, 投稿 */
        $menu_items .= <<<HTML

            <li class="nav-item me-2 me-md-0 d-none d-md-block">
                <a class="nav-link" href="{$home}/favorite" title="收藏夹" target="_blank">
                    <i class="fa-solid fa-heart d-md-none"></i>
                    <span class="d-none d-md-block">收藏夹</span>
                </a>
            </li>
            <li class="nav-item me-2 me-md-0 d-none d-md-block">
                <a class="nav-link" href="{$home}/history" title="历史记录" target="_blank">
                    <i class="fa-solid fa-history d-md-none"></i>
                    <span class="d-none d-md-block">历史</span>
                </a>
            </li>
            <li class="tougao-manage nav-item me-2 me-md-0 d-none d-md-block">
                <a class="nav-link" href="{$home}/up_home_page" title="稿件管理" target="_blank">
                    <i class="fa-solid fa-list-alt d-md-none"></i>
                    <span class="d-none d-md-block">稿件管理</span>
                </a>
            </li>
            <li class="tougao nav-item ">
                <a class=" btn btn-miku btn-sm px-3 px-md-5" href="{$home}/submit" title="新投稿" target="_blank">
                    <i class="fa-solid fa-upload me-2"></i>
                    <span class="">投稿</span>
                </a>
            </li>
HTML;
    }



    $output = <<<HTML

        <nav class="navbar navbar-expand small py-0">
            <ul class="navbar-nav align-items-center">
                {$menu_items}
            </ul>
        </nav>
HTML;

    return $output;
}
