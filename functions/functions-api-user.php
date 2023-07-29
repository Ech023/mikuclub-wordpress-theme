<?php


/**
 * JWT API 登陆跳过极验证专用
 * 通过使用 session变量 来跳过极验证
 *
 * @param $default_header
 *
 * @return mixed
 */
function api_pass_gee_test( $default_header ) {

	$_SESSION['login_from_api'] = true;

	return $default_header;
}

add_action( 'jwt_auth_cors_allow_headers', 'api_pass_gee_test', 100, 1 );

/**
 * 修改 JWT API登陆 token令牌的过期时间
 * 默认为 7天,  改为180天
 */
function modify_jwt_auth_expire() {

	$expire_days = 180;

	return time() + ( EXPIRED_1_DAY * $expire_days );

}

add_action( 'jwt_auth_expire', 'modify_jwt_auth_expire' );


/**
 * 修改 JWT API登陆后 返回的数据
 *
 * @param array $data
 * @param array $user
 *
 * @return array
 */
function modify_jwt_auth_response( $data, $user ) {
	$data['id']          = $user->ID;
	$data['user_login']  = $user->data->user_login;
	$data['user_meta']   = get_user_meta( $user->ID, '' );
	$data['avatar_urls'] = get_my_user_avatar( $user->ID );

	return $data;
}

add_action( 'jwt_auth_token_before_dispatch', 'modify_jwt_auth_response', 10, 2 );





/**
 * API 获取作者实例
 * 因为官方users接口有访问权限限制
 * 所以再自定义一个用户接口来获取作者信息
 *
 * @param $data ['id'=>作者id, 'full_view'=>是否要获取额外信息]
 *
 * @return My_User | My_System_User | WP_Error
 */
function api_get_author( $data ) {

	//如果参数错误
	if ( ! isset_numeric( $data['id'] ) ) {
		return new WP_Error( 400, __FUNCTION__ . ' : ID 参数错误' );
	}

	$author = get_custom_author( $data['id'] );

	//如果需要查看完整作者信息
	/*if ( ! empty( $data['full_view'] ) ) {

	}*/

	return $author;

}


/**
 * API 获取用户收藏夹 (文章id列表)
 * @return array
 */
function api_get_user_favorite() {
	return get_user_favorite();
}

/**
 * API添加文章id到收藏夹
 *
 * @param array $data ['post_id' => xxx]
 *
 * @return int[]|WP_Error
 */
function api_add_user_favorite( $data ) {

	if ( ! isset_numeric( $data['post_id'] ) ) {
		return new WP_Error( 400, __FUNCTION__ . ' : post_id 参数错误' );
	}

	return add_user_favorite( $data['post_id'] );

}

/**
 * API从收藏夹删除文章id
 *
 * @param array $data ['post_id' => xxx]
 *
 * @return int[]|WP_Error
 */
function api_delete_user_favorite( $data ) {

	if ( ! isset_numeric( $data['post_id'] ) ) {
		return new WP_Error( 400, __FUNCTION__ . ' : post_id 参数错误' );
	}

	return delete_user_favorite( $data['post_id'] );
}


/**
 * 在wp/v2/users  回复中增加自定义 metadata数据
 *
 * @param array $data
 *
 * @return array
 */
function api_custom_user_metadata( $data ) {

	$user_id = $data['id'];

	$metadata = [];
	//增加头像地址
	$metadata['avatar_src'] = get_my_user_avatar( $user_id );

	return $metadata;
}

/**
 * API获取用户关注
 * @return int[]|WP_Error
 */
function api_get_user_followed() {

	return get_user_followed();

}


/**
 * API添加用户关注
 *
 * @param array $data ['user_id' => xxx]
 *
 * @return boolean | WP_Error
 */
function api_add_user_followed( $data ) {

	if ( ! isset_numeric( $data['user_id'] ) ) {
		return new WP_Error( 400, __FUNCTION__ . ' : user_id 参数错误' );
	}
	//增加被关注用户的粉丝数
	set_user_fans_count($data['user_id'], true);

	return set_user_followed( $data['user_id'], true );

}

/**
 * API取消用户关注
 *
 * @param array $data ['user_id' => xxx]
 *
 * @return boolean | WP_Error
 */
function api_delete_user_followed( $data ) {

	if ( ! isset_numeric( $data['user_id'] ) ) {
		return new WP_Error( 400, __FUNCTION__ . ' : user_id 参数错误' );
	}
	//删除被取消关注用户的粉丝数
	set_user_fans_count($data['user_id'], false);

	return set_user_followed( $data['user_id'], false );
}


/**
 * 在API中给user添加自定义meta元数据支持
 **/
function register_custom_user_metadata() {

	$integer_meta_args = [
		'type'         => 'integer',
		'description'  => 'custom integer field',
		'single'       => true,
		'show_in_rest' => true,
	];

	register_meta( 'user', MY_USER_AVATAR, $integer_meta_args );

	register_rest_route( 'utils/v2', '/author/(?P<id>\d+)', [
		'methods'  => 'GET',
		'callback' => 'api_get_author',
	] );

	register_rest_route( 'utils/v2', '/favorite', [
		[
			'methods'             => 'GET',
			'callback'            => 'api_get_user_favorite',
			'permission_callback' => 'is_user_logged_in',
		],
		[
			'methods'             => 'POST',
			'callback'            => 'api_add_user_favorite',
			'permission_callback' => 'is_user_logged_in',
		],
		[
			'methods'             => 'DELETE',
			'callback'            => 'api_delete_user_favorite',
			'permission_callback' => 'is_user_logged_in',
		],
	] );

	register_rest_route( 'utils/v2', '/user_followed', [
		[
			'methods'             => 'GET',
			'callback'            => 'api_get_user_followed',
			'permission_callback' => 'is_user_logged_in',
		],
		[
			'methods'             => 'POST',
			'callback'            => 'api_add_user_followed',
			'permission_callback' => 'is_user_logged_in',
		],
		[
			'methods'             => 'DELETE',
			'callback'            => 'api_delete_user_followed',
			'permission_callback' => 'is_user_logged_in',
		],
	] );


	//在回复中添加自定义数据
	register_rest_field( 'user', 'metadata', [
			'get_callback' => 'api_custom_user_metadata',
		]
	);


}


add_action( 'rest_api_init', 'register_custom_user_metadata' );