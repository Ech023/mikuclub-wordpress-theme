<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Category;

/**
 * 主页 热门页面
 * @return string
 */
function print_home_hot_post_page_component()
{
	$active_paged = get_query_var(Post_Query::PAGED, 0);
	$active_paged = $active_paged > 1 ? $active_paged : 0; //因为是首页最新发布, 初始PAGED会是1, 所以需要重置低于等于1为0

	$breadcrumbs = print_breadcrumbs_component();

	$post_list_header_order = print_post_list_header_order();
	$post_list_header_download_type = print_post_list_header_download_type();
	$post_list_header_user_black_list = print_post_list_header_user_black_list();
	//获取当前页面的文章列表
	$post_list_component = print_post_list_component([
		'paged' => $active_paged,
	]);

	$ad_banner = '';
	//PC端+手机端
	if (get_theme_option(Admin_Meta::CATEGORY_TOP_ADSENSE_ENABLE))
	{
		$ad_banner .= '<div class="pop-banner text-center my-2 pb-2 border-bottom">' . get_theme_option(Admin_Meta::CATEGORY_TOP_ADSENSE) . '</div>';
	}



	//实际输出内容
	$output = <<<HTML

	{$breadcrumbs}

	{$ad_banner}

	{$post_list_header_order}
	{$post_list_header_download_type}
	{$post_list_header_user_black_list}
	{$post_list_component}
	
	
HTML;

	return $output;
}
