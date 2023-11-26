<?php

namespace mikuclub;

use Exception;
use mikuclub\constant\Post_Meta;
use mikuclub\User_Capability;
use WP_Error;
use WP_REST_Request;

/**
 * 增加文章查看次数
 *
 * @param WP_REST_Request $data
 *
 * @return int|WP_Error
 */
function api_add_post_views($data)
{

	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$post_id = Input_Validator::get_array_value($data, 'post_id', Input_Validator::TYPE_INT, true);
		$view_number = Input_Validator::get_array_value($data, 'view_number', Input_Validator::TYPE_INT, false) ?: 1;

		$result = add_post_views($post_id, $view_number);
		return $result;
	});

	return $result;
}


/**
 * 增加文章分享次数
 *
 * @param WP_REST_Request $data
 *
 * @return int|WP_Error
 **/
function api_add_post_shares($data)
{
	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$post_id = Input_Validator::get_array_value($data, 'post_id', Input_Validator::TYPE_INT, true);

		$result = add_post_shares($post_id);
		return $result;
	});

	return $result;
}


/**
 * 设置文章点赞次数
 *
 * @param WP_REST_Request $data
 *
 * @return int|WP_Error
 **/
function api_set_post_like($data)
{

	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$post_id = Input_Validator::get_array_value($data, 'post_id', Input_Validator::TYPE_INT, true);
		$cancel = Input_Validator::get_array_value($data, 'cancel', Input_Validator::TYPE_INT, false);

		if ($cancel)
		{
			$result = add_post_like($post_id);
		}
		else
		{
			$result = delete_post_like($post_id);
		}

		return $result;
	});

	return $result;
}

/**
 * 设置文章差评次数
 *
 * @param WP_REST_Request $data
 *
 * @return int|WP_Error
 **/
function api_set_post_unlike($data)
{

	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$post_id = Input_Validator::get_array_value($data, 'post_id', Input_Validator::TYPE_INT, true);
		$cancel = Input_Validator::get_array_value($data, 'cancel', Input_Validator::TYPE_INT, false);

		if ($cancel)
		{
			$result = add_post_unlike($post_id);
		}
		else
		{
			$result = delete_post_unlike($post_id);
		}


		return $result;
	});

	return $result;
}


/**
 * API刷新文章的创建时间
 *
 * @param WP_REST_Request $data
 *
 * @return boolean|WP_Error 成功的情况 返回文章id \ 错误的情况 返回0 或者 WpError对象
 */
function api_update_post_date($data)
{

	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$post_id = Input_Validator::get_array_value($data, 'post_id', Input_Validator::TYPE_INT, true);

		//只有高级用户有权限
		if (!User_Capability::is_premium_user())
		{
			throw new Exception('无权进行该项操作');
		}

		$result = update_post_date($post_id);
		return $result;
	});

	return $result;
}


/**
 * 设置文章置顶
 *
 * @param WP_REST_Request $data
 *
 * @return boolean|WP_Error
 **/
function api_add_sticky_posts($data)
{

	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$post_id = Input_Validator::get_array_value($data, 'post_id', Input_Validator::TYPE_INT, true);

		$result = add_sticky_posts($post_id);
		return $result;
	});

	return $result;
}

/**
 * 取消文章置顶
 *
 * @param WP_REST_Request $data
 *
 * @return boolean|WP_Error
 **/
function api_delete_sticky_posts($data)
{
	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$post_id = Input_Validator::get_array_value($data, 'post_id', Input_Validator::TYPE_INT, true);

		$result = delete_sticky_posts($post_id);
		return $result;
	});

	return $result;
}


/**
 * API 驳回稿件
 *
 * @param WP_REST_Request $data
 *
 * @return bool|WP_Error
 */
function api_reject_post($data)
{

	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$post_id = Input_Validator::get_array_value($data, 'post_id', Input_Validator::TYPE_INT, true);
		$cause = Input_Validator::get_array_value($data, 'cause', Input_Validator::TYPE_STRING, false) ?: '';

		reject_post($post_id, $cause);
		return true;
	});

	return $result;
}



/**
 * API 设置失效次数
 * @param WP_REST_Request $data
 *
 * @return int|WP_Error
 */
function api_set_post_fail_times($data)
{


	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$post_id = Input_Validator::get_array_value($data, 'post_id', Input_Validator::TYPE_INT, true);
		$disable = Input_Validator::get_array_value($data, 'disable', Input_Validator::TYPE_BOOL, false);
		$reset = Input_Validator::get_array_value($data, 'reset', Input_Validator::TYPE_BOOL, false);


		if (User_Capability::is_admin() && $disable)
		{
			$result = update_post_fail_times($post_id, -1);
		}
		else if (User_Capability::is_admin() && $reset)
		{
			$result = update_post_fail_times($post_id, 0);
		}
		else
		{
			$result = add_post_fail_times($post_id);
		}

		return $result;
	});

	return $result;
}


/**
 *API 更新文章元数据 (包括 文章 和 附件图片)
 *
 * @param WP_REST_Request $data ['post_id'=>文章或附件id, 'meta_key'=>元数据键名, 'meta_value'=>元数据键值]
 *
 * @return bool|WP_Error
 */
function api_update_post_meta($data)
{

	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$post_id = Input_Validator::get_array_value($data, 'post_id', Input_Validator::TYPE_INT, true);
		$meta_key = Input_Validator::get_array_value($data, 'meta_key', Input_Validator::TYPE_STRING, true);
		$meta_value = Input_Validator::get_array_value($data, 'meta_value', Input_Validator::TYPE_STRING, true);

		//如果数值是数值
		if (is_numeric($meta_value))
		{
			//如果是整数
			if (is_int($meta_value + 0))
			{
				$meta_value = intval($meta_value);
			}
			//否则是浮点数
			else
			{
				$meta_value = floatval($meta_value);
			}
		}

		$user_id = get_current_user_id();
		$author_id = intval(get_post_field('post_author', $post_id));

		//如果不是作者本人 或 管理员
		if ($user_id !== $author_id && !User_Capability::is_admin())
		{
			throw new Exception('无权进行该项操作');
		}

		//更新元数据
		$result = update_post_meta($post_id, $meta_key, $meta_value);

		if ($result === false)
		{
			throw new Exception('更新meta数据失败');
		}

		return $result;
	});

	return $result;
}

/**
 * API 撤回稿件
 *
 * @param WP_REST_Request $data
 *
 * @return bool|WP_Error
 */
function api_draft_post($data)
{

	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$post_id = Input_Validator::get_array_value($data, 'post_id', Input_Validator::TYPE_INT, true);

		$result = draft_post($post_id);
		return $result;
	});

	return $result;
}





/**
 * 在wp/v2/posts  回复中增加自定义 metadata数据
 * @param WP_REST_Request $data
 * @return array<string, mixed>
 */
function api_custom_post_metadata($data)
{

	$post_id = $data['id'];

	//获取所有meta数据 (如果键值是数组 需要自己反序列化)
	$metadata = get_post_meta($post_id, '', false);

	//如果未设置
	if (!isset($metadata[Post_Meta::POST_THUMBNAIL_SRC]))
	{
		$metadata[Post_Meta::POST_THUMBNAIL_SRC] = [];
	}
	if (empty($metadata[Post_Meta::POST_THUMBNAIL_SRC]) || empty($metadata[Post_Meta::POST_THUMBNAIL_SRC][0]))
	{
		//重新获取预览图地址
		$metadata[Post_Meta::POST_THUMBNAIL_SRC][0] = Post_Image::get_thumbnail_src($post_id);
	}

	//重新获取一遍各个大小的预览图片地址数组
	//因为默认批量获取 没有对数组进行反反序列化
	$metadata[Post_Meta::POST_IMAGES_THUMBNAIL_SRC] = Post_Image::get_array_image_thumbnail_src($post_id);
	$metadata[Post_Meta::POST_IMAGES_SRC]           = Post_Image::get_array_image_large_src($post_id);
	$metadata[Post_Meta::POST_IMAGES_FULL_SRC]      = Post_Image::get_array_image_full_src($post_id);


	/*
	//只替换post_id 偶数
	if ($post_id % 2 === 0)
	{
		//给app的大图 使用5号CDN虚拟机
		$metadata[Post_Meta::POST_IMAGES_SRC]           = fix_image_domain_with_file_6($metadata[Post_Meta::POST_IMAGES_SRC]);
	}*/

	//$metadata[Post_Meta::POST_THUMBNAIL_SRC][0] = fix_image_domain_with_file_6($metadata[Post_Meta::POST_THUMBNAIL_SRC][0]);




	//$metadata[Post_Meta::POST_IMAGES_SRC] = str_replace($array_search, $replace, $metadata[Post_Meta::POST_IMAGES_SRC]);
	//$metadata[Post_Meta::POST_IMAGES_FULL_SRC] = str_replace($array_search, $replace, $metadata[Post_Meta::POST_IMAGES_FULL_SRC]);


	//替换为 sugar虚拟机CDN2号的地址
	//$metadata[Post_Meta::POST_THUMBNAIL_SRC] = str_replace('www.mikuclub.cc', 'static.mikuclub.fun', $metadata[Post_Meta::POST_THUMBNAIL_SRC] );
	//$metadata[Post_Meta::POST_IMAGES_THUMBNAIL_SRC] = str_replace('www.mikuclub.cc', 'static.mikuclub.fun', $metadata[Post_Meta::POST_IMAGES_THUMBNAIL_SRC] );
	//$metadata[Post_Meta::POST_IMAGES_SRC] = str_replace('www.mikuclub.cc', 'static.mikuclub.fun', $metadata[Post_Meta::POST_IMAGES_SRC] );
	//$metadata[Post_Meta::POST_IMAGES_FULL_SRC] = str_replace('www.mikuclub.cc', 'static.mikuclub.fun', $metadata[Post_Meta::POST_IMAGES_FULL_SRC] );



	//获取作者信息
	$metadata['author'] = [get_custom_user($data['author'])];

	//获取评论总数
	$metadata['count_comments'] = [get_comments_number($post_id)];


	//获取图片预览id数组 (以后可以改进, 只有在编辑的时候需要用到)
	$metadata['previews'] = Post_Image::get_array_image_id($post_id);


	return $metadata;
}




/**
 * 注册自定义 api 接口
 * 
 * @return void
 */
function register_custom_post_api()
{

	register_rest_route('utils/v2', '/post_view_count', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\api_add_post_views',
	]);

	register_rest_route('utils/v2', '/post_sharing_count', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\api_add_post_shares',
	]);

	register_rest_route('utils/v2', '/post_like_count', [
		'methods'  => 'POST',
		'callback' => 'mikuclub\api_set_post_like',
	]);

	register_rest_route('utils/v2', '/post_unlike_count', [
		'methods'  => 'POST',
		'callback' => 'mikuclub\api_set_post_unlike',
	]);

	register_rest_route('utils/v2', '/update_post_date', [
		'methods'             => 'POST',
		'callback'            => 'mikuclub\api_update_post_date',
		'permission_callback' => 'is_user_logged_in',
	]);


	register_rest_route('utils/v2', '/sticky_posts', [
		[
			'methods'             => 'POST',
			'callback'            => 'mikuclub\api_add_sticky_posts',
			'permission_callback' => ['mikuclub\User_Capability', 'is_admin'],
		],
		[
			'methods'             => 'DELETE',
			'callback'            => 'mikuclub\api_delete_sticky_posts',
			'permission_callback' => ['mikuclub\User_Capability', 'is_admin'],
		],
	]);

	register_rest_route('utils/v2', '/reject_post', [
		'methods'             => 'POST',
		'callback'            => 'mikuclub\api_reject_post',
		'permission_callback' => ['mikuclub\User_Capability', 'is_admin'],
	]);

	register_rest_route('utils/v2', '/draft_post', [
		'methods'             => 'POST',
		'callback'            => 'mikuclub\api_draft_post',
		'permission_callback' => 'is_user_logged_in',
	]);

	register_rest_route('utils/v2', '/fail_down', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\api_set_post_fail_times',
	]);

	register_rest_route('utils/v2', '/post_meta', [
		'methods'             => 'POST',
		'callback'            => 'mikuclub\api_update_post_meta',
		'permission_callback' => 'is_user_logged_in',
	]);

	register_rest_field(
		'post',
		'metadata',
		[
			'get_callback' => 'api_custom_post_metadata',
		]
	);
}
