<?php
namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Category;

/**
 * 主页 热门页面
 * @return string
 */
function print_home_hot_post_page_component() {


	$hot_post_list = '';
	$ad_banner     = '';


	//$breadcrumbs = '';

	//在第一页的时候 输出热门文章列表
	if ( get_query_var( 'paged' ) == 1 ) {
		// $hot_post_list = get_hot_list_by_random(Category::NO_ADULT_CATEGORY, 8 );
	} //其他页面的话 输出面包屑
	else {
		//$breadcrumbs = print_breadcrumbs_component();
	}

	$breadcrumbs = print_breadcrumbs_component();

	$post_header = print_post_list_header_component();
	//获取当前页面的文章列表
	$post_list = post_list_component();


	$ad_banner = '';
	//PC端+手机端
	if ( get_theme_option( Admin_Meta::CATEGORY_TOP_ADSENSE_ENABLE ) ) {
		$ad_banner .= '<div class="pop-banner text-center my-2 pb-2 border-bottom">' . get_theme_option( Admin_Meta::CATEGORY_TOP_ADSENSE ) . '</div>';
	}



	//实际输出内容
	$output = <<<HTML

	{$breadcrumbs}

	{$ad_banner}

	{$post_header}
	
	{$post_list}
	
	
HTML;

	return $output;
}
