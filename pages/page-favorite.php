<?php
/*
	template name: 收藏页面
*/

namespace mikuclub;

//如果未登陆 重定向回首页

use mikuclub\constant\Post_Status;
use mikuclub\constant\Post_Template;
use mikuclub\User_Capability;

use function mikuclub\print_breadcrumbs_component;


User_Capability::prevent_not_logged_user();

get_header();


$user_favorite = get_user_favorite();

$breadcrumbs = print_breadcrumbs_component();



$search_form = post_list_header_search_form();
$post_list_header_category = print_post_list_header_category();
$post_list_header_order = print_post_list_header_order();
$post_list_header_download_type = print_post_list_header_download_type();
$post_list_header_post_status = print_post_list_header_post_status(Post_Status::PUBLISH);
$custom_post_query = [
    Post_Query::POST__IN => $user_favorite ?: [1], //如果用户没有收藏, 使用一个不存在的ID用来过滤列表
    //显示所有状态的文章
    Post_Query::POST_STATUS => Post_Status::get_to_array(),
    Post_Query::CUSTOM_NO_CACHE => true,
];
$post_list_component = print_post_list_component($custom_post_query, Post_Template::FAVORITE_POST);

$output = <<<HTML

    <div class="page-favorite">

        <div class="page-header row align-items-center">

            <div class="col">
                {$breadcrumbs}
            </div>

            <div class="col-auto ms-auto small">
                注: 如果投稿被删除, 将会从收藏夹里消失
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
