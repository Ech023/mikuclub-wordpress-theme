<?php
/*
	template name: 我的私信
	description:  
*/

//如果未登陆 重定向回首页
namespace mikuclub;

use mikuclub\constant\Message_Type;
use mikuclub\Session_Cache;
use mikuclub\User_Capability;

use function mikuclub\print_breadcrumbs_component;
use function mikuclub\get_user_comment_reply_unread_count;
use function mikuclub\get_user_forum_notification_unread_count;
use function mikuclub\get_user_private_message_unread_count;


use function mikuclub\set_user_private_message_as_read;

User_Capability::prevent_not_logged_user();

get_header();

$user_id = get_current_user_id();

//尝试从url参数中获取当前消息类型
$active_message_type = $_GET['type'] ?? Message_Type::PRIVATE_MESSAGE;


$breadcrumbs = print_breadcrumbs_component();


$nav_items = [
	[
		'type_key' => 'type',
		'type'      => Message_Type::PRIVATE_MESSAGE,
		'name'      => '我的私信',
		'count'     => get_user_private_message_unread_count(),
		'count_key' => Session_Cache::USER_PRIVATE_MESSAGE_UNREAD_COUNT,
		'page_link' => get_page_link(),
		'active' => 'btn-light-2',
	],
	[
		'type_key' => 'type',
		'type'      => Message_Type::COMMENT_REPLY,
		'name'      => '评论回复',
		'count'     => get_user_comment_reply_unread_count(),
		'count_key' => Session_Cache::USER_COMMENT_REPLY_UNREAD_COUNT,
		'page_link' => get_page_link(),
		'active' => 'btn-light-2',
	],
	[
		'type_key' => 'show_notification',
		'type'      => 1,
		'name'      => '论坛回复',
		'count'     => get_user_forum_notification_unread_count(),
		'count_key' => Session_Cache::USER_FORUM_NOTIFICATION_UNREAD_COUNT,
		'page_link' => get_home_url() . '/forums',
		'active' => 'btn-light-2',
	]

];



$nav_items_html = '';
foreach ($nav_items as $nav_item)
{

	if ($active_message_type === $nav_item['type'])
	{
		$nav_item['active'] = 'btn-miku';
		//清零对应的消息计数
		Session_Cache::set($nav_item['count_key'], 0);
	}

	$href = add_query_arg($nav_item['type_key'], $nav_item['type'], $nav_item['page_link']);

	$nav_items_html .= <<<HTML
		<div class="col">
			<a class="btn btn-sm w-100 {$nav_item['active']}" href="{$href}">
				{$nav_item['name']}
				<span class="d-block d-sm-inline">{$nav_item['count']}</span>
			</a>
		</div>
HTML;
}


$message_nav_component = <<<HTML
	<div class="row row-cols-3 g-2 my-2">
		{$nav_items_html}
	</div>
HTML;


$output = <<<HTML

	<div class="page-message">

		<div class="page-header ">

			{$breadcrumbs}

			{$message_nav_component}
		</div>

		<div class="page-content my-2" >
			<div class="message-list accordion" id="accordion" data-message-type="{$active_message_type}"></div>
			<div class="message-list-end"></div>
		</div>

	</div>

HTML;

echo $output;

get_footer();
