<?php

/**
 * 初始化用户数据
 */
function init_user_data()
{

	//如果有登陆
	if (is_user_logged_in())
	{

		//黑名单用户检查
		check_blocked_user();

		//设置用户的消息变量
		init_user_unread_message_total_count();
	}
}

/**
 * 检查是否是黑名单用户, 如果是, 直接跳转到其他网站
 */
function check_blocked_user()
{

	//如果还未初始化 检查记录
	if (!isset($_SESSION[BLOCKED_USER_CHECK]))
	{
		$_SESSION[BLOCKED_USER_CHECK] = current_user_can('read');
	}
	//如果是黑名单用户
	if ($_SESSION[BLOCKED_USER_CHECK] === false)
	{
		//跳转
		$redirect_site =  'https://www.mikuclub.com';
		wp_redirect($redirect_site);
		exit;
	}
}

/**
 * 获取用户的头像图片ID
 *
 * @param int $user_id
 *
 * @return int | string
 */
function get_my_user_avatar_id($user_id)
{
	return get_user_meta($user_id, MY_USER_AVATAR, true);
}

/**
 * 设置用户的头像图片ID
 *
 * @param int $user_id
 * @param int $attachment_id
 */
function set_my_user_avatar_id($user_id, $attachment_id)
{

	if ($user_id)
	{
		update_user_meta($user_id, MY_USER_AVATAR, $attachment_id);
	}
}

/**
 * 获取用户头像图地址
 *
 * @param int $user_id
 *
 * @return string 图像http地址
 */
function get_my_user_avatar($user_id)
{

	$user_avatar = '';
	if ($user_id)
	{
		//获取内存缓存
		$cache_key   = MY_USER_AVATAR . '_' . $user_id;
		$user_avatar = get_cache_meta($cache_key, CACHE_GROUP_USER, EXPIRED_7_DAYS);

		//如果缓存不存在
		if (empty($user_avatar))
		{

			//获取本地头像

			$avatar_id = get_user_meta($user_id, MY_USER_AVATAR, true);
			if ($avatar_id)
			{

				$array_image =  wp_get_attachment_image_src($avatar_id, 'full');
				if ($array_image)
				{
					$user_avatar = $array_image[0];
				}
			}

			//如果未设置本地头像, 尝试获取社会化账号头像
			if (empty($user_avatar))
			{
				$social_avatar = get_user_meta($user_id, "open_img", true);

				//如果不是QQ头像地址 才使用 (QQ头像 好像已失效)
				//if(stripos($user_avatar, 'thirdqq.qlogo.cn') === false){
				$user_avatar = $social_avatar;
				//}

				//去除地址前的http 和 https 前缀
				$user_avatar = ltrim($user_avatar, "https:");
				//$user_avatar = substr($user_avatar, stripos($user_avatar, '//'));
			}

			//如果都没有则设置主题默认头像
			if (empty($user_avatar))
			{
				$user_avatar = get_my_user_default_avatar();
			}

			set_cache_meta($cache_key,  CACHE_GROUP_USER, $user_avatar);
		}

		$user_avatar = fix_image_domain_with_file_5($user_avatar);
	}

	return $user_avatar;
}

/**
 * 获取默认头像地址
 * @return string
 */
function get_my_user_default_avatar()
{
	return get_template_directory_uri() . "/img/default_avatar.jpg";
}


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

	return '<img class="avatar rounded-circle" src="' . $avatar_src . '" width="' . $size . '" height="' . $size . '" alt="用户头像" />';
}

/**
 * 更新用户头像动作
 *
 * @param int $user_id
 * @param int $attachment_id
 */
function action_on_update_avatar($user_id, $attachment_id)
{

	//添加附件类型键值对数据 说明是 用户头像
	update_post_meta($attachment_id, ATTACHMENT_WP_USER_AVATAR, $user_id);

	//删除旧头像文件
	$old_avatar_id = get_my_user_avatar_id($user_id);
	if ($old_avatar_id)
	{
		wp_delete_attachment($old_avatar_id, true);
	}

	//设置新头像
	set_my_user_avatar_id($user_id, $attachment_id);

	//清空旧头像文件缓存
	$cache_key = MY_USER_AVATAR . '_' . $user_id;
	delete_cache_meta($cache_key, CACHE_GROUP_USER);
}


/**
 * 获取用户当前等级
 *
 * @param int $user_id
 *
 * @return string 用户当前等级 || 如果无等级 则返回空字符串
 */
function get_user_level($user_id)
{

	$level = '';
	if ($user_id && function_exists('mycred_get_users_rank'))
	{

		$user_rank = mycred_get_users_rank($user_id);
		if ($user_rank)
		{
			$level = $user_rank->title;
		}
	}

	return $level;
}

/**
 * 获取用户当前积分
 *
 * @param int $user_id
 *
 * @return int 积分数量
 */
function get_user_points($user_id)
{

	$points = 0;
	if ($user_id && function_exists('mycred_get_users_balance'))
	{

		$points = mycred_get_users_balance($user_id);
		if ($points)
		{
			$points = number_format($points);
		}
	}

	return $points;
}


/**
 * 获取用户勋章
 *
 * @param int $user_id
 *
 * @return array[][] 勋章数组
 */
function get_user_badges($user_id)
{
	//获取统计数据
	$user_post_count    = get_user_post_count($user_id);
	$user_comment_count = get_user_comment_count($user_id);
	$user_like_count    = get_user_like_count($user_id);


	//用户初始3个勋章位
	$user_badges = [
		[],
		[],
		[]
	];


	//可选的勋章
	$available_badges = [
		[
			'score'            => 500,
			'level'            => 'Lv6',
			USER_POST_COUNT    => '白金UP',
			USER_COMMENT_COUNT => '真龙王',
			USER_LIKE_COUNT    => '点赞大师'
		],
		[
			'score'            => 200,
			'level'            => 'Lv5',
			USER_POST_COUNT    => '资深UP主',
			USER_COMMENT_COUNT => '资深龙王',
			USER_LIKE_COUNT    => '资深点赞师'
		],
		[
			'score'            => 100,
			'level'            => 'Lv4',
			USER_POST_COUNT    => '高级UP主',
			USER_COMMENT_COUNT => '高级龙王',
			USER_LIKE_COUNT    => '高级点赞师'
		],
		[
			'score'            => 50,
			'level'            => 'Lv3',
			USER_POST_COUNT    => '中级UP主',
			USER_COMMENT_COUNT => '中级龙王',
			USER_LIKE_COUNT    => '中级点赞师'
		],
		[
			'score'            => 25,
			'level'            => 'Lv2',
			USER_POST_COUNT    => '初级UP主',
			USER_COMMENT_COUNT => '初级龙王',
			USER_LIKE_COUNT    => '初级点赞师'
		],
		[
			'score'            => 5,
			'level'            => 'Lv1',
			USER_POST_COUNT    => '见习UP主',
			USER_COMMENT_COUNT => '见习龙王',
			USER_LIKE_COUNT    => '见习点赞师'
		]
	];

	//遍历可选勋章
	foreach ($available_badges as $badge)
	{

		//如果还未设置过相应勋章系列 + 分数足够
		if (!$user_badges[0] && $user_post_count >= $badge['score'])
		{
			$user_badges[0][] = 'badge bg-danger ';
			$user_badges[0][] = $badge[USER_POST_COUNT];
			$user_badges[0][] = $badge['level'];
		}
		if (!$user_badges[1] && $user_comment_count >= $badge['score'])
		{
			$user_badges[1][] = 'badge bg-primary';
			$user_badges[1][] = $badge[USER_COMMENT_COUNT];
			$user_badges[1][] = $badge['level'];
		}
		if (!$user_badges[2] && $user_like_count >= $badge['score'])
		{
			$user_badges[2][] = 'badge bg-success';
			$user_badges[2][] = $badge[USER_LIKE_COUNT];
			$user_badges[2][] = $badge['level'];
		}
	}

	return $user_badges;
}

/**
 * 输出用户勋章
 *
 * @param int $user_id
 *
 * @return string HTML代码
 */
function print_user_badges($user_id)
{

	$user_badges = get_user_badges($user_id);

	$output = '';
	//如果有获取到胸章, 才会输出
	foreach ($user_badges as $user_badge)
	{
		//如果有包含勋章信息
		if ($user_badge)
		{
			$output .= '<span class="' . $user_badge[0] . ' me-2 p-2 my-1">' . $user_badge[1] . '</span>';
		}
	}

	return $output;
}


/**
 * 获取用户发布的文章总数量
 * 先使用缓存, 无缓存的话再重新获取
 *
 * @param int $user_id
 *
 * @return string 文章数量
 */
function get_user_post_count($user_id)
{

	$count = 0;

	if ($user_id)
	{

		$cache_key = USER_POST_COUNT . '_' . $user_id;

		$count = get_cache_meta($cache_key, CACHE_GROUP_USER, EXPIRED_1_DAY);

		if (!$count)
		{
			$count = count_user_posts($user_id, 'post', true);
			//如果结果NULL, 则重设为0
			if (!$count)
			{
				$count = 0;
			}
			set_cache_meta($cache_key, CACHE_GROUP_USER, $count);
		}
	}


	return $count;
}


/**
 * 获取用户发布的文章的查看总次数
 * 先使用缓存, 无缓存的话再重新获取
 *
 * @param int user_id
 *
 * @return string 查看总次数
 */
function get_user_post_total_views($user_id)
{

	$count = 0;
	if ($user_id)
	{

		$cache_key = USER_POST_TOTAL_VIEWS . '_' . $user_id;

		$count = get_cache_meta($cache_key, CACHE_GROUP_USER, EXPIRED_3_DAYS);

		if ($count === '')
		{
			//重新计算
			global $wpdb;
			$count = $wpdb->get_var(" SELECT SUM(M.meta_value) FROM {$wpdb->posts} P, {$wpdb->postmeta} M WHERE P.post_author = {$user_id} AND P.post_type = 'post' AND P.post_status = 'publish' AND P.ID = M.post_id AND M.meta_key='views' ");
			//如果结果NULL, 则重设为0
			if (!$count)
			{
				$count = '0';
			}
			set_cache_meta($cache_key, CACHE_GROUP_USER, $count);
		}
	}

	return $count;
}


/**
 * 获取用户发布的文章收到的总评论数
 * 先使用缓存, 无缓存的话再重新获取
 *
 * @param int user_id
 *
 * @return string 总评论数
 */
function get_user_post_total_comments($user_id)
{

	$count = 0;
	if ($user_id)
	{

		$cache_key = USER_POST_TOTAL_COMMENTS . '_' . $user_id;

		$count = get_cache_meta($cache_key, CACHE_GROUP_USER, EXPIRED_3_DAYS);

		if ($count === '')
		{
			//重新计算
			global $wpdb;
			$count = $wpdb->get_var(" SELECT SUM(P.comment_count) FROM {$wpdb->posts} P WHERE P.post_author = {$user_id} AND P.post_type =  'post'  AND P.post_status =  'publish' ");
			//如果结果NULL, 则重设为0
			if (!$count)
			{
				$count = 0;
			}
			set_cache_meta($cache_key, CACHE_GROUP_USER, $count);
		}
	}

	return $count;
}


/**
 * 获取用户发布的文章收到的总点赞数
 * 先使用缓存, 无缓存的话再重新获取
 *
 * @param int user_id
 *
 * @return string 总点赞数
 */
function get_user_post_total_likes($user_id)
{

	$count = 0;
	if ($user_id)
	{

		$cache_key = USER_POST_TOTAL_LIKES . '_' . $user_id;
		$count     = get_cache_meta($cache_key, CACHE_GROUP_USER, EXPIRED_3_DAYS);

		if ($count === '')
		{
			//重新计算
			global $wpdb;
			$count = $wpdb->get_var(" SELECT SUM(M.meta_value) FROM {$wpdb->posts} P, {$wpdb->postmeta} M WHERE P.post_author = {$user_id} AND P.post_type = 'post' AND P.post_status = 'publish' AND P.ID = M.post_id AND M.meta_key='count_like' ");
			//如果结果NULL, 则重设为0
			if (!$count)
			{
				$count = 0;
			}
			set_cache_meta($cache_key, CACHE_GROUP_USER, $count);
		}
	}

	return $count;
}


/**
 * 获取用户发布的评论总数
 * 先使用缓存, 无缓存的话再重新获取
 *
 * @param int $user_id
 *
 * @return string 评论总数
 */
function get_user_comment_count($user_id)
{

	$count = 0;

	if ($user_id)
	{

		$cache_key = USER_COMMENT_COUNT . '_' . $user_id;

		$count = get_cache_meta($cache_key, CACHE_GROUP_USER, EXPIRED_1_DAY);

		if ($count === '')
		{

			$count = get_user_meta($user_id, USER_COMMENT_COUNT, true);

			if ($count === '')
			{
				//重新计算
				global $wpdb;
				$count = $wpdb->get_var(" SELECT COUNT(*) FROM {$wpdb->comments} WHERE user_id = {$user_id} AND comment_approved = 1 ");
				//如果结果NULL, 则重设为0
				if (!$count)
				{
					$count = 0;
				}
				//更新到用户列表里
				update_user_meta($user_id, USER_COMMENT_COUNT, $count);
			}
			//保存到缓存里
			set_cache_meta($cache_key, CACHE_GROUP_USER, $count);
		}
	}

	return $count;
}

/**
 * 增加用户评论总数
 *
 * @param int $user_id
 */
function add_user_comment_count($user_id)
{

	$count = get_user_comment_count($user_id);
	$count++;
	update_user_meta($user_id, USER_COMMENT_COUNT, $count);
}


/**
 * 获取用户未读的评论数量
 *
 * @return int
 */
function get_user_unread_comment_reply_count()
{

	$user_id = get_current_user_id();
	$count   = 0;

	//必须有用户id
	if ($user_id)
	{

		$args = [

			'status'     => 'approve',
			'count'      => true,
			'meta_query' => [
				'meta_query' => [
					'relation' => 'AND',
					[
						'key'     => COMMENT_PARENT_USER_ID,
						'value'   => $user_id,
						'compare' => '=',
						'type'    => 'NUMERIC',
					],
					[
						'key'     => COMMENT_PARENT_USER_READ,
						'value'   => 0,
						'compare' => '=',
						'type'    => 'NUMERIC',
					],
				],
			],
		];

		$count = get_comments($args);
	}

	return $count;
}


/**
 * 获取用户的点赞总次数
 *
 * @param int $user_id
 *
 * @return int 点赞总次数
 */
function get_user_like_count($user_id)
{

	$count = 0;

	if ($user_id)
	{

		$count = get_user_meta($user_id, USER_LIKE_COUNT, true);
		//如果用户从未评过分
		if ($count === "")
		{
			//设置默认为0
			$count = 0;
		}
	}

	return $count;
}

/**
 * 增加用户点赞总次数
 *
 * @param int $user_id
 */
function add_user_like_count($user_id)
{

	//只有登陆用户, 才会更新计数
	if ($user_id)
	{

		$count = get_user_like_count($user_id);
		$count++;
		update_user_meta($user_id, USER_LIKE_COUNT, (int) $count);
	}
}

/**
 * 减少用户点赞总次数
 *
 * @param int $user_id
 */
function delete_user_like_count($user_id)
{

	//只有登陆用户, 才会更新计数
	if ($user_id)
	{

		$count = get_user_like_count($user_id);
		//如果点赞数 大于 0
		if ($count > 0)
		{
			$count--;
		}
		else
		{
			$count = 0;
		}
		update_user_meta($user_id, USER_LIKE_COUNT, (int) $count);
	}
}


/**
 * 检测当前用户是否是管理员
 * @return bool
 */
function current_user_is_admin()
{
	return current_user_can('manage_options');
}




/**
 * 检测当前用户是否是高级作者用户
 * @return bool
 */
function current_user_can_publish_posts()
{
	return current_user_can('publish_posts');
}

/**
 * 检测当前用户是否是正常用户
 * @return bool
 */
function current_user_is_regular()
{
	return current_user_can('edit_posts');
}

/**
 * 根据id获取用户信息
 *
 * @param int $user_id
 *
 * @return My_System_User|My_User
 */
function get_custom_author($user_id)
{

	$author = '';
	//如果是0, 创建系统用户实例
	if ($user_id == 0)
	{
		$author = new My_System_User();
	}
	else
	{
		$user = get_userdata($user_id);
		if ($user)
		{
			//创建自定义用户类
			$author = new My_User($user);
		}
		//如果用户不存在, 创建已删除用户模板
		else
		{
			$author = new My_Custom_Deleted_User();
		}
	}


	//旧版安卓app需要的数据
	//全部升级到安卓1.2后可以删除=====================
	$author->author_id  = $author->id;
	$author->name       = $author->display_name;
	$author->avatar_src = $author->user_image;

	//=====================
	return $author;
}

/**
 * 获取评论作者信息
 *
 * @param $user_id
 *
 * @return bool| My_Custom_Comment_User
 */
function get_custom_comment_user($user_id)
{

	$user = get_userdata($user_id);
	if ($user)
	{

		$user = new My_Custom_Comment_User($user);
	}
	//如果用户不存在
	else
	{
		//创建一个默认空用户对象
		$user = new My_Custom_Deleted_User();
	}

	return $user;
}


/**
 * 输出用户系统和游览器信息
 *
 * @param $ua
 *
 * @return string
 */
function get_useragent($ua)
{

	$browser = get_browsers($ua);
	$os      = get_os($ua);

	return '<span class="useragent"><i class="fab ' . $browser[1] . '"></i> ' . $browser[0] . ' <i class="fab ' . $os[1] . '"></i> ' . $os[0] . '</span>';
}

/**
 * 获取用户游览器信息
 *
 * @param string $ua
 *
 * @return string[]
 */
function get_browsers($ua)
{

	$title = '未知';
	$icon  = 'fa-internet-explorer';

	//谷歌浏览器
	if (preg_match('#Chrome/([a-zA-Z0-9.]+)#i', $ua, $matches))
	{
		//$title = 'Google Chrome ' . $matches[1];
		$title = 'Chrome';
		$icon  = 'fa-chrome';
		//opera浏览器
		if (preg_match('#OPR/([a-zA-Z0-9.]+)#i', $ua, $matches))
		{
			$title = 'Opera';
			$icon  = 'fa-opera';
		}
	}
	else if (preg_match('#UCWEB([a-zA-Z0-9.]+)#i', $ua, $matches))
	{
		$title = 'UC';
	}
	else if (preg_match('#Safari/([a-zA-Z0-9.]+)#i', $ua, $matches))
	{
		$title = 'Safari';
		$icon  = 'fa-safari';
	}
	else if (preg_match('#QQBrowser/([a-zA-Z0-9.]+)#i', $ua, $matches))
	{
		$title = 'QQ';
		$icon  = 'fa-qq';
	} // 增加 安卓APP客户端的判断
	else if (strpos($ua, 'Dalvik') !== false)
	{
		$title = '安卓APP客户端 ';
		$icon  = 'fa-android';
	}
	else if (preg_match('#SE 2([a-zA-Z0-9.]+)#i', $ua, $matches))
	{
		$title = '搜狗';
	}
	else if (preg_match('#Firefox/([a-zA-Z0-9.]+)#i', $ua, $matches))
	{
		$title = 'Firefox';
		$icon  = 'fa-firefox';
	}
	else if (preg_match('#Edge/([a-zA-Z0-9.]+)#i', $ua, $matches))
	{
		$title = 'Edge';
		$icon  = 'fa-edge';
	}
	else if (preg_match('#360([a-zA-Z0-9.]+)#i', $ua, $matches))
	{
		$title = '360';
	}
	else if (preg_match('#MSIE ([a-zA-Z0-9.]+)#i', $ua, $matches))
	{
		$title = 'IE';
		$icon  = 'fa-internet-explorer';
	}
	else if (preg_match('/rv:(11.0)/i', $ua, $matches))
	{
		$title = 'IE';
		$icon  = 'fa-internet-explorer';
	}
	else if (preg_match('#Opera.(.*)Version[ /]([a-zA-Z0-9.]+)#i', $ua, $matches))
	{
		$title = 'Opera';
		$icon  = 'opera';
	}

	return [
		$title,
		$icon,
	];
}

/**
 * 获取系统信息
 *
 * @param string $ua
 *
 * @return array
 */
function get_os($ua)
{
	$title = '未知';
	$icon  = 'fa-windows';

	if (preg_match('/Linux/i', $ua))
	{
		$title = 'Linux';
		$icon  = 'fa-linux';
		if (preg_match('/Android.([0-9. _]+)/i', $ua, $matches))
		{
			$title = '安卓';
			$icon  = "fa-android";
		}
	}
	elseif (preg_match('/win/i', $ua) || preg_match('/WinNT/i', $ua) || preg_match('/Win32/i', $ua) || preg_match('/Windows/i', $ua))
	{

		$icon = "fa-windows";

		if (preg_match('/Windows NT 10.0; Win64; x64/i', $ua) || preg_match('/Windows NT 10.0; WOW64/i', $ua))
		{
			$title = "Win 10";
		}
		elseif (preg_match('/Windows NT 10.0/i', $ua))
		{
			$title = "Win 10";
		}
		elseif (preg_match('/Windows NT 6.4; Win64; x64/i', $ua) || preg_match('/Windows NT 6.4; WOW64/i', $ua))
		{
			$title = "Win 10";
		}
		elseif (preg_match('/Windows NT 6.4/i', $ua))
		{
			$title = "Win 10";
		}
		elseif (preg_match('/Windows NT 6.1; Win64; x64/i', $ua) || preg_match('/Windows NT 6.1; WOW64/i', $ua))
		{
			$title = "Win 7";
		}
		elseif (preg_match('/Windows NT 6.1/i', $ua))
		{
			$title = "Win 7";
		}
		elseif (preg_match('/Windows NT 5.1/i', $ua))
		{
			$title = "XP";
		}
		elseif (preg_match('/Windows NT 6.2; Win64; x64/i', $ua) || preg_match('/Windows NT 6.2; WOW64/i', $ua))
		{
			$title = "Win 8";
		}
		elseif (preg_match('/Windows NT 6.2/i', $ua))
		{
			$title = "Win 8";
		}
		elseif (preg_match('/Windows NT 6.3; Win64; x64/i', $ua) || preg_match('/Windows NT 6.3; WOW64/i', $ua))
		{
			$title = "Win 8.1";
		}
		elseif (preg_match('/Windows NT 6.3/i', $ua))
		{
			$title = "Win 8.1";
		}
		elseif (preg_match('/Windows NT 6.0/i', $ua))
		{
			$title = "Vista";
		}
		elseif (preg_match('/Windows NT 5.2/i', $ua))
		{

			$title = "XP";
		}
		elseif (preg_match('/Windows Phone/i', $ua))
		{
			$matches = explode(';', $ua);
			$title   = 'Win Phone';
		}
	}
	elseif (preg_match('#iPhone OS ([a-zA-Z0-9.( _)]+)#i', $ua, $matches))
	{ // 1.2 修改成 iphone os 来判断
		$title = 'Iphone';
		$icon  = "fa-apple";
	}
	elseif (preg_match('#iPod.*.CPU.([a-zA-Z0-9.( _)]+)#i', $ua, $matches))
	{
		$title = "iPod";
		$icon  = "fa-apple";
	}
	elseif (preg_match('#iPad.*.CPU.([a-zA-Z0-9.( _)]+)#i', $ua, $matches))
	{
		$title = "iPad";
		$icon  = "fa-apple";
	}
	elseif (preg_match('/Mac OS X.([0-9. _]+)/i', $ua, $matches))
	{
		$title = "Mac OSX";
		$icon  = "fa-apple";
	}
	elseif (preg_match('/Macintosh/i', $ua))
	{
		$title = "Mac OS";
		$icon  = "fa-apple";
	}
	elseif (preg_match('/CrOS/i', $ua))
	{
		$title = "Google Chrome OS";
		$icon  = "fa-chrome";
	}


	return [
		$title,
		$icon,
	];
}


/**
 * 自定义用户个人资料信息
 *
 * @param string[] $contact_methods
 *
 * @return string[]
 */
function profile_custom_contact_fields($contact_methods)
{

	//取消不必要的用户
	unset($contact_methods['yim']);
	unset($contact_methods['aim']);
	unset($contact_methods['jabber']);
	unset($contact_methods['twitter']);

	$contact_methods['qq']         = 'QQ';
	$contact_methods['sina_weibo'] = '新浪微博';

	return $contact_methods;
}

add_filter('user_contactmethods', 'profile_custom_contact_fields');


/**
 * 移除普通用户在后台的菜单选项
 */
function remove_menus()
{

	if (!current_user_is_admin())
	{

		remove_menu_page('index.php'); //仪表盘
		remove_menu_page('upload.php'); //多媒体
		remove_menu_page('edit.php'); //文章
		remove_menu_page('post-new.php'); //新建文章
		remove_menu_page('media-new.php'); //新建多媒体
		remove_menu_page('edit-comments.php'); //评论
		remove_menu_page('tools.php'); //工具
	}
}

add_action('admin_menu', 'remove_menus');

/**
 * 后台屏蔽普通用户导航栏LOGO, 评论, 新文章
 */
function annointed_admin_bar_remove()
{

	if (!current_user_is_admin())
	{
		global $wp_admin_bar;
		$wp_admin_bar->remove_menu('wp-logo');
		$wp_admin_bar->remove_menu('comments');
		$wp_admin_bar->remove_menu('new-content');
	}
}

//add_action( 'wp_before_admin_bar_render', 'annointed_admin_bar_remove', 0 );


/**
 * 禁止普通用户进入后台特定页面
 */
function bandUserRedirection()
{
	//检测用户是否有管理权限
	if (!current_user_is_admin())
	{
		$address = get_home_url();
		wp_redirect($address, 302);
	}
}

add_action('admin_print_scripts-index.php', 'bandUserRedirection', 10, 0);
add_action('admin_print_scripts-post-new.php', 'bandUserRedirection', 10, 0);
add_action('admin_print_scripts-edit.php', 'bandUserRedirection', 10, 0);
add_action('admin_print_scripts-edit-comments.php', 'bandUserRedirection', 10, 0);
add_action('admin_print_scripts-upload.php', 'bandUserRedirection', 10, 0);
add_action('admin_print_scripts-media-new.php', 'bandUserRedirection', 10, 0);
add_action('admin_print_scripts-profile.php', 'bandUserRedirection', 10, 0);


/*=============================================================*/


/**
 * 用户登录时触发的动作
 *
 * @param string $user_name
 * @param WP_User | int $user , 因为还有通过API给客户端调用, 所以可能会是 int
 */
function action_on_user_login($user_name, $user)
{
}

add_action('wp_login', 'action_on_user_login', 10, 2);

/**
 * 用户退出登陆时触发的动作
 */
function action_on_user_logout()
{
	//删除消息变量
	delete_user_unread_message_total_count_session();
}

add_action('wp_logout', 'action_on_user_logout');


/**
 * 获取用户收藏夹 (文章id列表)
 * @return int[]
 */
function get_user_favorite()
{

	$output  = [];
	$user_id = get_current_user_id();

	//确保用户有登陆
	if ($user_id)
	{
		$output = get_user_meta($user_id, MY_USER_FAVORITE_POST_LIST, true);
		//如果收藏夹未初始化
		if ($output === '')
		{
			$output = [];
		}
	}

	return $output;
}

/**
 * 添加文章id到收藏夹
 *
 * @param int $post_id
 *
 * @return int[] 新的收藏夹列表
 */
function add_user_favorite($post_id)
{

	$output = [];

	$user_id = get_current_user_id();

	//确保用户有登陆 并且有 文章id
	if ($user_id && $post_id)
	{

		$current_favorite = get_user_favorite();
		//只有在文章id不存在于数组中的时候 , 避免重复添加
		if (!in_array($post_id, $current_favorite))
		{
			//在头部添加新收藏的文章id
			array_unshift($current_favorite, $post_id);
			//更新数组
			update_user_meta($user_id, MY_USER_FAVORITE_POST_LIST, $current_favorite);

			//文章收藏数+1
			add_post_favorites($post_id);
		}

		$output = $current_favorite;
	}

	return $output;
}

/**
 * 从收藏夹移除文章id
 *
 * @param int $post_id
 *
 * @return int[] 新的收藏夹列表
 */
function delete_user_favorite($post_id)
{

	$output  = [];
	$user_id = get_current_user_id();


	//确保用户有登陆 并且有 文章id
	if ($user_id && $post_id)
	{

		$current_favorite = get_user_favorite();
		$index            = array_search($post_id, $current_favorite);
		//确保有找到对应元素后再操作
		if ($index !== false)
		{
			array_splice($current_favorite, $index, 1);
			//更新数组
			update_user_meta($user_id, MY_USER_FAVORITE_POST_LIST, $current_favorite);
			//文章收藏数-1
			delete_post_favorites($post_id);
		}

		$output = $current_favorite;
	}

	return $output;
}

/**
 * 初始化用户未读消息数
 */
function init_user_unread_message_total_count()
{

	//设置未读私信数量
	if (!isset($_SESSION[CUSTOM_PRIVATE_MESSAGE_COUNT]))
	{
		$_SESSION[CUSTOM_PRIVATE_MESSAGE_COUNT] = get_user_private_message_unread_count();
	}
	//设置未读评论数量
	if (!isset($_SESSION[CUSTOM_COMMENT_REPLY_COUNT]))
	{
		$_SESSION[CUSTOM_COMMENT_REPLY_COUNT] = get_user_unread_comment_reply_count();
	}
	//设置未读论坛帖子回复数量
	if (!isset($_SESSION[CUSTOM_FORUM_REPLY_COUNT]))
	{
		$_SESSION[CUSTOM_FORUM_REPLY_COUNT] = get_user_forum_notification_count();
	}
}

/**
 * 获取用户未读消息总数
 * @return int;
 */
function get_user_unread_message_total_count()
{

	$total_count = 0;

	if (isset($_SESSION[CUSTOM_PRIVATE_MESSAGE_COUNT]))
	{
		$total_count += $_SESSION[CUSTOM_PRIVATE_MESSAGE_COUNT];
	}
	if (isset($_SESSION[CUSTOM_COMMENT_REPLY_COUNT]))
	{
		$total_count += $_SESSION[CUSTOM_COMMENT_REPLY_COUNT];
	}
	if (isset($_SESSION[CUSTOM_FORUM_REPLY_COUNT]))
	{
		$total_count += $_SESSION[CUSTOM_FORUM_REPLY_COUNT];
	}

	return $total_count;
}

/**
 * 移除用户未读消息数的session变量
 */
function delete_user_unread_message_total_count_session()
{
	unset($_SESSION[CUSTOM_PRIVATE_MESSAGE_COUNT]);
	unset($_SESSION[CUSTOM_COMMENT_REPLY_COUNT]);
	unset($_SESSION[CUSTOM_FORUM_REPLY_COUNT]);
}


/**
 * 如果用户未登陆 重定向到其他页面 (默认回到首页)
 *
 * @param string $location
 */
function redirect_for_not_logged($location = '')
{

	if (!is_user_logged_in())
	{
		if (empty($location))
		{
			$location = get_site_url();
		}
		wp_redirect($location);
		exit;
	}
}

/**
 * 如果用户不是管理员 重定向到其他页面 (默认回到首页)
 *
 * @param string $location
 */
function redirect_for_not_admin($location = '')
{

	if (!current_user_is_admin())
	{
		if (empty($location))
		{
			$location = get_site_url();
		}
		wp_redirect($location);
		exit;
	}
}


/**
 * 获取用户的关注数组
 * @return  array
 */
function get_user_followed()
{

	$output  = [];
	$user_id = get_current_user_id();

	//确保用户有登陆
	if ($user_id)
	{
		$output = get_user_meta($user_id, MY_USER_FOLLOWED, true);
		//如果不存在 重设为空数组
		if ($output === '')
		{
			$output = [];
		}
	}

	return $output;
}

/**
 * 设置用户的关注
 *
 * @param int $user_id_to_follow
 * @param boolean $is_add 是否是添加关注操作, 否则为取消关注操作
 *
 * @return boolean 成功TRUE 失败 false
 */
function set_user_followed($user_id_to_follow, $is_add)
{

	$result = false;

	$user_id = get_current_user_id();
	//确保用户有登陆
	if ($user_id)
	{

		$user_followed = get_user_followed();
		//如果是添加关注, 确保不在数组里已有相关元素
		if ($is_add && !in_array($user_id_to_follow, $user_followed))
		{
			//添加
			$user_followed[] = $user_id_to_follow;
			//更新
			$result = update_user_meta($user_id, MY_USER_FOLLOWED, $user_followed);
		}
		//如果是取消关注, 确保在数组中已存在相关元素
		else if (!$is_add && in_array($user_id_to_follow, $user_followed))
		{
			//过滤掉相关元素
			$user_followed = array_filter($user_followed, function ($element) use ($user_id_to_follow)
			{
				return $element != $user_id_to_follow;
			});
			//更新
			$result = update_user_meta($user_id, MY_USER_FOLLOWED, $user_followed);
		}
	}

	return $result == true;
}

/**
 * 检测是否已被用户关注
 * @param int $user_id_to_follow
 *
 * @return bool
 */
function is_user_followed($user_id_to_follow)
{

	$result  = false;
	$user_id = get_current_user_id();
	//确保用户有登陆
	if ($user_id)
	{
		$user_followed = get_user_followed();
		$result = in_array($user_id_to_follow, $user_followed);
	}

	return $result;
}

/**
 * 获取用户的粉丝数量
 *
 * @param int $user_id
 *
 * @return  int
 */
function get_user_fans_count($user_id)
{

	$output = 0;

	//确保用户有登陆
	if ($user_id)
	{
		$output = get_user_meta($user_id, MY_USER_FANS_COUNT, true);
		//如果不存在 重设为0
		if ($output === '')
		{
			$output = 0;
		}
	}

	return $output;
}


/**
 * 设置用户的粉丝数
 *
 * @param int $user_id
 * @param boolean $is_add 是否是添增加粉丝数操作, 否则为删除粉丝数
 *
 * @return boolean 成功TRUE 失败 false
 */
function set_user_fans_count($user_id, $is_add)
{

	$result = false;

	//确保用户有登陆
	if ($user_id)
	{

		$fans_count = get_user_fans_count($user_id);
		//如果是添加粉丝数
		if ($is_add)
		{
			//粉丝数+1
			$fans_count++;
		}
		//如果是删除粉丝数
		else if (!$is_add)
		{
			//粉丝数-1
			$fans_count--;
		}

		//更新
		$result = update_user_meta($user_id, MY_USER_FANS_COUNT, $fans_count);
	}

	return $result == true;
}

/**
 * 通过挂钩 更改默认登陆cookie过期时间
 * @param int $expiration
 * @param int $user_id
 * @param bool $remember
 */
function set_default_auth_cookie_expiration($expiration, $user_id, $remember)
{
	//3个月过期一次
	return 7776000;
}
add_filter('auth_cookie_expiration', 'set_default_auth_cookie_expiration', 10, 3);
