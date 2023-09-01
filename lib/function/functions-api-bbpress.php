<?php


/**
 * api 获取论坛回复列表
 *
 * @param array $data
 */
function api_get_bbpress_replies( $data ) {

	//默认参数
	$paged  = 1;
	$number = 20;

	if ( isset_numeric( $data['paged'] ) ) {
		$paged = $data['paged'];
	}
	else if ( isset( $data['paged'] ) && ! is_numeric( $data['paged'] ) ) {
		return new WP_Error( 400, __FUNCTION__ . ' : paged 参数错误' );
	}

	if ( isset_numeric( $data['number'] ) ) {
		$number = $data['number'];
	}
	else if ( isset( $data['number'] ) && ! is_numeric( $data['number'] ) ) {
		return new WP_Error( 400, __FUNCTION__ . ' : number 参数错误' );
	}

	return get_bbpress_replies( $paged, $number );

}


/**
 * 注册自定义 api 接口
 */
function register_custom_bbpress_api() {

	//添加自定义接口
	register_rest_route( 'utils/v2', '/bbpress', [
		'methods'             => 'GET',
		'callback'            => 'api_get_bbpress_replies',
		'permission_callback' => 'is_user_logged_in',

	] );


}


add_action( 'rest_api_init', 'register_custom_bbpress_api' );
