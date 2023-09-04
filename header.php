<?php

global $paged;


//网站主页地址
$home = get_home_url();
//网站名称
$site_name = get_option('blogname');
//初始化用户变量+禁止黑名单用户访问
init_user_data();

//页面标题
$title = '';
//如果是首页
if (is_home())
{
    //如果是第一页
    if ($paged <= 1)
    {
        //网站名称 + 描述
        $title .= get_option('blogname') . ' | ' . get_option('blogdescription');
    }
    //如果不是第一页
    else
    {

        $title .= "最新发布 第{$paged}页 | " . get_option('blogname');
    }
}
//如果不是首页
else
{

    $title = wp_title('', false, '');
    //如果未登录 访问成人分类 和成人文章 输出404内容
    if (!is_user_logged_in() && is_adult_category())
    {
        $title = '页面不存在';
    }
    else
    {
        //如果页数大于1
        if ($paged > 1)
        {
            $title .= " 第{$paged}页";
        }
    }
    $title .= ' | ' . get_option('blogname');
}




?>

<!DOCTYPE HTML>
<html lang="zh">

<head>
    <meta charset="UTF-8">

    <meta http-equiv="X-UA-Compatible" content="IE=10,IE=9,IE=8">

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no, user-scalable=0, minimum-scale=1, maximum-scale=1">



    <meta property="qc:admins" content="1616274367651353452633" />
    <meta property="wb:webmaster" content="1115343366e6fa53" />



    <?php
    //避免搜索引擎收录成人内容
    if (is_adult_category())
    {
        echo '<meta name="robots" content="noindex,nofollow">';
    }
    ?>
    <link rel="icon" href="https://<?php echo Web_Domain::CDN_MIKUCLUB_FUN ?>/favicon.ico" type="image/x-icon">
    <!--加载图标-->
    <link rel="shortcut icon" href="https://<?php echo Web_Domain::CDN_MIKUCLUB_FUN ?>/favicon.ico" type="image/x-icon">
    <!--加载图标2-->

    <title>
        <?php echo $title; ?>
    </title>

    <?php
    wp_head();


    //页面头部公共代码
    if (dopt('d_headcode_b'))
    {
        echo dopt('d_headcode');
    }

    //判断当前是否是 页面
    if (is_page())
    {

        //输出 论坛自定义表情按钮的js代码
        echo wpforo_custom_editor_smiley_js_code();
    }


    ?>





</head>

<body <?php body_class(); ?> <?php
                                //如果是内容页, 增加滚动监听
                                echo ' data-spy="scroll" data-target="#fixed-sidebar-menu" data-offset="10" ';
                                ?>>


    <!-- 谷歌跟踪代码 Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PWWVMMV" height="0" width="0" style="display:none;visibility:hidden">
        </iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->

    <script>
        //初始化暗夜模式
        init_dark_mode();
    </script>

    <div class="container-fluid px-0">

        <header id="header" class="header mx-auto">

            <div class="top-menu-bar row mx-0 px-3 px-sm-5">

                <!--顶部左侧菜单栏-->
                <div class="left-menu col-12 col-xxl-auto d-none d-md-block">
                    <nav class="navbar navbar-expand ">
                        <?php echo get_top_left_menu(); ?>
                    </nav>
                </div>

                <!-- 顶部中侧菜单栏-->
                <div class="center-menu col-sm text-center d-none d-sm-block">

                    <?php
                    //只有在不是搜索页面的时候显示
                    if (!is_search())
                    { ?>
                        <div class="row">
                            <div class="col align-items-center py-2">
                                <form class="search-form ms-xxl-auto me-auto " style="max-width: 500px;">
                                    <div class="input-group ">
                                        <input type="text" class="form-control" placeholder="搜索" name="search" autocomplete="off">

                                        <button class="btn btn-miku" type="submit">
                                            <i class="fa-solid fa-search"></i>
                                        </button>

                                    </div>
                                </form>
                            </div>
                        </div>





                    <?php } ?>
                </div>

                <!--顶部右侧菜单栏-->
                <div class="right-menu col-12 col-sm-auto ">

                    <?php top_right_menu_component(); ?>

                </div>

                <div class="col-12 d-md-none my-2 text-center">
                    <button class="wap-menu-button  py-2 px-3 btn btn-outline-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#site-main-menu">
                        <i class="fa-solid fa-bars"></i>
                        <span class="d-none d-sm-inline"><?php echo $site_name; ?></span>菜单
                    </button>
                </div>

            </div>

            <div class="image-windows  d-none d-md-block " style="background-image: url('<?php echo get_random_head_background_image() ?>')">
                <div class="image-windows-view w-75 m-auto d-flex flex-column h-100">
                    <div class="site-title flex-fill d-flex align-items-center position-relative">

                        <a class=" d-none d-md-block m-auto" href="<?php echo $home; ?>" title="初音社" style="
                        position: relative;
                        z-index: 5;
                    ">
                            <h1 class="display-3 text-miku fw-bold" style="color: white !important;   text-shadow: 1px 1px #0000004a, -1px 1px #0000004a, 0 -1px #0000004a, 0 1px #0000004a">
                                <?php echo $site_name; ?>
                            </h1>
                        </a>

                        <div class="image-copyright  text-center position-absolute w-100 bottom-0">
                            <div class="copyright-text">© SEGA / © Craft Egg Inc. Developed by Colorful Palette / © Crypton
                                Future Media, INC. www.piapro.netAll rights reserved.
                            </div>
                        </div>


                        <?php
                        //主页播放音乐 + 雪花
                        /*
					if ( is_home() && ! get_query_var( 'paged' ) ) { ?>

                        <div class="position-absolute  rounded p-3 border border-info end-0"
                             style="background-color: #fffffff2;">
                            <div class="text-center mb-2">
                                <span>圣诞之歌 歌手: 莉姬LIJI 来源:</span>
                                <a href="https://www.bilibili.com/video/BV1uJ411s7xp" target="_blank"
                                   style="position: relative; z-index: 5; "
                                   rel="external nofollow">BV1uJ411s7xp</a>
                            </div>
                            <audio src="https://tuku.mikuclub.cn/down/圣诞单身狗之歌.mp3" autoplay controls loop
                                   style="position: relative;  z-index: 5;"></audio>
                        </div>

					<?php }
                    */
                        ?>


                    </div>

                </div>
            </div>


            <!-- 网站主菜单 -->
            <div id="nav-header" class="main-menu mx-3 mx-sm-5">
                <nav class="navbar navbar-expand-md">

                    <div class="collapse navbar-collapse " id="site-main-menu">
                        <?php echo get_main_menu(); ?>
                        <!-- 手机菜单搜索-->
                        <div class="d-md-none my-2">
                            <form class="form-inline flex-grow-1 search-form">
                                <div class="input-group flex-grow-1">
                                    <input type="text" class="form-control" placeholder="搜索" name="search">

                                    <button class="btn btn-miku" type="submit">
                                        <i class="fa-solid fa-search"></i> 搜索
                                    </button>

                                </div>
                            </form>
                        </div>
                    </div>
                </nav>
            </div>
        </header>

        <section class=" mh-75vh mx-auto px-3 px-sm-5">

            <!--
            <div>
                <hr class="m-0" />
            </div>
            -->

            <?php if (!is_page() && !is_single())
            { ?>

                <!-- 菜单下方功能栏 -->
                <div class="menu-functional-bar speedbar  my-2 p-2 px-3 rounded text-end">
                    <div class="ms-auto " title="如果无法正常加载站内图片, 可以尝试开启备用图床 (备用图床的加载速度比默认的更缓慢)">
                        <span class="enable_backup_image_domain_title me-1">
                            站内图片无法加载?
                        </span>
                        <div class="form-check form-check-inline form-switch m-0">
                            <input class="form-check-input cursor_pointer" type="checkbox" role="switch" id="enable_backup_image_domain">
                            <label class="form-check-label cursor_pointer" for="enable_backup_image_domain">使用备用图床</label>
                        </div>
                    </div>

                </div>

                <div class="speedbar <?php echo !is_home() ? 'd-none d-sm-block' : '' ?> my-2 p-2 px-3 rounded">
                    <span class=""><i class="fa-solid fa-bullhorn"></i> 公告:</span>
                    <?php echo dopt('d_tui'); ?>

                </div>

                <!-- 输出默认折叠区域 (qq群信息)-->
                <div id="qq-group-info" class="my-2 collapse border rounded px-3">
                    <?php echo dopt('d_tui_qq_collapse'); ?>
                </div>

            <?php } ?>


            <!-- 全站顶部横屏栏广告位-->
            <?php
            if (is_home() || is_single() || is_category() || is_tag())
            {

                if (dopt('d_adsite_01_b'))
                {
                    echo '<div class="pop-banner d-none d-md-block text-center my-3 py-2">' . dopt('d_adsite_01') . '</div>';
                }
                if (dopt('Mobiled_adindex_01_b'))
                {
                    echo '<div class="pop-banner d-block d-md-none text-center my-3">' . dopt('Mobiled_adindex_01') . '</div>';
                }
            }


            //首页 主菜单下方广告位
            if (is_home() && !get_query_var('paged'))
            {
                if (dopt('d_adindex_00_b'))
                {
                    echo '<div class="pop-banner d-none d-md-block text-center my-3 py-2">' . dopt('d_adindex_00') . '</div>';
                }
                if (dopt('Mobiled_adindex_00_b'))
                {
                    echo '<div class="pop-banner d-block d-md-none text-center my-3">' . dopt('Mobiled_adindex_00') . '</div>';
                }
            }
            else if (is_single())
            {

                //广告：文章页 - 主菜单下方
                if (dopt('d_adpost_05_b'))
                {
                    echo '<div class=" pop-banner  my-3 py-2 d-none d-md-block">' . dopt('d_adpost_05') . '</div>';
                }

                //手机广告 - 主菜单下方
                if (dopt('Mobiled_adpost_05_b'))
                {
                    echo '<div class="pop-banner text-center my-3 d-md-none">' . dopt('Mobiled_adpost_05') . '</div>';
                }
            }


            ?>