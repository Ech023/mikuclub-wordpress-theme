<?php
namespace mikuclub;

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


	//如果缺少必要参数
	if (!isset_numeric($data['post_id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : post_id 参数错误');
	}

	$view_number = $data['view_number'] ?? null;

	return add_post_views($data['post_id'], $view_number);
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

	//如果缺少必要参数
	if (!isset_numeric($data['post_id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : post_id 参数错误');
	}

	return add_post_shares($data['post_id']);
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

	//如果缺少必要参数
	if (!isset_numeric($data['post_id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : post_id 参数错误');
	}

	//默认 增加点赞
	if (!isset($data['cancel']))
	{
		$count = add_post_like($data['post_id']);
	}
	//取消点赞
	else
	{
		$count = delete_post_like($data['post_id']);
	}

	return $count;
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

	//如果缺少必要参数
	if (!isset($data['post_id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : post_id 参数错误');
	}

	//默认 增加点赞
	if (!isset($data['cancel']))
	{
		$count = add_post_unlike($data['post_id']);
	}
	//取消点赞
	else
	{
		$count = delete_post_unlike($data['post_id']);
	}

	return $count;
}


/**
 * API刷新文章的创建时间
 *
 * @param WP_REST_Request $data
 *
 * @return int|WP_Error 成功的情况 返回文章id \ 错误的情况 返回0 或者 WpError对象
 */
function api_update_post_date($data)
{

	//如果缺少必要参数
	if (!isset_numeric($data['post_id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : post_id 参数错误');
	}

	return update_post_date($data['post_id']);
}


/**
 * 设置文章置顶
 *
 * @param WP_REST_Request $data
 *
 * @return boolean | WP_Error
 **/
function api_add_sticky_posts($data)
{

	//如果缺少必要参数
	if (!isset_numeric($data['post_id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : post_id 参数错误');
	}

	return add_sticky_posts($data['post_id']);
}

/**
 * 取消文章置顶
 *
 * @param WP_REST_Request $data
 *
 * @return boolean | WP_Error
 **/
function api_delete_sticky_posts($data)
{

	//如果缺少必要参数
	if (!isset_numeric($data['post_id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : post_id 参数错误');
	}

	return delete_sticky_posts($data['post_id']);
}


/**
 * API 驳回稿件
 *
 * @param WP_REST_Request $data
 *
 * @return bool | WP_Error
 */
function api_reject_post($data)
{

	//如果缺少必要参数
	if (!isset_numeric($data['post_id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : post_id 参数错误');
	}

	//如果有自定义退稿原因
	if (isset($data['cause']) && $data['cause'])
	{
		reject_post($data['post_id'], $data['cause']);
	}
	else
	{
		reject_post($data['post_id']);
	}


	return true;
}



/**
 * API 设置失效次数
 * @param WP_REST_Request $data
 *
 * @return int|WP_Error
 */
function api_set_post_fail_times($data)
{

	//如果缺少必要参数
	if (!isset_numeric($data['post_id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : post_id 参数错误');
	}

	//如果是要注销失效计数
	if (isset($data['disable']) && current_user_is_admin())
	{

		$count = update_post_fail_times($data['post_id'], -1);
	}
	//如果是要清零
	else if (isset($data['reset']) && current_user_is_admin())
	{

		$count = update_post_fail_times($data['post_id'], 0);
	}
	//默认 增加计数
	else
	{
		$count = add_post_fail_times($data['post_id']);
	}


	return $count;
}


/**
 *API 更新文章元数据 (包括 文章 和 附件图片)
 *
 * @param WP_REST_Request $data ['post_id'=>文章或附件id, 'meta_key'=>元数据键名, 'meta_value'=>元数据键值]
 *
 * @return bool | WP_Error
 */
function api_update_post_meta($data)
{

	//如果缺少必要参数
	if (!isset_numeric($data['post_id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : post_id 参数错误');
	}
	if (!isset($data['meta_key']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : meta_key 参数错误');
	}
	if (!isset($data['meta_value']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : meta_value 参数错误');
	}

	$post_id    = $data['post_id'];
	$meta_key   = $data['meta_key'];
	$meta_value = $data['meta_value'];
	$user_id    = get_current_user_id();
	$author_id  = get_post_field('post_author', $post_id);

	//如果不是作者本人 或 管理员
	if ($user_id != $author_id && !current_user_is_admin())
	{
		return new WP_Error(401, __FUNCTION__ . ' : 无权进行该项操作');
	}

	//更新元数据
	$result = update_post_meta($post_id, $meta_key, $meta_value);

	if ($result === false)
	{
		return new WP_Error(500, __FUNCTION__ . ' : 更新meta数据失败');
	}

	return $result;
}

/**
 * API 撤回稿件
 *
 * @param WP_REST_Request $data
 *
 * @return bool | WP_Error
 */
function api_draft_post($data)
{

	//如果缺少必要参数
	if (!isset_numeric($data['post_id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : post_id 参数错误');
	}
	draft_post($data['post_id']);
	return true;
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
		$metadata[Post_Meta::POST_THUMBNAIL_SRC][0] = get_thumbnail_src($post_id);
	}

	//重新获取一遍各个大小的预览图片地址数组
	//因为默认批量获取 没有对数组进行反反序列化
	$metadata[Post_Meta::POST_IMAGES_THUMBNAIL_SRC] = get_images_thumbnail_size($post_id);
	$metadata[Post_Meta::POST_IMAGES_SRC]           = get_images_large_size($post_id);
	$metadata[Post_Meta::POST_IMAGES_FULL_SRC]      = get_images_full_size($post_id);


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
	$metadata['author'] = [get_custom_author($data['author'])];

	//获取评论总数
	$metadata['count_comments'] = [get_comments_number($post_id)];


	//获取图片预览id数组 (以后可以改进, 只有在编辑的时候需要用到)
	$metadata['previews'] = get_image_ids_from_form_field_by_post_id($post_id);


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
		'callback' => 'api_add_post_views',
	]);

	register_rest_route('utils/v2', '/post_sharing_count', [
		'methods'  => 'GET',
		'callback' => 'api_add_post_shares',
	]);

	register_rest_route('utils/v2', '/post_like_count', [
		'methods'  => 'POST',
		'callback' => 'api_set_post_like',
	]);

	register_rest_route('utils/v2', '/post_unlike_count', [
		'methods'  => 'POST',
		'callback' => 'api_set_post_unlike',
	]);

	register_rest_route('utils/v2', '/update_post_date', [
		'methods'             => 'POST',
		'callback'            => 'api_update_post_date',
		'permission_callback' => 'is_user_logged_in',
	]);


	register_rest_route('utils/v2', '/sticky_posts', [
		[
			'methods'             => 'POST',
			'callback'            => 'api_add_sticky_posts',
			'permission_callback' => 'current_user_is_admin',
		],
		[
			'methods'             => 'DELETE',
			'callback'            => 'api_delete_sticky_posts',
			'permission_callback' => 'current_user_is_admin',
		],
	]);

	register_rest_route('utils/v2', '/reject_post', [
		'methods'             => 'POST',
		'callback'            => 'api_reject_post',
		'permission_callback' => 'current_user_is_admin',
	]);

	register_rest_route('utils/v2', '/draft_post', [
		'methods'             => 'POST',
		'callback'            => 'api_draft_post',
		'permission_callback' => 'is_user_logged_in',
	]);

	register_rest_route('utils/v2', '/fail_down', [
		'methods'  => 'GET',
		'callback' => 'api_set_post_fail_times',
	]);

	register_rest_route('utils/v2', '/post_meta', [
		'methods'             => 'POST',
		'callback'            => 'api_update_post_meta',
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

/* 挂载函数到系统中*/
add_action('rest_api_init', 'mikuclub\register_custom_post_api');
