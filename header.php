<?php

namespace mikuclub;
use mikuclub\User_Capability;

//检查是否是黑名单用户
User_Capability::prevent_blocked_user();


$head_output = print_head_component();

$body_class = esc_attr(implode(' ', get_body_class()));

$body_header_output = print_body_header_component();



echo <<<HTML

    {$head_output}

    <!-- 如果是内容页, 增加滚动监听 -->
    <body class="{$body_class} overflow-x-hidden" >

    <!-- 谷歌跟踪代码 Google Tag Manager (noscript) -->
    <!-- <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PWWVMMV" height="0" width="0" style="display:none;visibility:hidden">
        </iframe>
    </noscript> -->
    <!-- End Google Tag Manager (noscript) -->

        <script>
            //初始化暗夜模式,必须在body标签加载后再运行
            init_dark_theme();
        </script>

        <div class="container-fluid px-0">
            {$body_header_output}

            <section class="mh-75vh px-3 px-md-4 my-2">
                <div class="content">

HTML;
