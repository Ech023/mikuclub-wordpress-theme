<?php

namespace mikuclub;

/**
 * 在页面上输出手机版底部菜单栏
 * @return string
 */
function print_float_bottom_menu_bar_component()
{
    //判断是否显示页面跳转按钮
    $show_change_page_button = false;
    if (
        (
            is_home() && get_query_var(Post_Query::PAGED) > 0
        )
        || is_category() || is_tag() || is_search() || is_author()
    )
    {
        $show_change_page_button = true;
    }
    else if (is_page())
    {
        //如果是页面, 只在特定页面显示
        $array_page_with_post_list = [
            'followed', //关注页
            'favorite', //收藏夹
            'history',  //历史
            'up_home_page', //投稿管理
        ];
        $pagename = get_query_var(Post_Query::PAGENAME);
        $show_change_page_button = in_array($pagename, $array_page_with_post_list);
    }

    //如果有未读消息, 高亮菜单
    $menu_button_class = get_user_total_unread_count() ? 'text-miku' : '';

    $menu_items = <<<HTML

        <!-- 打开手机菜单 -->
        <div class="col col-md-auto px-0 px-md-auto">
            <button class="btn w-100 px-auto px-md-4 {$menu_button_class}" data-bs-toggle="offcanvas" data-bs-target="#phone_sidebar_menu">
                <i class="fa-solid fa-bars me-md-2"></i>
                <span class="d-block d-md-inline fs-75 fs-md-100 text-truncate">菜单</span>
            </button>
        </div>

HTML;

    //如果用户未登陆
    if (!is_user_logged_in())
    {
        $login_url = wp_login_url();

        $menu_items .= <<<HTML
            <div class="col col-md-auto px-0 px-md-auto">
                <a class="btn w-100 px-auto px-md-4" href="{$login_url}" title="登录/注册">
                    <i class="fa-solid fa-sign-in-alt me-md-2"></i>
                    <span class="d-block d-md-inline fs-75 fs-md-100 text-truncate">登录</span>
                </a>
            </div>
HTML;
    }
    else
    {

        $menu_items .= <<<HTML

            <div class="col col-md-auto px-0 px-md-auto">
                <button class="go_top btn w-100 px-auto px-md-4">
                    <i class="fa-solid fa-arrow-up me-md-2"></i>
                    <span class="d-block d-md-inline fs-75 fs-md-100 text-truncate">顶部</span>
                </button>
            </div>

HTML;
    }

    //如果是文章页
    if (is_single())
    {

        $menu_items .= <<<HTML

            <div class="col col-md-auto px-0 px-md-auto">
                <button class="go_download_port btn w-100 px-auto px-md-4">
                    <i class="fa-solid fa-cloud-arrow-down me-md-2"></i>
                    <span class="d-block d-md-inline fs-75 fs-md-100 text-truncate">下载</span>
                </button>
            </div>
            <div class="col col-md-auto px-0 px-md-auto">
                <button class="go_comments_part btn w-100 px-auto px-md-4">
                    <i class="fa-solid fa-comments me-md-2"></i>
                    <span class="d-block d-md-inline fs-75 fs-md-100 text-truncate">评论</span>
                </button>
            </div>

HTML;
    }

    if ($show_change_page_button){
        $menu_items .= <<<HTML
            <div class="col col-md-auto px-0 px-md-auto">
                <button class="open_change_paged_modal btn w-100 px-auto px-md-4">
                    <i class="fa-solid fa-retweet me-md-2"></i>
                    <span class="d-block d-md-inline fs-75 fs-md-100 text-truncate">
                        跳转
                        <span class="paged small">1</span>
                    </span>

                </button>
            </div>
HTML;
    }

    if (!is_single())
    {
        $menu_items .= <<<HTML

            <div class="col col-md-auto px-0 px-md-auto">
                <button class="enable_backup_image_domain btn w-100 px-auto px-md-4">
                    <i class="fa-solid fa-images me-md-2"></i>
                    <span class="d-block d-md-inline fs-75 fs-md-100 text-truncate">备用图床</span>
                </button>
            </div>

HTML;
    }


    $menu_items .= <<<HTML

       
        <div class="col col-md-auto px-0 px-md-auto">
            <button class="enable_dark_theme no_theme btn w-100 px-auto px-md-4" title="点击后开启白天模式">
                <i class="fa-solid fa-cloud-sun me-md-2"></i>
                <span class="d-block d-md-inline fs-75 fs-md-100 text-truncate">颜色</span>
            </button>
            <button class="enable_dark_theme light_theme btn w-100 px-auto px-md-4" style="display: none;" title="点击后开启夜间模式">
                <i class="fa-solid fa-sun me-md-2"></i>
                <span class="d-block d-md-inline fs-75 fs-md-100 text-truncate">白天</span>
            </button>
            <button class="enable_dark_theme dark_theme btn w-100 px-auto px-md-4" style="display: none;" title="点击后开启自动模式">
                <i class="fa-solid fa-moon me-md-2"></i>
                <span class="d-block d-md-inline fs-75 fs-md-100 text-truncate">夜间</span>
            </button>
          
        </div>


HTML;


    $output = <<<HTML


        <div class="float_bottom_menu_bar position-fixed bottom-0 bg-body border-top w-100" style="z-index: 20">
            <div class="">
                <div class="row text-center justify-content-center my-auto my-md-1">
                    {$menu_items}
                </div>
            </div>
          


        </div>


HTML;

    return $output;
}
