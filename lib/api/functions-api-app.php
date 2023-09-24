<?php
namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use WP_REST_Request;
use WP_REST_Response;

/**
 * APK更新检测功能
 * @return array<string, mixed>
 */
function api_check_app_update() {

	$output = [
		'versionCode' => 28,
		'versionName' => 'v2.8版本 [21年12月24号发布]',
		'forceUpdate' => false,
		'downUrl' => 'https://cdn.mikuclub.fun/mikuclub_v2.8.apk',
		'description' => '' .
        '- 修复 无法打开站内投稿链接的问题\n'.
		'- 修复 微博登陆问题\n'.
		'- 增加 投稿页面秒传链接的文本框\n'.
		'- 支持直接唤起阿里云盘 (失败)\n'.
		'- 增加开屏广告-每30分钟最多显示一次 (成功)\n'.
        '[如果无法打开下载页, 请到初音社网站下载: mikuclub.cc]',

	];

	return $output;
}


/**
 * 通过api 获取菜单
 * 获取安卓应用专用菜单
 * @return array<int, array<string, string>>
 */
function api_get_menu() {

	$array_menu = wp_get_nav_menu_items( 6375 );
	$output     = [];

	//遍历每个菜单项, 需要去除自定义链接选项
	for ( $i = 0; $i < count( $array_menu ); $i ++ ) {
		$menu_item = $array_menu[ $i ];
		//如果是个正常分类
		if ( $menu_item->object == "category" ) {
			$item                = [];
			$item['object_id']   = $menu_item->object_id;
			$item['title']       = $menu_item->title;
			$item['post_parent'] = $menu_item->post_parent;
			$item['children']    = [];

			//如果这是子分类
			if ( $item['post_parent'] > 0 ) {
				for ( $j = count( $output ); $j >= 0; $j -- ) {
					if ( $item['post_parent'] == $output[ $j ]['object_id'] ) {
						$output[ $j ]['children'][] = $item;
						break;
					}
				}
			}
			//如果不是子分类
			else {
				//添加到最终输出数组里
				$output[] = $item;
			}

		}
	}

	return $output;
}


/**
 * 获取app端的公告和广告信息
 * @return array<string, mixed>
 */
function api_get_app_communication() {

	return [
		'communication'       => get_theme_option( Admin_Meta::APP_ANNOUNCEMENT),
		Admin_Meta::APP_ADSENSE_TEXT => get_theme_option( Admin_Meta::APP_ADSENSE_TEXT),
		Admin_Meta::APP_ADSENSE_LINK => get_theme_option( Admin_Meta::APP_ADSENSE_LINK),
		Admin_Meta::APP_ADSENSE_ENABLE => get_theme_option( Admin_Meta::APP_ADSENSE_ENABLE),
	];
}


/**
 * APP专用获取收藏文章列表
 * 
 * @param WP_REST_Request $data
 * @return WP_REST_Response
 */
function api_get_my_favorite_post_list_for_app($data){

	//从内部调用wordpress API接口
	$query = [];
	$query['per_page'] = $data['per_page'];
	$query['_envelope'] = $data['_envelope'];
	$query['page'] = $data['page'];
	$query['orderby'] = 'include';
	$query['include'] = get_user_favorite();

	$request = new WP_REST_Request( 'GET', '/wp/v2/posts' );
	$request->set_query_params( $query );
	$response = rest_do_request( $request );
	//$server = rest_get_server();
	//$result = $server->response_to_data( $response, false );

	return $response;
}

/*===============================================================*/


/**
 * 注册自定义 api 接口
 * 
 * @return void
 */
function register_custom_app_api() {

	register_rest_route( 'utils/v2', '/app_update', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\api_check_app_update',
	] );


	register_rest_route( 'utils/v2', '/get_menu', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\api_get_menu',
	] );

	register_rest_route( 'utils/v2', '/app_favorite_post_list', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\api_get_my_favorite_post_list_for_app',
	] );


	register_rest_route( 'utils/v2', '/get_app_communication', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\api_get_app_communication',
	] );




}


add_action( 'rest_api_init', 'mikuclub\register_custom_app_api' );