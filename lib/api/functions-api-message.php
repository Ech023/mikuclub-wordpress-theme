<?php

namespace mikuclub;

use Exception;
use mikuclub\constant\Config;
use WP_Error;
use WP_REST_Request;

/**
 * 获取用户未读私信数量API
 *
 * @param WP_REST_Request $data
 *
 * @return int 私信数量
 */
function api_get_user_private_message_unread_count($data)
{

	return get_user_private_message_unread_count();
}


/**
 *  获取私信列表API
 *
 * @param WP_REST_Request $data 
 * [
 * 	'paged' => 页数, 
 * 	'number' => 每页数据数量, 
 * 	'sender_id' =>是否只要当前用户和sender之间互相写的私信
 * ]
 *
 * @return My_Private_Message_Model[]|WP_Error
 */
function api_get_private_messages($data)
{
	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$paged = Input_Validator::get_array_value($data, 'paged', Input_Validator::TYPE_INT, false) ?: 1;
		$number = Input_Validator::get_array_value($data, 'number', Input_Validator::TYPE_INT, false) ?: Config::NUMBER_PRIVATE_MESSAGE_LIST_PER_PAGE;
		$sender_id = Input_Validator::get_array_value($data, 'sender_id', Input_Validator::TYPE_INT, false);

		//如果有指定特定 发件人,
		if ($sender_id)
		{
			//获取和特定发件人之间的私信列表
			$result = get_user_private_message_list_with_one_sender($sender_id, $paged);
		}
		else
		{
			//进行普通分类查询 获取只包含所有发件人最后消息的私信列表
			$result = get_user_private_message_list_grouped($paged, $number);
		}

		return $result;
	});

	return $result;
}


/**
 * 发送私信API
 *
 * @param WP_REST_Request $data
 * [
 * 	'recipient_id' => 收件人id,
 * 	'content' => 私信内容,
 * 	'respond' =>是否在回复另外一条私信
 * ]
 *
 * @return My_Private_Message_Model|WP_Error
 */
function api_send_private_message($data)
{
	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$recipient_id = Input_Validator::get_array_value($data, 'recipient_id', Input_Validator::TYPE_INT, true);
		$respond = Input_Validator::get_array_value($data, 'respond', Input_Validator::TYPE_INT, false) ?? 0;

		$content = Input_Validator::get_array_value($data, 'content', Input_Validator::TYPE_STRING, true);
		//移除html标签
		$content = strip_tags($content, '<p><a><br>');

		//如果收件人不存在
		if (empty(get_userdata($recipient_id)))
		{
			throw new Exception('收件人不存在');
		}

		$result = send_private_message($recipient_id, $content, $respond);
		return $result;
	});

	return $result;
}


/**
 * 删除私信API
 *
 * @param WP_REST_Request $data
 * [
 * 	'id' => 私信id
 * ]
 *
 * @return bool|WP_Error 是否删除成功 或者 报错
 */
function api_delete_private_message($data)
{

	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$user_id = get_current_user_id();
		$message_id = Input_Validator::get_array_value($data, 'id', Input_Validator::TYPE_INT, false);
		$target_user_id = Input_Validator::get_array_value($data, 'target_user_id', Input_Validator::TYPE_INT, false);

		//如果2个参数都缺
		if (empty($message_id) && empty($target_user_id))
		{
			throw new Exception('id 参数缺失/target_user_id 参数缺失');
		}

		$result = delete_private_message($user_id, $message_id, $target_user_id);
		return $result;
	});

	return $result;
}



/**
 * API发送投诉信息
 *
 * @param WP_REST_Request $data
 * [
 * 	'recipient_id' => int 收件人id,
 * 	'content' => string 私信内容,
 * 	'respond' => bool 是否在回复另外一条私信
 * ]
 * @return My_Private_Message_Model|WP_Error
 */
function api_send_report_message($data)
{

	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$post_id = Input_Validator::get_array_value($data, 'post_id', Input_Validator::TYPE_INT, true);
		$report_type = Input_Validator::get_array_value($data, 'report_type', Input_Validator::TYPE_STRING, true);
		$report_description = Input_Validator::get_array_value($data, 'report_description', Input_Validator::TYPE_STRING, false) ?: '';
		//移除html标签
		$report_description = strip_tags($report_description, '<p><a><br>');

		$report_contact = Input_Validator::get_array_value($data, 'report_contact', Input_Validator::TYPE_STRING, false) ?: '';

		$post_title = get_the_title($post_id);
		$post_link = get_permalink($post_id);

		$message_content = <<<HTML
			<p>投诉类型: {$report_type}</p>
			<p>详细说明: {$report_description}</p>
			<p>相关稿件: <a href="{$post_link}" target="_blank">{$post_title}</a></p>
			<p>联系方式: {$report_contact}</p>
HTML;

		$is_system = is_user_logged_in() ? false : true;
		$admin_user_id = 1;
		$respond = 0;

		//发送私信
		$result = send_private_message($admin_user_id, $message_content, $respond, $is_system);
		return $result;
	});

	return $result;
}



/**
 * 注册自定义 api 接口
 * 
 * @return void
 */
function register_custom_private_message_api()
{


	register_rest_route('utils/v2', '/message', [
		[
			'methods'             => 'GET',
			'callback'            => 'mikuclub\api_get_private_messages',
			'permission_callback' => 'is_user_logged_in',
		],
		[
			'methods'             => 'POST',
			'callback'            => 'mikuclub\api_send_private_message',
			'permission_callback' => ['mikuclub\User_Capability', 'is_regular_user'],
		],
		[
			'methods'             => 'delete',
			'callback'            => 'mikuclub\api_delete_private_message',
			'permission_callback' => 'is_user_logged_in',
		],
	]);
	register_rest_route('utils/v2', '/message/(?P<id>\d+)', [
		[
			'methods'             => 'DELETE',
			'callback'            => 'mikuclub\api_delete_private_message',
			'permission_callback' => 'is_user_logged_in',
		],
	]);

	register_rest_route('utils/v2', '/message_count', [
		'methods'             => 'GET',
		'callback'            => 'mikuclub\api_get_user_private_message_unread_count',
		'permission_callback' => 'is_user_logged_in',
	]);

	register_rest_route('utils/v2', '/message_report', [
		[
			'methods'             => 'POST',
			'callback'            => 'mikuclub\api_send_report_message',
		],
	]);
}


