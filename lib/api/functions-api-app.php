<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use WP_REST_Request;
use WP_REST_Response;

/**
 * 安卓APP用到的接口
 */

/**
 * APK更新检测功能
 * @return array<string, mixed>
 */
function api_check_app_update()
{

	$output = [
		'versionCode' => 37,
		'versionName' => 'v3.0版本 [24年1月22号发布]',
		'forceUpdate' => false,
		// 'downUrl' => 'https://mikuclub.fun/app/mikuclub_v2.9.4.apk',
		'downUrl' => 'https://cdn2.mikuclub.fun/app/mikuclub_v3.0.apk',
		'description' => '' .
			'重大更新, 建议下载升级新版' .
			'- 增加差评按钮\n' .
			'- 评论列表优化, 支持删除自己的评论\n' .
			'- 显示二级解压密码' .
			'- 添加备用图床功能' .
			'- 支持谷歌账号登陆' .
			'- 修复若干BUG\n' .
			//'- 修复 微博登陆问题\n'.
			//'- 增加 投稿页面秒传链接的文本框\n'.
			//'- 支持直接唤起阿里云盘 (失败)\n'.
			//'- 增加开屏广告-每30分钟最多显示一次 (成功)\n'.
			'[如果无法打开下载页, 请到初音社网站下载: mikuclub.cn]',

	];

	return $output;
}

/**
 * 获取app端的公告和广告信息
 * @return array<string, mixed>
 */
function api_get_app_communication()
{

	return [
		'communication'       => get_theme_option(Admin_Meta::APP_ANNOUNCEMENT),
		Admin_Meta::APP_ADSENSE_TEXT => get_theme_option(Admin_Meta::APP_ADSENSE_TEXT),
		Admin_Meta::APP_ADSENSE_LINK => get_theme_option(Admin_Meta::APP_ADSENSE_LINK),
		'app_adindex_01_show' => get_theme_option(Admin_Meta::APP_ADSENSE_ENABLE),
	];
}



/**
 * 通过api 获取菜单
 * 获取安卓应用专用菜单
 * @return array<int, array<string, mixed>>
 */
function api_get_menu()
{

	$app_menu_id = 6375;
	$array_menu = wp_get_nav_menu_items($app_menu_id);
	$output     = [];

	//遍历每个菜单项, 需要去除自定义链接选项
	foreach ($array_menu as $menu_item)
	{

		//如果是个正常分类
		if ($menu_item->object == "category")
		{

			$category = [
				'object_id' => $menu_item->object_id,
				'title' => $menu_item->title,
				'post_parent' => $menu_item->post_parent,
				'children' => []
			];

			//如果这是子分类
			if ($menu_item->post_parent > 0)
			{
				//提取出id数组用来判断对应分类的index位置
				$array_id_category_of_output = array_column($output, 'object_id');
				$parent_category_index = array_search($menu_item->post_parent, $array_id_category_of_output);
				//如果存在对应的主分类
				if ($parent_category_index !== false)
				{
					$output[$parent_category_index]['children'][] = $category;
				}
			}
			//如果不是子分类
			else
			{
				//添加到最终输出数组里
				$output[] = $category;
			}
		}
	}

	return $output;
}




/**
 * APP专用获取收藏文章列表
 * 
 * @param WP_REST_Request $data
 * @return WP_REST_Response
 */
function api_get_my_favorite_post_list_for_app($data)
{

	$per_page = Input_Validator::get_array_value($data, 'per_page', Input_Validator::TYPE_INT, false) ?: 1;
	$page = Input_Validator::get_array_value($data, 'page', Input_Validator::TYPE_INT, false) ?: 1;
	$_envelope = Input_Validator::get_array_value($data, '_envelope', Input_Validator::TYPE_INT, false);


	//从内部调用wordpress API接口
	$query = [
		'per_page'  => $per_page,
		'page'      => $page,
		// '_envelope' => $_envelope,
		'orderby'   => 'include',
		'include'   => get_user_favorite(),
	];

	if ($_envelope)
	{
		$query['_envelope'] = $_envelope;
	}

	$request = new WP_REST_Request('GET', '/wp/v2/posts');
	$request->set_query_params($query);
	$response = rest_do_request($request);
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
function register_custom_app_api()
{

	register_rest_route('utils/v2', '/app_update', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\api_check_app_update',
	]);

	register_rest_route('utils/v2', '/get_app_communication', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\api_get_app_communication',
	]);


	register_rest_route('utils/v2', '/get_menu', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\api_get_menu',
	]);

	register_rest_route('utils/v2', '/app_favorite_post_list', [
		'methods'  => 'GET',
		'callback' => 'mikuclub\api_get_my_favorite_post_list_for_app',
	]);
}
