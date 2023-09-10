<?php
namespace mikuclub;

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
 * @return My_Private_Message[]|WP_Error
 */
function api_get_private_messages($data)
{

	//默认参数
	$paged  = 1;
	$number = 20;

	if (isset_numeric($data['paged']))
	{
		$paged = $data['paged'];
	}
	else if (isset($data['paged']) && !is_numeric($data['paged']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : paged 参数错误');
	}

	if (isset_numeric($data['number']))
	{
		$number = $data['number'];
	}
	else if (isset($data['number']) && !is_numeric($data['number']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : number 参数错误');
	}


	//如果未指定特定 发件人,
	if (!isset_numeric($data['sender_id']))
	{
		//进行普通分类查询 获取只包含所有发件人最后消息的私信列表
		$result = get_user_private_message_list_grouped($paged, $number);
	}
	else
	{
		//获取和特定发件人之间的私信列表
		$result = get_user_private_message_list_with_one_sender($data['sender_id'], $paged, $number);
	}

	return $result;
}


/**
 * 发送私信API
 *
 * @param WP_REST_Request $data
 * 'recipient_id' => 收件人id,
 * 'content' => 私信内容,
 * 'respond' =>是否在回复另外一条私信]
 *
 * @return My_Private_Message |WP_Error
 */
function api_send_private_message($data)
{


	if (!isset_numeric($data['recipient_id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : recipient_id 参数错误');
	}
	if (empty($data['content']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : content 参数错误');
	}

	$recipient_id    = $data['recipient_id'];
	$message_content = strip_tags(trim($data['content']), '<p><a><br>');

	//如果收件人不存在
	if (empty(get_userdata($recipient_id)))
	{
		return new WP_Error(400, __FUNCTION__ . ' : 收件人不存在');
	}

	//如果是在回复上一条私信  ID必须是纯数字
	if (isset($data['respond']))
	{
		$respond = $data['respond'];
	}
	else
	{
		$respond = 0;
	}

	//发送私信
	return send_private_message($recipient_id, $message_content, $respond);
}


/**
 * 删除私信API
 *
 * @param WP_REST_Request $data ['id' => 私信id]
 *
 * @return bool  | WP_Error 是否删除成功 或者 报错
 */
function api_delete_private_message($data)
{
	$user_id = get_current_user_id();
	$message_id = $data['id'] ?? null;
	$target_user_id = $data['target_user_id'] ?? null;

	//如果2个参数都缺
	if (is_null($message_id) && is_null($target_user_id))
	{
		return new WP_Error(400, __FUNCTION__ . ' : id 参数缺失/target_user_id 参数缺失');
	}
	//如果id参数错误
	else if ($message_id && !is_numeric($message_id))
	{
		return new WP_Error(400, __FUNCTION__ . ' : id 参数错误');
	}
	//如果target_user_id参数错误
	else if ($target_user_id && !is_numeric($target_user_id))
	{
		return new WP_Error(400, __FUNCTION__ . ' : target_user_id 参数错误');
	}


	return delete_private_message($user_id, $message_id, $target_user_id);
}



/**
 * API发送投诉信息
 *
 * @param WP_REST_Request $data
 * 'recipient_id' => 收件人id,
 * 'content' => 私信内容,
 * 'respond' =>是否在回复另外一条私信]
 *
 * @return My_Private_Message |WP_Error
 */
function api_send_report_message($data)
{


	if (!isset_numeric($data['post_id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : post_id 参数错误');
	}
	if (!isset($data['report_type']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : report_type 参数缺少');
	}

	$report_description = '';
	if (isset($data['report_description']))
	{
		$report_description = $data['report_description'];
		$report_description = strip_tags(trim($report_description), '<p><a><br>');
	}
	$report_contact = '';
	if (isset($data['report_contact']))
	{
		$report_contact = $data['report_contact'];
	}

	$post_title = get_the_title($data['post_id']);
	$post_link = get_permalink($data['post_id']);

	$message_content = <<<HTML

	<p>投诉类型: {$data['report_type']}</p>
	<p>详细说明: {$report_description}</p>
	<p>相关稿件: <a href="{$post_link}" target="_blank">$post_title</a></p>
	<p>联系方式: {$report_contact}</p>

HTML;

	$is_system = false;
	if (!is_user_logged_in())
	{
		$is_system = true;
	}

	//发送私信
	return send_private_message(1, $message_content, 0, $is_system);
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
			'callback'            => 'api_get_private_messages',
			'permission_callback' => 'is_user_logged_in',
		],
		[
			'methods'             => 'POST',
			'callback'            => 'api_send_private_message',
			'permission_callback' => 'current_user_is_regular',
		],
		[
			'methods'             => 'delete',
			'callback'            => 'api_delete_private_message',
			'permission_callback' => 'is_user_logged_in',
		],
	]);
	register_rest_route('utils/v2', '/message/(?P<id>\d+)', [
		[
			'methods'             => 'DELETE',
			'callback'            => 'api_delete_private_message',
			'permission_callback' => 'is_user_logged_in',
		],
	]);

	register_rest_route('utils/v2', '/message_count', [
		'methods'             => 'GET',
		'callback'            => 'api_get_user_private_message_unread_count',
		'permission_callback' => 'is_user_logged_in',
	]);

	register_rest_route('utils/v2', '/message_report', [
		[
			'methods'             => 'POST',
			'callback'            => 'api_send_report_message',
		],
	]);
}

/* 挂载函数到系统中*/
add_action('rest_api_init', 'mikuclub\register_custom_private_message_api');
