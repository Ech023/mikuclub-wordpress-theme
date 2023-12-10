<?php

/*
	template name: up主个人中心
	description: 显示统计数据, 管理投稿
*/

//如果未登陆 重定向回首页

namespace mikuclub;

use mikuclub\constant\Post_Status;
use mikuclub\constant\Post_Template;
use mikuclub\User_Capability;
use function mikuclub\print_breadcrumbs_component;
use function mikuclub\print_author_statistics;

User_Capability::prevent_not_logged_user();

get_header();




$user = wp_get_current_user();
$breadcrumbs = print_breadcrumbs_component();
$author_author_statistics = print_author_statistics($user->ID);

$search_form = post_list_header_search_form();
$post_list_header_category = print_post_list_header_category();
$post_list_header_order = print_post_list_header_order(true);
$post_list_header_download_type = print_post_list_header_download_type();
$post_list_header_post_status = print_post_list_header_post_status(Post_Status::get_to_array());
$custom_post_query = [
    Post_Query::AUTHOR => $user->ID,
    //显示所有状态的文章
    Post_Query::POST_STATUS => Post_Status::get_to_array(),
    Post_Query::CUSTOM_NO_CACHE => true,
];
$post_list_component = print_post_list_component($custom_post_query, Post_Template::MANAGE_POST);




$output = <<<HTML

    <div class="page-uploader">

        <div class="page-header">

            {$breadcrumbs}

            <div class="row row-cols-3 row-cols-md-6 text-center g-2">
                {$author_author_statistics}
            </div>

        </div>
		<div class="page-content my-2">

            <div class="my-4 w-md-50 mx-auto">
                {$search_form}
            </div>
            <div class="my-2 border-bottom">
                {$post_list_header_category}
            </div>
            {$post_list_header_order}
            {$post_list_header_download_type}
            {$post_list_header_post_status}

            {$post_list_component}
			
		</div>

    </div>

HTML;

echo $output;

get_footer();
