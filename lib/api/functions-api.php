<?php

namespace mikuclub;

use Exception;
use mikuclub\constant\Post_Status;
use WP_Error;
use WP_REST_Request;


/**
 * API转发文章到微博
 *
 * @param WP_REST_Request $data
 *
 * @return bool|mixed|string|WP_Error
 */
function api_share_to_sina($data)
{

	//如果 密钥错误
	if (!isset($data['appkey']) && $data['appkey'] != 173298400)
	{
		return new WP_Error(400, __FUNCTION__ . ' : appkey 参数错误');
	}

	return Weibo_Share::share_to_sina();
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

	try
	{
		$post_id = Input_Validator::get_request_value('post_id', Input_Validator::TYPE_INT, true);
		$aid = Input_Validator::get_request_value('aid', Input_Validator::TYPE_STRING);
		$bvid = Input_Validator::get_request_value('bvid', Input_Validator::TYPE_STRING);

		if (empty($aid) && empty($bvid))
		{
			throw new Empty_Exception('aid和bvid参数');
		}

		$result = Bilibili_Video::get_video_meta($post_id, $aid, $bvid);
	}
	catch (Exception $e)
	{
		$result = new WP_Error(400, $e->getMessage(), __FUNCTION__);
	}

	return $result;
}


/**
 *获取百度盘分享页面HTML
 *
 * @param WP_REST_Request $data
 *
 * @return string | WP_Error
 */
function check_baidu_pan_link($data)
{

	//未设置, 或者 不是百度盘域名
	if (!isset($data['url']) || stripos($data['url'], 'pan.baidu.com') === false)
	{
		return new WP_Error(400, __FUNCTION__ . ' : url 参数错误');
	}

	$url = $data['url'];
	//发起请求
	$response = wp_remote_get($url);
	if (is_wp_error($response))
	{
		return $response;
	}

	//返回请求到的数据
	return wp_remote_retrieve_body($response);
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

	//未设置, 或者 不是百度盘域名
	if (!isset($data['share_id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : share_id 参数错误');
	}

	//阿里云检测地址
	$url = 'https://api.aliyundrive.com/adrive/v3/share_link/get_share_by_anonymous';

	$body = [
		'share_id'  => $data['share_id'],
	];
	$body = wp_json_encode($body);

	$options = [
		'body'        => $body,
		'headers'     => [
			'Content-Type' => 'application/json',
		],
	];

	//发起请求
	$response = wp_remote_post($url, $options);
	if (is_wp_error($response))
	{
		return $response;
	}

	//返回请求到的数据
	return json_decode(wp_remote_retrieve_body($response), true);
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

	$result = [];

	if ($posts)
	{

		foreach ($posts as $single_post)
		{
			wp_delete_post($single_post->ID, true);
			$result[] = $single_post->ID;
		}
	}

	return $result;
}


/**
 *
 * @param WP_REST_Request $data
 * @return array<mixed, mixed>
 */
function test_function($data)
{

	$args = [
		'posts_per_page' => $data['number'],
		'ignore_sticky_posts' => 1,
		'post_status'         => Post_Status::PUBLISH,
		'paged' => $data['paged'],
		'orderby' => 'ID',
	];

	$result = [];
	$posts = get_posts($args);
	foreach ($posts as $post)
	{

		/*$imgs = get_images_large_size($post->ID);
		if (count($imgs) > 1)
		{
			$img = $imgs[1];
			$img = str_replace('www.mikuclub.cc', 'static.mikuclub.cc', $img);
			
			//发起请求
			$response = wp_remote_get($img);
			if (!is_wp_error($response))
			{
				$result[] = $post->ID;
			}
		}*/

		//$img = get_thumbnail_src($post->ID);

		$array_image = get_images_thumbnail_size($post->ID);
		//$array_image = array_merge($array_image, get_images_large_size($post->ID));
		//$array_image = array_merge($array_image, get_images_full_size($post->ID));

		$array_search = [
			'file1.mikuclub.fun',
			'file2.mikuclub.fun',
			'file3.mikuclub.fun',
			'file4.mikuclub.fun',
		];

		$replace = 'file1.mikuclub.fun';

		$array_image = str_replace($array_search, $replace, $array_image);

		$result[$post->ID] = [];

		foreach ($array_image as $image)
		{
			//发起请求
			$response = wp_remote_get($image, [
				'timeout' => 60,
			]);
			$response_code = wp_remote_retrieve_response_code($response);
			$result[$post->ID][] =  $response_code;
		}
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
		'methods'  => 'POST',
		'callback' => 'mikuclub\test_function',
	]);
}


/* 挂载函数到系统中*/
add_action('rest_api_init', 'mikuclub\register_custom_api');
