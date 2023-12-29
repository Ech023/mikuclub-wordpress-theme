<?php

namespace mikuclub;

use Exception;
use mikuclub\constant\Post_Status;
use mikuclub\constant\Web_Domain;
use WP_Error;
use WP_REST_Request;


/**
 * API转发文章到微博
 *
 * @param WP_REST_Request $data
 *
 * @return bool|string|WP_Error
 */
function api_share_to_sina($data)
{
	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$appkey = Input_Validator::get_array_value($data, 'appkey', Input_Validator::TYPE_INT, true);
		if ($appkey !== Weibo_Share::WEIBO_APP_KEY)
		{
			throw new Exception('appkey 参数错误');
		}

		$result = Weibo_Share::share_to_sina();
		return $result;
	});

	return $result;
}


/*代理请求第三方网站的信息============================================================*/

/**
 *通过B站API获取视频相关信息
 *
 * @param WP_REST_Request $data
 *
 * @return array<string,string>|WP_Error
 */
function api_get_bilibili_video_info($data)
{

	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$post_id = Input_Validator::get_array_value($data, 'post_id', Input_Validator::TYPE_INT, true);
		$aid = Input_Validator::get_array_value($data, 'aid', Input_Validator::TYPE_STRING);
		$bvid = Input_Validator::get_array_value($data, 'bvid', Input_Validator::TYPE_STRING);
		if (empty($aid) && empty($bvid))
		{
			throw new Empty_Exception('aid和bvid参数');
		}

		$result = Bilibili_Video::get_video_meta($post_id, $aid, $bvid);
		return $result;
	});

	return $result;
}


/**
 *获取百度盘分享页面HTML
 *
 * @param WP_REST_Request $data
 *
 * @return string|WP_Error
 */
function check_baidu_pan_link($data)
{

	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$url = Input_Validator::get_array_value($data, 'url', Input_Validator::TYPE_STRING, true);
		if (stripos($url, Web_Domain::PAN_BAIDU_COM) === false)
		{
			throw new Exception('url 参数不符合百度网盘地址');
		}

		//发起请求
		$response = wp_remote_get($url);
		if (is_wp_error($response))
		{
			$result = $response;
		}
		else
		{
			//返回请求到的数据
			$result = wp_remote_retrieve_body($response);
		}

		return $result;
	});

	return $result;
}

/**
 *检测阿里云盘链接有效性
 *
 * @param WP_REST_Request $data
 *
 * @return string | WP_Error
 */
function check_aliyun_pan_link($data)
{


	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		//阿里云分享ID
		$share_id = Input_Validator::get_array_value($data, 'share_id', Input_Validator::TYPE_STRING, true);

		$body = wp_json_encode([
			'share_id'  => $share_id,
		]);

		$options = [
			'body'        => $body,
			'headers'     => [
				'Content-Type' => 'application/json',
			],
		];

		//发起请求
		$response = wp_remote_post(Web_Domain::ALIYUN_DRIVE_CHECK, $options);
		if (is_wp_error($response))
		{
			$result =  $response;
		}
		else
		{
			//返回请求到的数据
			$result = json_decode(wp_remote_retrieve_body($response), true);
		}

		return $result;
	});

	return $result;
}


/**
 * 给谷歌广告蜘蛛登陆用的小号
 * @return bool
 */
function login_adsense_account()
{

	//小号ID
	$adsense_user_id = 262357;

	//设置http状态下的登陆cookie
	wp_set_auth_cookie($adsense_user_id, true, false);
	//设置https状态下的登陆cookie
	wp_set_auth_cookie($adsense_user_id, true, true);
	//设置当前用户
	wp_set_current_user($adsense_user_id);

	return true;
}


/**
 * 清空回收站里的文章
 * @return int[]
 */
function delete_trash_post()
{

	$args = [
		'post_status'     => 'trash',
		'posts_per_page' => 1,
	];

	$posts = get_posts($args);

	$result = array_map(function ($post)
	{

		wp_delete_post($post->ID, true);
		return $post->ID;
	}, $posts);


	return $result;
}


/**
 *
 * @param WP_REST_Request $data
 * @return void
 */
function test_function($data)
{
	// //小号ID
	// $adsense_user_id = 309794;

	// //设置http状态下的登陆cookie
	// wp_set_auth_cookie($adsense_user_id, true, false);
	// //设置https状态下的登陆cookie
	// wp_set_auth_cookie($adsense_user_id, true, true);
	// //设置当前用户
	// wp_set_current_user($adsense_user_id);

	// return true;

	$number = Input_Validator::get_array_value($data, 'number', Input_Validator::TYPE_INT, true);

	$paged = Input_Validator::get_array_value($data, 'paged', Input_Validator::TYPE_INT, true);

	$args = [
		'posts_per_page' => $number,
		'ignore_sticky_posts' => 1,
		'post_status'         => Post_Status::PUBLISH,
		'paged' => $paged,
		'orderby' => 'ID',
		'fields' => 'ids',  // 设置为 'ids' 只获取文章 ID
	];

	$result = [];
	$array_id = get_posts($args);
	foreach ($array_id as $post_id)
	{
		//更新所有大小版本的图片地址
		Post_Image::update_all_array_image_src($post_id);
		//更新缩微图图片地址
		Post_Image::set_thumbnail_src($post_id);


		// //更新文章的下载属性
		// set_post_array_down_type($post_id);

		// // $array_image = Post_Image::get_array_image_thumbnail_src($post->ID);
		// //$array_image = array_merge($array_image, Post_Image::get_array_image_large_src($post->ID));
		// //$array_image = array_merge($array_image, Post_Image::get_array_image_full_src($post->ID));

		// $array_search = [
		// 	'file1.mikuclub.fun',
		// 	'file2.mikuclub.fun',
		// 	'file3.mikuclub.fun',
		// 	'file4.mikuclub.fun',
		// ];

		// $replace = 'file1.mikuclub.fun';

		// $array_image = str_replace($array_search, $replace, $array_image);

		// $result[$post->ID] = [];

		// foreach ($array_image as $image)
		// {
		// 	//发起请求
		// 	$response = wp_remote_get($image, [
		// 		'timeout' => 60,
		// 	]);
		// 	$response_code = wp_remote_retrieve_response_code($response);
		// 	$result[$post->ID][] =  $response_code;
		// }
	}

	return $array_id;
}



/**
 * 通过try catch 运行代码, 并在有错误的情况下返回 WP_Error
 *
 * @param callable $callable
 * @return mixed|WP_Error
 */
function execute_with_try_catch_wp_error($callable)
{

	try
	{
		$result = $callable();
	}
	catch (Exception $e)
	{
		$result = new WP_Error(400, $e->getMessage(), __FUNCTION__);
	}

	return $result;
}


/**
 * 注册自定义 api 接口
 * 
 * @return void
 */
function register_custom_api()
{

	register_rest_route('utils/v2', '/sharing_to_sina', [
		'methods'  => 'POST',
		'callback' => 'mikuclub\api_share_to_sina',
	]);

	register_rest_route('utils/v2', '/get_bilibili_video_info', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\api_get_bilibili_video_info',
	]);


	register_rest_route('utils/v2', '/check_baidu_pan_link', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\check_baidu_pan_link',
	]);

	register_rest_route('utils/v2', '/check_aliyun_pan_link', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\check_aliyun_pan_link',
	]);

	register_rest_route('utils/v2', '/login_adsense_account', [
		'methods'  => 'POST',
		'callback' => 'mikuclub\login_adsense_account',
	]);


	register_rest_route('utils/v2', '/delete_trash_post', [
		'methods'  => 'DELETE',
		'callback' => 'mikuclub\delete_trash_post',
	]);



	register_rest_route('utils/v2', '/test', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\test_function',
	]);
}
