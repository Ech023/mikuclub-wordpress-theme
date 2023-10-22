<?php
namespace mikuclub;

use WP_Error;
use WP_REST_Request;

/**
 * 通过api获取文章列表
 *
 * @param WP_REST_Request $data
 *
 * @return My_Post_Model[]
 */
function api_get_post_list( $data ) {

	//获取请求参数
	$data = $data->get_params();

	//如果没有设置页数, 就重设为2
	if (isset($data['paged']) && ! isset( $data['paged'] ) ) {
		$data['paged'] = 2;
	}

	//查询文章
	$post_list = get_post_list( $data );

	//如果是作者页面
	if ( isset( $data['page_type'] ) && $data['page_type'] == 'author' ) {
		//清空文章列表中的作者信息
		foreach ( $post_list as $My_Post_Model ) {
			//$My_Post_Model->post_author = null;
		}
	}

	return $post_list;


}


/**
 * 通过api获取收藏夹文章列表
 *
 * @param WP_REST_Request $data
 *
 * @return My_Post_Model[]
 */
function api_get_my_favorite_post_list( $data ) {

	$paged = $data['paged'] ?? 1;
	$search = $data['s'] ?? null;
	$cat = $data['cat'] ?? null;

	//查询文章
	return get_my_favorite_post_list($paged, $search, $cat);

}





/*获取从当前 到 特定时间 之间新发布的文章数量 API接口*/
/**
 *
 * @param WP_REST_Request $data
 *
 * @return int|WP_Error
 */
function api_get_new_post_count( $data ) {

	//如果缺少必要参数
	if ( ! isset( $data['date'] ) && ! isset( $data['days'] ) ) {
		return new WP_Error( 400, __FUNCTION__ . ' : 缺少必要参数 (date 或 days)' );
	}

	$count = 0;
	if (isset($data['days']) &&  isset( $data['days'] ) ) {
		$count = get_new_post_count( $data['days'] );
	}
	else if ( isset( $data['date'] ) ) {
		$count = get_new_post_count( $data['date'] );
	}


	return $count;

}


/**
 * 注册自定义 api 接口
 * 
 * @return void
 */
function register_custom_post_list_api() {

	register_rest_route( 'utils/v2', '/post_list', [
		'methods'  => 'GET, POST',
		'callback' => 'mikuclub\api_get_post_list',
	] );

	register_rest_route( 'utils/v2', '/favorite_post_list', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\api_get_my_favorite_post_list',
	] );

	register_rest_route( 'utils/v2', '/new_post_count', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\api_get_new_post_count',
	] );

}

/* 挂载函数到系统中*/
add_action( 'rest_api_init', 'mikuclub\register_custom_post_list_api' );


