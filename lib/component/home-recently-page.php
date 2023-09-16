<?php
namespace mikuclub;

use mikuclub\constant\Admin_Meta;

/**
 * 主页 第一页 最新发布 组件
 * @return string
 */
function home_recently_page() {


	$hot_post_list = '';
	$ad_banner     = '';


	//$breadcrumbs = '';

	//在第一页的时候 输出热门文章列表
	if ( get_query_var( 'paged' ) == 1 ) {
		$hot_post_list = get_hot_list_by_random( 8 );
	} //其他页面的话 输出面包屑
	else {
		//$breadcrumbs = breadcrumbs_component();
	}

	$breadcrumbs = breadcrumbs_component();

	//获取当前页面的文章列表
	$post_list = post_list_component();


	$ad_banner = '';
	//PC端+手机端
	if ( dopt( Admin_Meta::CATEGORY_TOP_ADSENSE_ENABLE ) ) {
		$ad_banner .= '<div class="pop-banner  text-center my-4">' . dopt( Admin_Meta::CATEGORY_TOP_ADSENSE ) . '</div>';
	}



	//实际输出内容
	$output = <<<HTML

	 <header class="archive-header">
		<h4>{$breadcrumbs}</h4>
	</header>	
	
	{$hot_post_list}
	
	
	
	{$ad_banner}
	
	{$post_list}

	
	
HTML;

	return $output;
}
