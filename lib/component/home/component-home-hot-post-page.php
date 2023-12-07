<?php
namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Category;

/**
 * 主页 热门页面
 * @return string
 */
function print_home_hot_post_page_component() {


	$breadcrumbs = print_breadcrumbs_component();

	$post_list_header = print_post_list_header_component();
	//获取当前页面的文章列表
	$post_list_component = post_list_component();

	$ad_banner = '';
	//PC端+手机端
	if ( get_theme_option( Admin_Meta::CATEGORY_TOP_ADSENSE_ENABLE ) ) {
		$ad_banner .= '<div class="pop-banner text-center my-2 pb-2 border-bottom">' . get_theme_option( Admin_Meta::CATEGORY_TOP_ADSENSE ) . '</div>';
	}



	//实际输出内容
	$output = <<<HTML

	{$breadcrumbs}

	{$ad_banner}

	{$post_list_header}
	{$post_list_component}
	
	
HTML;

	return $output;
}
