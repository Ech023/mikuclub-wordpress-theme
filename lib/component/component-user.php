<?php

namespace mikuclub;


/**
 * 输出用户头像html
 *
 * @param string $avatar_src 用户头像地址
 * @param int $size 图片显示大小
 *
 * @return string HTML代码
 */
function print_user_avatar($avatar_src, $size = 50)
{

	return <<<HTML
		<img class="avatar rounded-circle" src="{$avatar_src}" style="width: {$size}px; height: {$size}px" alt="用户头像" />
HTML;
}

/**
 * 输出关注和发私信给目标用户的按钮
 *
 * @param int $author_id
 * @return string
 */
function print_user_follow_and_message_button($author_id)
{
	//当前登陆用户ID
	$current_user_id = get_current_user_id();
	//检测是否已经关注了当前用户
	$is_user_followed = is_user_followed($author_id);

	$output = '';

	$author = get_custom_user($author_id);

	//必须是登陆用户, 并且不能是作者自己
	if ($current_user_id > 0 && $current_user_id !== $author_id)
	{
		//作者的关注数
		$user_fans_count = get_user_fans_count($author_id);

		//关注按钮样式
		$add_follow_button_style = $is_user_followed ? 'display: none;' : '';
		$delete_follow_button_style = $is_user_followed ? '' : 'display: none;';

		$follow_and_message_button = <<<HTML
	
			<div class="col-auto user-follow" data-user-fans-count="{$user_fans_count}">
				<button class="btn btn-sm btn-light-2 add-user-follow-list"  style="{$add_follow_button_style}" data-target-user-id="{$author_id}">
					<i class="fa-solid fa-plus"></i>
					<span>关注</span>
					<span class="user-fans-count">{$user_fans_count}</span>
				</button>
				<button class="btn btn-sm btn-dark-1 delete-user-follow-list"  style="{$delete_follow_button_style}" data-target-user-id="{$author_id}">
					<i class="fa-solid fa-minus"></i>
					<span>已关注</span>
					<span class="user-fans-count">{$user_fans_count}</span>
				</button>
			</div>
			<div class="col-auto">
				<button class="btn btn-sm btn-light-2 open_private_message_modal" data-recipient_id="{$author_id}" data-recipient_name="{$author->display_name}">
				<i class="fa-solid fa-envelope"></i> 发私信
				</button>
			</div>
	
HTML;


		$toggle_black_list_button = '';
		$toggle_black_list_button_class = 'add-user-black-list';
		$toggle_black_list_button_text = '加入黑名单';

		//如果该作者已被用户加入黑名单
		if (in_user_black_list($current_user_id, $author->id))
		{
			$toggle_black_list_button_class = 'delete-user-black-list';
			$toggle_black_list_button_text = '从黑名单里移除';
		}

		$toggle_black_list_button = <<<HTML
			<div class="col-auto">
				<div class="dropdown">
					<a class="btn btn-light-2 btn-sm " href="javascript:void(0);" role="button" data-bs-toggle="dropdown" title="更多操作">
						<i class="fa-solid fa-ellipsis-vertical"></i>
					</a>
					<ul class="dropdown-menu">
						<li>
							<a class="dropdown-item {$toggle_black_list_button_class}" href="javascript:void(0);" data-target-user-id="{$author_id}">{$toggle_black_list_button_text}</a>
						</li>
					</ul>
				</div>
			</div>
HTML;


		$output = <<<HTML
			
			{$follow_and_message_button}
			{$toggle_black_list_button}
			
HTML;
	}

	return $output;
}

/**
 * 输出用户等级+勋章
 *
 * @param int $user_id
 * @param string $badge_class 自定义徽章类名
 *
 * @return string HTML代码
 */
function print_user_badges($user_id, $badge_class = '')
{

	$user_badges = get_user_badges($user_id);

	//获取积分等级信息
	$user_level = get_user_level($user_id);
	//如果存在
	if ($user_level)
	{
		//插入到勋章数组头部
		array_unshift($user_badges, [
			'class' => 'badge text-bg-miku',
			'title' => $user_level,
			'level' => 0,
		]);
	}

	$output = '';
	//遍历每个胸章
	foreach ($user_badges as $user_badge)
	{
		$output .= '<span class="me-2 p-1 p-sm-2 my-1 rounded-1 ' . $user_badge['class'] . ' ' . $badge_class . '">' . $user_badge['title'] . '</span>';
	}

	return $output;
}


/**
 * 输出作者统计数据
 *
 * @param int $author_id
 * @param string $col_class 每个行元素的自定义类名
 * @return string HTML代码
 */
function print_author_statistics($author_id, $col_class = '')
{


	$arra_count = [];
	$arra_count[] = [
		'title' => '粉丝数',
		'icon' => 'fa-solid fa-user-plus',
		'value' => get_user_fans_count($author_id),
	];
	$arra_count[] = [
		'title' => '被拉黑数',
		'icon' => 'fa-solid fa-user-slash',
		'value' => get_user_blacked_count($author_id),
	];
	$arra_count[] = [
		'title' => '投稿数',
		'icon' => 'fa-solid fa-file-arrow-up',
		'value' => get_user_post_count($author_id),
	];
	$arra_count[] = [
		'title' => '获好评数',
		'icon' => 'fa-solid fa-thumbs-up',
		'value' => get_user_post_total_likes($author_id),
	];
	$arra_count[] = [
		'title' => '获评论数',
		'icon' => 'fa-solid fa-comments',
		'value' => get_user_post_total_comments($author_id),
	];
	$arra_count[] = [
		'title' => '获点击数',
		'icon' => 'fa-solid fa-eye',
		'value' => get_user_post_total_views($author_id),
	];

	$output = '';
	//遍历每个数据
	foreach ($arra_count as $element)
	{

		$output .= <<<HTML

		<div class="col {$col_class}">
			<div><i class="me-2 {$element['icon']}"></i>{$element['title']}</div>
			<div>{$element['value']}</div>
		</div>

HTML;
	}


	return $output;
}
