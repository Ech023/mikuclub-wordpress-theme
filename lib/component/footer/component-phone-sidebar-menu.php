<?php

namespace mikuclub;

use mikuclub\constant\Message_Type;





/**
 * 在页面上输出手机版悬浮侧边栏菜单
 * @return string
 */
function print_phone_sidebar_menu_component()
{
    $phone_sidebar_submenu_component = print_phone_sidebar_submenu_component();
    $main_phone_menu = get_main_phone_menu();

    $output = <<<HTML

        <div class="offcanvas offcanvas-start" tabindex="-1" id="phone_sidebar_menu" style="width: 300px">
            <div class="offcanvas-header px-4 py-2  border-bottom">
                
                    <button type="button" class="btn btn-secondary me-2 " data-bs-dismiss="offcanvas"> <i class="fa-solid fa-bars"></i></button>
                    <span class="offcanvas-title fs-5 fw-bold">初音社</span>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
               
            </div>
            <div class="offcanvas-body mt-2 pt-0 px-0">

                <div class="px-4">
                    {$phone_sidebar_submenu_component}
                </div>
            
                <div class="border-bottom my-2"></div>

                <div class="px-4">
                    <div class="fs-5 fw-bold">
                        <i class="fa-solid fa-border-all me-2"></i>
                        <span>分类</span>
                    </div>
                    <nav class="navbar">
                        {$main_phone_menu}
                    </nav>
                </div>
                

            </div>
        </div>
HTML;

    return $output;
}

/**
 * 在页面上输出手机版悬浮侧边栏菜单 里的第一部分子菜单
 * @return string
 */
function print_phone_sidebar_submenu_component()
{

    //当前用户ID
    $user_id = get_current_user_id();
    //网站主页地址
    $home = get_home_url();

    $message_count = get_user_total_unread_count() ?: '';

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
       

       

        $author_page_href = get_author_posts_url($user_id);
        $private_message_page_link = add_query_arg('type', Message_Type::PRIVATE_MESSAGE, $home . '/message');
        $logout_url = wp_logout_url();

        $menu_items = <<<HTML

            <li class="nav-item">
                <a class="nav-link" href="{$home}/user_profile" title="用户信息">
                    <i class="fa-solid fa-user me-2"></i>
                    <span>用户信息</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{$author_page_href}" title="个人空间">
                    <i class="fa-solid fa-shop me-2"></i>
                    <span>个人空间</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{$home}/char" title="签到">
                    <i class="fa-solid fa-calendar-check me-2"></i>
                    <span>签到</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{$private_message_page_link}" title="消息">
                    <i class="fa-solid fa-envelope me-2"></i>
                    <span>消息</span>
                    <span class="badge text-bg-miku px-2">{$message_count}</span>
                </a>
            </li>



          

            <li class="nav-item">
                <a class="nav-link" href="{$home}/favorite" title="收藏夹" >
                    <i class="fa-solid fa-heart me-2"></i>
                    <span class="">收藏夹</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{$home}/history" title="历史记录" >
                    <i class="fa-solid fa-history me-2"></i>
                    <span class="">历史</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{$home}/up_home_page" title="稿件管理" >
                    <i class="fa-solid fa-list-alt me-2"></i>
                    <span class="">稿件管理</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{$home}/submit" title="新投稿" >
                    <i class="fa-solid fa-upload me-2"></i>
                    <span class="">投稿</span>
                </a>
            </li>

            <li class="border-bottom my-2" style="margin-left: -1.5rem; margin-right: -1.5rem;"></li>

            <li class="nav-item">
                <a class="nav-link" href="{$home}/150107" title="赞助网站" >
                    <i class="fa-solid fa-circle-dollar-to-slot me-2"></i>
                    <span class="">赞助网站</span>
                </a>
            </li>

            <li class="border-bottom my-2" style="margin-left: -1.5rem; margin-right: -1.5rem;"></li>

            <li class="nav-item">
                <a class="nav-link" href="{$logout_url}" title="退出账号">
                    <i class="fa-solid fa-right-from-bracket me-2"></i>
                    <span class="">退出账号</span>
                </a>
            </li>

HTML;



     

        
    }



    $output = <<<HTML

        <nav class="navbar ">
            <ul class="navbar-nav w-100">
                {$menu_items}
            </ul>
        </nav>
HTML;

    return $output;
}
