<?php
/*
	template name: 我的关注
*/

//如果未登陆 重定向回首页

namespace mikuclub;

use mikuclub\User_Capability;

use function mikuclub\print_breadcrumbs_component;
use function mikuclub\get_my_user_avatar;
use function mikuclub\get_user_followed;

use function mikuclub\print_user_avatar;


User_Capability::prevent_not_logged_user();

get_header();



//获取用户关注列表
$user_followed = get_user_followed();

$breadcrumbs = print_breadcrumbs_component();
$number_followed = count($user_followed);


//关注列表元素
$array_author_element = '';

$post_list_header_category = print_post_list_header_category();
$post_list_header_order = print_post_list_header_order();
$post_list_header_download_type = print_post_list_header_download_type();


$custom_post_query = [
	Post_Query::AUTHOR__IN => $user_followed,
];
$post_list_component = print_post_list_component($custom_post_query);


//如果关注列表不是空
if ($user_followed)
{

	// 获取关注作者的实例数组
	$array_user = get_users(array(
		'include' => $user_followed,
	));

	//转换成html元素
	$array_author_element = array_reduce($array_user, function ($result, $user)
	{

		$display_name = $user->display_name;
		$user_image = print_user_avatar(get_my_user_avatar($user->ID));
		$href = get_author_posts_url($user->ID);;

		$result .= <<<HTML

			<div class="col-auto">
				<a class="text-center" href="{$href}">
					<div>
						{$user_image}
					</div>
					<div class="small text-break mt-2 text-2-rows" style="width: 80px;">
						{$display_name}
					</div>
				</a>
			</div>

HTML;
		return $result;
	}, '');
}
else
{
	$array_author_element = <<<HTML

	<div class="col my-4">
		<div class="text-center fs-5">
			抱歉, 目前没有正在关注的用户
		</div>
	</div>

HTML;
}


$output = <<<HTML

<div class="page-followed">

	<div class="page-header">
		
		{$breadcrumbs}
	
		<div class="row gy-3 my-2">
			<div class="col-12 col-md-auto align-self-center">
				<div class="text-center">
					<div>我关注的用户数量</div>
					<div class="fw-bold fs-5">{$number_followed}</div>
				</div>
			</div>
			<div class="col-12 col-md">
				<div class="row g-2 mt-2 pb-2 overflow-y-auto" style="max-height: 312px">
					{$array_author_element}
				</div>
			</div>

		</div>
	
	</div>

	<div class="page-content my-2">
		<div class="my-2 border-bottom">
			{$post_list_header_category}
		</div>
		{$post_list_header_order}
		{$post_list_header_download_type}

		{$post_list_component}
	</div>

</div>

HTML;

echo $output;

get_footer();
