<?php

namespace mikuclub;

use WP_Error;
use WP_REST_Request;

/**
 * 通过api获取文章列表
 *
 * @param WP_REST_Request $data
 * @return array<string, mixed>
 * [
 *  'posts' => My_Post_Model[],
 *  'max_num_pages' => int, 总页数
 * ]
 */
function api_get_post_list($data)
{
	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		//获取请求参数
		$data = $data->get_params();

		// $paged = Input_Validator::get_array_value($data, 'paged', Input_Validator::TYPE_INT, false) ?: 1;

		//查询文章
		$result = get_post_list($data);

		// //如果是作者页面
		// if (isset($data['page_type']) && $data['page_type'] == 'author')
		// {
		// 	//清空文章列表中的作者信息
		// 	foreach ($post_list as $My_Post_Model)
		// 	{
		// 		//$My_Post_Model->post_author = null;
		// 	}
		// }

		return $result;
	});

	return $result;
}


/**
 * 通过api获取收藏夹文章列表
 *
 * @param WP_REST_Request $data
 *
 * @return My_Post_Model[]|WP_Error
 */
function api_get_my_favorite_post_list($data)
{

	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$cat = Input_Validator::get_array_value($data, 'cat', Input_Validator::TYPE_INT, false);
		$search = Input_Validator::get_array_value($data, 's', Input_Validator::TYPE_STRING, false);
		$paged = Input_Validator::get_array_value($data, 'paged', Input_Validator::TYPE_INT, false) ?: 1;

		$result = get_my_favorite_post_list($cat, $search, $paged);
		return $result;
	});

	return $result;
}



/**
 * 获取从当前 到 特定时间 之间新发布的文章数量 API接口
 * @param WP_REST_Request $data
 *
 * @return int|WP_Error
 */
function api_get_new_post_count($data)
{
	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$days = Input_Validator::get_array_value($data, 'days', Input_Validator::TYPE_INT, false);
		$date = Input_Validator::get_array_value($data, 'date', Input_Validator::TYPE_STRING, false);
		if ($days)
		{
			$result = get_new_post_count($days);
		}
		else if ($date)
		{
			$result = get_new_post_count($date);
		}
		

		return $result ?? 0;
	});

	return $result;
}


/**
 * 注册自定义 api 接口
 * 
 * @return void
 */
function register_custom_post_list_api()
{

	register_rest_route('utils/v2', '/post_list', [
		'methods'  => 'GET, POST',
		'callback' => 'mikuclub\api_get_post_list',
	]);

	register_rest_route('utils/v2', '/favorite_post_list', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\api_get_my_favorite_post_list',
	]);

	register_rest_route('utils/v2', '/new_post_count', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\api_get_new_post_count',
	]);
}
