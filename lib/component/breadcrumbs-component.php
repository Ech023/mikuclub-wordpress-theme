<?php
namespace mikuclub;
/**
 * 面包屑导航
 * @return string
 */
function breadcrumbs_component() {

	$breadcrumb_item_li        = '<li class="breadcrumb-item">';
	$breadcrumb_item_li_active = '<li class="breadcrumb-item active">';
	$breadcrumb_item_close_li  = '</li>'; // 在当前链接后插入

	$local_html = '';
	$home       = get_home_url();

	//如果是 主页-最新发布页面
	if ( is_home() ) {
		$local_html = $breadcrumb_item_li_active . '最新发布';
	} //标签页
	else if ( is_tag() ) {
		$local_html = $breadcrumb_item_li_active . '标签 ' . single_tag_title( '', false );
	}
	else if ( is_page() ) {
		$local_html = $breadcrumb_item_li_active . get_the_title();
	}
	else if( is_search()){
		$local_html = $breadcrumb_item_li_active . '搜索页';
	}

	//如果是分类 或者 文章页
	else if ( is_category() || is_single() ) {

		//如果是分类
		if ( is_category() ) {

			global $wp_query;
			$current_cat_id = $wp_query->get_queried_object()->term_id;
			$current_cat    = get_category( $current_cat_id );

			$local_html = $breadcrumb_item_li_active . $current_cat->name;
		}
		//如果是文章页
		else {


			$categories = get_the_category();
			if ( $categories ) {
				$current_cat      = get_the_category()[0];
				$current_cat_link = get_category_link( $current_cat->term_id );

				$local_html = $breadcrumb_item_li . '
	               <a href="' . $current_cat_link . '">
			                	' . $current_cat->name . '
	                </a>';
			}


		}

		//如果有父分类 就循环添加
		while ( $current_cat && $current_cat->parent != 0 ) {
			//获取父分类
			$current_cat      = get_category( $current_cat->parent );
			$current_cat_link = get_category_link( $current_cat->term_id );
			//确保只有在分类获取成功的时候才添加
			if ( $current_cat ) {
				//插入到头部
				$local_html = $breadcrumb_item_li . '
			            	<a href="' . $current_cat_link . '">
			                	' . $current_cat->name . '
			                </a>'
				              . $breadcrumb_item_close_li
				              . $local_html;
			}
		}
	}

	//如果是在分页并且大于1 添加页面数字说明
	$paged = get_query_var( 'paged' );
	if ( $paged && $paged > 1 ) {
		$local_html .= ' 第 ' . get_query_var( 'paged' ) . ' 页';
	}

	$output = '';
	if ( $local_html ) {

		$output = '<nav aria-label="breadcrumb">
						<ol class="breadcrumb bg-transparent mb-0">'
		          . $breadcrumb_item_li .
		          '<a href="' . $home . '"><i class="fa-solid fa-house"></i></a>'
		          . $breadcrumb_item_close_li
		          . $local_html
		          . $breadcrumb_item_close_li
		          . '</ol>
						</nav>';

	}


	return $output;

}
