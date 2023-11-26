<?php

namespace mikuclub;

use mikuclub\constant\User_Meta;
use WP_Error;
use WP_REST_Request;




/**
 * API 获取作者实例
 * 因为官方users接口有访问权限限制
 * 所以再自定义一个用户接口来获取作者信息
 *
 * @param WP_REST_Request $data ['id'=>作者id, 'full_view'=>是否要获取额外信息]
 *
 * @return My_User_Model|WP_Error
 */
function api_get_author($data)
{

	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$id = Input_Validator::get_array_value($data, 'id', Input_Validator::TYPE_INT, true);

		$result = get_custom_user($id);
		return $result;
	});

	return $result;
}


/**
 * API 获取用户收藏夹 (文章id列表)
 * @return int[]
 */
function api_get_user_favorite()
{
	return get_user_favorite();
}

/**
 * API添加文章id到收藏夹
 *
 * @param WP_REST_Request $data ['post_id' => xxx]
 *
 * @return int[]|WP_Error
 */
function api_add_user_favorite($data)
{

	return execute_with_try_catch_wp_error(function () use ($data)
	{

		$post_id = Input_Validator::get_array_value($data, 'post_id', Input_Validator::TYPE_INT, true);

		add_user_favorite($post_id);

		//为了兼容APP 需要返回新的收藏列表
		$result = get_user_favorite();
		return $result;
	});
}

/**
 * API从收藏夹删除文章id
 *
 * @param WP_REST_Request $data ['post_id' => xxx]
 *
 * @return int[]|WP_Error
 */
function api_delete_user_favorite($data)
{
	return execute_with_try_catch_wp_error(function () use ($data)
	{

		$post_id = Input_Validator::get_array_value($data, 'post_id', Input_Validator::TYPE_INT, true);

		delete_user_favorite($post_id);

		//为了兼容APP 需要返回新的收藏列表
		$result = get_user_favorite();
		return $result;
	});
}

/**
 * API获取用户关注
 * 
 * @return int[]
 */
function api_get_user_followed()
{
	return get_user_followed();
}


/**
 * API添加用户关注
 *
 * @param WP_REST_Request $data ['user_id' => xxx]
 *
 * @return boolean|WP_Error
 */
function api_add_user_followed($data)
{
	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$target_user_id = Input_Validator::get_array_value($data, 'target_user_id', Input_Validator::TYPE_INT, true);

		//增加被关注用户的粉丝数
		add_user_fans_count($target_user_id);

		$result = add_user_followed($target_user_id);
		return $result;
	});

	return $result;
}

/**
 * API取消用户关注
 *
 * @param WP_REST_Request $data ['user_id' => xxx]
 *
 * @return boolean|WP_Error
 */
function api_delete_user_followed($data)
{
	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$target_user_id = Input_Validator::get_array_value($data, 'target_user_id', Input_Validator::TYPE_INT, true);

		//删除被取消关注用户的粉丝数
		delete_user_fans_count($target_user_id);

		$result = delete_user_followed($target_user_id);
		return $result;
	});

	return $result;
}

/**
 * API获取用户黑名单
 * @return int[]
 */
function api_get_user_black_list()
{
	$user_id = get_current_user_id();
	return get_user_black_list($user_id);
}


/**
 * API添加ID到用户黑名单
 *
 * @param WP_REST_Request $data ['target_user_id' => xxx]
 *
 * @return boolean|WP_Error
 */
function api_add_user_black_list($data)
{
	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$user_id = get_current_user_id();
		$target_user_id = Input_Validator::get_array_value($data, 'target_user_id', Input_Validator::TYPE_INT, true);

		$result = add_user_black_list($user_id, $target_user_id);
		return $result;
	});

	return $result;
}

/**
 * API 从用户黑名单里移除ID
 *
 * @param WP_REST_Request $data ['target_user_id' => xxx]
 *
 * @return boolean|WP_Error
 */
function api_delete_user_black_list($data)
{
	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$user_id = get_current_user_id();
		$target_user_id = Input_Validator::get_array_value($data, 'target_user_id', Input_Validator::TYPE_INT, true);

		$result = delete_user_black_list($user_id, $target_user_id);
		return $result;
	});

	return $result;
}


/**
 * 在wp/v2/users  回复中增加自定义 metadata数据
 *
 * @param WP_REST_Request $data
 *
 * @return array<string, mixed>
 */
function api_custom_user_metadata($data)
{

	$user_id = $data['id'];

	$metadata = [
		//增加头像地址
		'avatar_src' => get_my_user_avatar($user_id),
	];

	return $metadata;
}



/**
 * 在API中给user添加自定义meta元数据支持
 * 
 * @return void
 **/
function register_custom_user_metadata()
{

	$integer_meta_args = [
		'type'         => 'integer',
		'description'  => 'custom integer field',
		'single'       => true,
		'show_in_rest' => true,
	];

	register_meta('user', User_Meta::USER_AVATAR, $integer_meta_args);

	//在回复中添加自定义数据
	register_rest_field(
		'user',
		'metadata',
		[
			'get_callback' => 'api_custom_user_metadata',
		]
	);

	register_rest_route('utils/v2', '/author/(?P<id>\d+)', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\api_get_author',
	]);

	register_rest_route('utils/v2', '/favorite', [
		[
			'methods'             => 'GET',
			'callback'            => 'mikuclub\api_get_user_favorite',
			'permission_callback' => 'is_user_logged_in',
		],
		[
			'methods'             => 'POST',
			'callback'            => 'mikuclub\api_add_user_favorite',
			'permission_callback' => 'is_user_logged_in',
		],
		[
			'methods'             => 'DELETE',
			'callback'            => 'mikuclub\api_delete_user_favorite',
			'permission_callback' => 'is_user_logged_in',
		],
	]);

	register_rest_route('utils/v2', '/user_followed', [
		[
			'methods'             => 'GET',
			'callback'            => 'mikuclub\api_get_user_followed',
			'permission_callback' => 'is_user_logged_in',
		],
		[
			'methods'             => 'POST',
			'callback'            => 'mikuclub\api_add_user_followed',
			'permission_callback' => 'is_user_logged_in',
		],
		[
			'methods'             => 'DELETE',
			'callback'            => 'mikuclub\api_delete_user_followed',
			'permission_callback' => 'is_user_logged_in',
		],
	]);

	register_rest_route('utils/v2', '/user_black_list', [
		[
			'methods'             => 'GET',
			'callback'            => 'mikuclub\api_get_user_black_list',
			'permission_callback' => 'is_user_logged_in',
		],
		[
			'methods'             => 'POST',
			'callback'            => 'mikuclub\api_add_user_black_list',
			'permission_callback' => 'is_user_logged_in',
		],
		[
			'methods'             => 'DELETE',
			'callback'            => 'mikuclub\api_delete_user_black_list',
			'permission_callback' => 'is_user_logged_in',
		],
	]);
}
