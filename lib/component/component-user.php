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
            'class' => 'badge bg-miku',
            'title' => $user_level,
            'level' => 0,
        ]);
    }

    $output = '';
    //遍历每个胸章
    foreach ($user_badges as $user_badge)
    {
        $output .= '<span class="me-2 p-2 my-1 rounded-1 ' . $user_badge['class'] . ' ' . $badge_class . '">' . $user_badge['title'] . '</span>';
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
		'title' => '获点赞数',
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

