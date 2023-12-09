<?php
/*
	template name: 历史页面
*/



namespace mikuclub;

use mikuclub\constant\Post_Template;
use mikuclub\User_Capability;

use function mikuclub\print_breadcrumbs_component;

//如果未登陆 重定向回首页
User_Capability::prevent_not_logged_user();

get_header();

$breadcrumbs = print_breadcrumbs_component();

$search_form = post_list_header_search_form();
$post_list_header_category = print_post_list_header_category();
$post_list_header_download_type = print_post_list_header_download_type();
$post_list_header_post_status = print_post_list_header_post_status();
$custom_post_query = [
    Post_Query::CUSTOM_NO_CACHE => true,
];
$post_list_component = print_post_list_component($custom_post_query, Post_Template::HISTORY_POST);

$output = <<<HTML

		<div class="page-history">

			<div class="page-header row">

				<div class="col">
					{$breadcrumbs}
				</div>

				<div class="col-auto ms-auto">
                    <div class="text-end mb-2">
                        <button class="clear_history btn btn-sm btn-light-2">清空浏览历史</button>
                    </div>
                    <div class="small">
                        注: 如果投稿被UP删除, 将会从历史里消失
                    </div>
                    
				</div>

			</div>
			<div class="page-content my-2">
                <div class="my-4 w-md-50 mx-auto">
                    {$search_form}
                </div>
                <div class="my-2 border-bottom">
                    {$post_list_header_category}
                </div>
                {$post_list_header_download_type}
                {$post_list_header_post_status}

                {$post_list_component}
			</div>
			
		</div>

HTML;

echo $output;

get_footer();
