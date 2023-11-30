<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Web_Domain;

/**
 * 在页面上输出和HEAD相关的组件
 * @return string
 */
function print_head_component()
{

    //如果是成人内容, 禁止搜索引擎收录
    $meta_root_noindex = is_adult_category() ? '<meta name="robots" content="noindex,nofollow">' : '';

    //CDN地址
    $domain_cdn_mikuclub_fun = Web_Domain::CDN_MIKUCLUB_FUN;
    //页面标题
    $title = create_site_title();

    //wordpress head
    $wp_head = echo_to_string('wp_head');
    //输出 论坛自定义表情按钮的js代码
    $wpforo_custom_editor_smiley_js_code = wpforo_custom_editor_smiley_js_code();
    //网站顶部公共代码
    $site_top_code = get_theme_option(Admin_Meta::SITE_TOP_CODE_ENABLE) ? get_theme_option(Admin_Meta::SITE_TOP_CODE) : '';


    $output = <<<HTML

    <!DOCTYPE HTML>
    <html lang="zh">

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=10,IE=9,IE=8">
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=0, minimum-scale=1, maximum-scale=1">
            {$meta_root_noindex}

            <!--加载图标-->
            <link rel="icon" href="https://{$domain_cdn_mikuclub_fun}/favicon.ico" type="image/x-icon">
            <!--加载图标2-->
            <link rel="shortcut icon" href="https://{$domain_cdn_mikuclub_fun}/favicon.ico" type="image/x-icon">

            <!-- <meta property="qc:admins" content="1616274367651353452633" /> -->
            <!-- <meta property="wb:webmaster" content="1115343366e6fa53" /> -->

            <title>{$title}</title>

            {$wp_head}
            {$wpforo_custom_editor_smiley_js_code}
            {$site_top_code}

        </head>
HTML;

    return $output;
}



/**
 * 在页面上输出和BODY第一部分的组件
 * @return string
 */
function print_body_header_component()
{
    //网站主页地址
    $home = get_home_url();
    //网站名称
    $site_name = get_option('blogname');


    $top_menu_bar_component = print_top_menu_bar_component();

    //如果是文章页, 直接隐藏
    $image_windows_class = is_singular() ? 'd-none' : 'd-none d-md-block';
    //随机加载2张大图地址
    $random_head_background_image = get_random_head_background_image();
    $random_head_background_image2 = get_random_head_background_image(1);
    //顶部大图组件
    $top_image_windows = <<<HTML
        <div class="image-windows {$image_windows_class}" style="background-image: url('{$random_head_background_image}'), url('{$random_head_background_image2}')">
            <div class="row justify-content-center align-content-end h-100">

                <div class="col-auto mb-4">
                    <h1 class="text-white fw-bold user-select-none" style="text-shadow: 1px 1px #0000004a, -1px 1px #0000004a, 0 -1px #0000004a, 0 1px #0000004a;">
                           {$site_name}
                    </h1>
                </div>
                <div class="m-0"></div>
                <div class="col-auto">
                    <div class="copyright-text">
                        © SEGA / © Craft Egg Inc. Developed by Colorful Palette / © Crypton Future Media, INC. www.piapro.netAll rights reserved.
                    </div>
                </div>
            </div>
        </div>

HTML;

    $main_menu_items = get_main_menu();

    $message_count = get_user_total_unread_count() ?: '';

    $main_menu_component = <<<HTML

        <!-- 网站主菜单 -->
        <div class="px-3 px-md-4 d-none d-md-block border-bottom">
            <div class="row ">
                <div class="col main-menu ">
                    <nav class="navbar navbar-expand small">

                        <!-- <div class="collapse navbar-collapse " id="site-main-menu"> -->
                            {$main_menu_items}
                            <!-- 手机菜单搜索-->
                            <!-- <div class="d-md-none my-2">
                                <form class="form-inline flex-grow-1 site-search-form">
                                    <div class="input-group flex-grow-1">
                                        <input type="text" class="form-control" placeholder="搜索" name="search">
                                        <button class="btn btn-miku" type="submit">
                                            <i class="fa-solid fa-search"></i> 搜索
                                        </button>

                                    </div>
                                </form>
                            </div> -->
                        <!-- </div> -->

                    </nav>
                </div>

                <!-- 手机菜单 -->
                <!-- <div class="col d-md-none text-center">
                    <div class="my-2">
                        <button class="wap-menu-button btn btn-sm btn-outline-secondary w-75" type="button" data-bs-toggle="offcanvas" data-bs-target="#phone_sidebar_menu">
                            <i class="fa-solid fa-bars me-2"></i>
                            <span class="d-none d-sm-inline">{$site_name}</span><span>菜单</span>
                            <span class="badge text-bg-miku px-2">{$message_count}</span>
                        </button>
                    </div>
                </div> -->

            </div>
        </div>

      

HTML;

    $site_announcement = '';
    if (!is_singular())
    {
        $speedbar_class = !is_home() ? 'd-none d-sm-block' : '';
        $announcement_text = get_theme_option(Admin_Meta::SITE_ANNOUNCEMENT_TOP);
        $announcement_collapse_text = get_theme_option(Admin_Meta::SITE_ANNOUNCEMENT_TOP_COLLAPSE);

        $site_announcement = <<<HTML

            <!-- 公告栏-->
            <div class="speedbar small my-2 px-3 px-md-4 {$speedbar_class}">

                <div class="row ">

                    <div class="col">
                        <div class="rounded bg-gray-half p-2 h-100 ">
                            <span><i class="fa-solid fa-bullhorn me-2"></i> 公告:</span>
                            <div class="d-inline">{$announcement_text}</div>
                        </div>
                    </div>
                    <!-- <div class="col-12 col-md-auto ps-md-1 mt-2 mt-md-0 text-end">

                        <div class="rounded bg-gray-half p-2 h-100" title="如果无法正常加载站内图片, 可以尝试开启备用图床 (备用图床的加载速度比默认的更缓慢)">
                            <span class="enable_backup_image_domain_title me-1">
                            站内图片无法加载?
                            </span>
                            <div class="form-check form-check-inline form-switch m-0">
                                <input class="form-check-input cursor_pointer" type="checkbox" role="switch" id="enable_backup_image_domain">
                                <label class="form-check-label cursor_pointer" for="enable_backup_image_domain">使用备用图床</label>
                            </div>
                        </div>    

                    </div> -->

                     <!-- 输出默认折叠区域 (qq群信息)-->
                    <div id="qq-group-info" class="col-12 my-2 collapse">
                        <div class="rounded bg-gray-half p-2">
                            {$announcement_collapse_text}
                        </div>
                    </div>

                </div>

            </div>

          

HTML;
    }

    $adsense_component = '';
    //全站-主菜单下方广告位
    if (is_home() || is_single() || is_category() || is_tag())
    {
        if (get_theme_option(Admin_Meta::SITE_TOP_ADSENSE_PC_ENABLE))
        {
            $adsense_component .= '<div class="pop-banner d-none d-md-block text-center my-2">' . get_theme_option(Admin_Meta::SITE_TOP_ADSENSE_PC) . '</div>';
        }
        if (get_theme_option(Admin_Meta::SITE_TOP_ADSENSE_PHONE_ENABLE))
        {
            $adsense_component .= '<div class="pop-banner d-block d-md-none text-center my-2">' . get_theme_option(Admin_Meta::SITE_TOP_ADSENSE_PHONE) . '</div>';
        }
    }

    //首页-主菜单下方广告位
    if (is_home() && !get_query_var('paged'))
    {
        if (get_theme_option(Admin_Meta::HOME_TOP_ADSENSE_PC_ENABLE))
        {
            $adsense_component .= '<div class="pop-banner d-none d-md-block text-center my-2">' . get_theme_option(Admin_Meta::HOME_TOP_ADSENSE_PC) . '</div>';
        }
        if (get_theme_option(Admin_Meta::HOME_TOP_ADSENSE_PHONE_ENABLE))
        {
            $adsense_component .= '<div class="pop-banner d-block d-md-none text-center my-2">' . get_theme_option(Admin_Meta::HOME_TOP_ADSENSE_PHONE) . '</div>';
        }
    }

    $adsense_component = <<<HTML
        <div class="px-3 px-md-4">{$adsense_component}</div>
HTML;

    $output = <<<HTML
        <header id="header" class="header">  
            {$top_menu_bar_component}
            {$top_image_windows}
            {$main_menu_component}
            {$site_announcement}
            {$adsense_component}
        </header>
        
HTML;

    return $output;
}
