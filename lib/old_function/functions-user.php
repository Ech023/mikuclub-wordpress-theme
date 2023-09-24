<?php

namespace mikuclub;

use mikuclub\constant\Comment_Meta;
use mikuclub\constant\Expired;
use mikuclub\constant\Post_Meta;
use mikuclub\constant\User_Meta;
use WP_Post;
use WP_REST_Request;
use WP_User;

/**
 * 初始化用户数据
 * @return void
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
 * @return void
 */
function check_blocked_user()
{

	//如果还未初始化 检查记录
	if (!isset($_SESSION[Session_Cache::IS_REGULAR_USER]))
	{
		$_SESSION[Session_Cache::IS_REGULAR_USER] = current_user_can('read');
	}
	//如果是黑名单用户
	if ($_SESSION[Session_Cache::IS_REGULAR_USER] === false)
	{
		//跳转
		$redirect_site =  'https://www.mikuclub.com';
		wp_redirect($redirect_site);
		exit;
	}
}

/**
 * 初始化 第一次访问的时候统计用户未读消息数
 * @return void
 */
function init_user_unread_message_total_count()
{

	//设置未读私信数量
	if (!isset($_SESSION[Session_Cache::PRIVATE_MESSAGE_COUNT]))
	{
		$_SESSION[Session_Cache::PRIVATE_MESSAGE_COUNT] = get_user_private_message_unread_count();
	}
	//设置未读评论数量
	if (!isset($_SESSION[Session_Cache::COMMENT_REPLY_COUNT]))
	{
		$_SESSION[Session_Cache::COMMENT_REPLY_COUNT] = get_user_unread_comment_reply_count();
	}
	//设置未读论坛帖子回复数量
	if (!isset($_SESSION[Session_Cache::FORUM_REPLY_COUNT]))
	{
		$_SESSION[Session_Cache::FORUM_REPLY_COUNT] = get_user_forum_notification_count();
	}
}



/**
 * 设置用户的头像图片ID
 *
 * @param int $user_id
 * @param int $attachment_id
 * @return void
 */
function set_my_user_avatar_id($user_id, $attachment_id)
{

	if ($user_id)
	{
		update_user_meta($user_id, User_Meta::USER_AVATAR, $attachment_id);
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
		$cache_key   = User_Meta::USER_AVATAR . '_' . $user_id;
		$user_avatar = File_Cache::get_cache_meta($cache_key, File_Cache::DIR_USER, Expired::EXP_7_DAYS);

		//如果缓存不存在
		if (empty($user_avatar))
		{

			//获取本地头像

			$avatar_id = get_user_meta($user_id, User_Meta::USER_AVATAR, true);
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

			File_Cache::set_cache_meta($cache_key,  File_Cache::DIR_USER, $user_avatar);
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
	return get_template_directory_uri() . "/img/default_avatar.webp";
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
 * @return void
 */
function action_on_update_avatar($user_id, $attachment_id)
{

	if ($user_id)
	{

		//添加附件类型键值对数据 说明是 用户头像
		update_post_meta($attachment_id, Post_Meta::ATTACHMENT_WP_USER_AVATAR, $user_id);

		//获取旧头像ID
		$old_avatar_id = get_user_meta($user_id, User_Meta::USER_AVATAR, true);
		//如果存在
		if ($old_avatar_id)
		{
			//删除
			wp_delete_attachment($old_avatar_id, true);
		}

		//保存新头像
		update_user_meta($user_id, User_Meta::USER_AVATAR, $attachment_id);

		//清空旧头像文件缓存
		$cache_key = User_Meta::USER_AVATAR . '_' . $user_id;
		File_Cache::delete_cache_meta($cache_key, File_Cache::DIR_USER);
	}
}

/**
 * 通过API上传图片附件的时候触发
 *
 * @param WP_Post $attachment Inserted or updated attachment object.
 * @param WP_REST_Request $request Request object.
 * @return void
 */
function action_on_rest_after_insert_attachment($attachment, $request)
{

	//如果是上传更换新头像 的 动作
	if ($request->has_param(User_Meta::ACTION_UPDATE_AVATAR_BY_API))
	{
		action_on_update_avatar(intval($attachment->post_author), $attachment->ID);
	}
}

add_action('rest_after_insert_attachment', 'mikuclub\action_on_rest_after_insert_attachment', 10, 2);


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
 * @return array<int, array<string, mixed>> 勋章数组
 */
function get_user_badges($user_id)
{
	//获取统计数据
	$user_post_count    = get_user_post_count($user_id);
	$user_comment_count = get_user_comment_count($user_id);
	$user_like_count    = get_user_like_count($user_id);

	$user = get_userdata($user_id);
	$timestamp = strtotime($user->user_registered) ?: strtotime('now');
	//计算用户注册年份
	$user_old = date("Y") - date("Y", $timestamp);


	//初始化用户勋章数组
	$user_badges = [];

	//如果存在UP主徽章
	$user_post_count_level = calculate_user_badges_level($user_post_count);
	if ($user_post_count_level)
	{

		$user_badges[] = [
			'class' => 'badge text-bg-danger',
			'title' => 'UP主 Lv' . $user_post_count_level,
			'level' => $user_post_count_level,
		];
	}

	//如果存在评价徽章
	$user_comment_count_level = calculate_user_badges_level($user_comment_count);
	if ($user_comment_count_level)
	{

		$user_badges[] = [
			'class' => 'badge text-bg-primary',
			'title' => '评价师 Lv' . $user_comment_count_level,
			'level' => $user_comment_count_level,
		];
	}

	//如果存在点赞徽章
	$user_like_count_level = calculate_user_badges_level($user_like_count);
	if ($user_like_count_level)
	{
		$user_badges[] = [
			'class' => 'badge text-bg-success',
			'title' => '点赞家 Lv' . $user_like_count_level,
			'level' => $user_like_count_level,
		];
	}

	//如果用户注册时间大于1年
	if ($user_old > 1)
	{
		//根据用户年龄不如, 使用不同的颜色
		$badge_color = 'text-bg-secondary';
		if ($user_old > 6)
		{
			$badge_color = 'text-bg-info';
		}
		else if ($user_old > 3)
		{
			$badge_color = 'text-bg-warning';
		}

		$user_badges[] = [
			'class' => 'badge ' . $badge_color,
			'title' => $user_old . '年用户',
			'level' => $user_old,
		];
	}



	return $user_badges;
}

/**
 * 根据数值计算用户出自定义徽章的等级
 *
 * @param int $value
 * @return int 如果无等级返回 0
 */
function calculate_user_badges_level($value)
{

	$level_6 = 1000;
	$level_5 = 300;
	$level_4 = 100;
	$level_3 = 30;
	$level_2 = 10;
	$level_1 = 3;

	$result = 0;


	if ($value >= $level_6)
	{
		$result = 6;
	}
	else if ($value >= $level_5)
	{
		$result = 5;
	}
	else if ($value >= $level_4)
	{
		$result = 4;
	}
	else if ($value >= $level_3)
	{
		$result = 3;
	}
	else if ($value >= $level_2)
	{
		$result = 2;
	}
	else if ($value >= $level_1)
	{
		$result = 1;
	}

	return $result;
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
 * 获取用户发布的文章总数量
 * 先使用缓存, 无缓存的话再重新获取
 *
 * @param int $user_id
 *
 * @return int 文章数量
 */
function get_user_post_count($user_id)
{

	$count = 0;

	if ($user_id)
	{

		$cache_key = File_Cache::USER_POST_COUNT . '_' . $user_id;

		$count = File_Cache::get_cache_meta($cache_key, File_Cache::DIR_USER, Expired::EXP_1_DAY);

		if (!$count)
		{
			$count = count_user_posts($user_id, 'post', true);
			//如果结果NULL, 则重设为0
			if (!$count)
			{
				$count = 0;
			}
			File_Cache::set_cache_meta($cache_key, File_Cache::DIR_USER, $count);
		}
	}


	return $count;
}


/**
 * 获取用户发布的文章的查看总次数
 * 先使用缓存, 无缓存的话再重新获取
 *
 * @param int $user_id
 *
 * @return int 查看总次数
 */
function get_user_post_total_views($user_id)
{

	global $wpdb;

	$count = 0;

	if ($user_id)
	{

		$cache_key = File_Cache::USER_POST_TOTAL_VIEW . '_' . $user_id;

		$count = File_Cache::get_cache_meta($cache_key, File_Cache::DIR_USER, Expired::EXP_3_DAYS);

		if (!$count)
		{
			//重新计算
			$count = $wpdb->get_var(" SELECT SUM(M.meta_value) FROM {$wpdb->posts} P, {$wpdb->postmeta} M WHERE P.post_author = {$user_id} AND P.post_type = 'post' AND P.post_status = 'publish' AND P.ID = M.post_id AND M.meta_key='views' ");
			//如果结果NULL, 则重设为0
			if (!$count)
			{
				$count = 0;
			}
			File_Cache::set_cache_meta($cache_key, File_Cache::DIR_USER, $count);
		}
	}

	return $count;
}


/**
 * 获取用户发布的文章收到的总评论数
 * 先使用缓存, 无缓存的话再重新获取
 *
 * @param int $user_id
 *
 * @return string 总评论数
 */
function get_user_post_total_comments($user_id)
{

	$count = 0;
	if ($user_id)
	{

		$cache_key = File_Cache::USER_POST_TOTAL_COMMENT . '_' . $user_id;

		$count = File_Cache::get_cache_meta($cache_key, File_Cache::DIR_USER, Expired::EXP_3_DAYS);

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
			File_Cache::set_cache_meta($cache_key, File_Cache::DIR_USER, $count);
		}
	}

	return $count;
}


/**
 * 获取用户发布的文章收到的总点赞数
 * 先使用缓存, 无缓存的话再重新获取
 *
 * @param int $user_id
 *
 * @return int 总点赞数
 */
function get_user_post_total_likes($user_id)
{

	$count = 0;
	if ($user_id)
	{

		$cache_key = File_Cache::USER_POST_TOTAL_LIKE . '_' . $user_id;
		$count     = File_Cache::get_cache_meta($cache_key, File_Cache::DIR_USER, Expired::EXP_3_DAYS);

		if (!$count)
		{
			//重新计算
			global $wpdb;
			$count = $wpdb->get_var(" SELECT SUM(M.meta_value) FROM {$wpdb->posts} P, {$wpdb->postmeta} M WHERE P.post_author = {$user_id} AND P.post_type = 'post' AND P.post_status = 'publish' AND P.ID = M.post_id AND M.meta_key='count_like' ");
			//如果结果NULL, 则重设为0
			if (!$count)
			{
				$count = 0;
			}
			File_Cache::set_cache_meta($cache_key, File_Cache::DIR_USER, $count);
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
 * @return int 评论总数
 */
function get_user_comment_count($user_id)
{

	$count = 0;

	if ($user_id)
	{

		$cache_key = User_Meta::USER_COMMENT_COUNT . '_' . $user_id;

		$count = File_Cache::get_cache_meta($cache_key, File_Cache::DIR_USER, Expired::EXP_1_DAY);

		if ($count === '')
		{

			$count = get_user_meta($user_id, User_Meta::USER_COMMENT_COUNT, true);

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
				update_user_meta($user_id, User_Meta::USER_COMMENT_COUNT, $count);
			}
			//保存到缓存里
			File_Cache::set_cache_meta($cache_key, File_Cache::DIR_USER, $count);
		}
	}

	return $count;
}

/**
 * 增加用户评论总数
 *
 * @param int $user_id
 * @return void
 */
function add_user_comment_count($user_id)
{

	$count = get_user_comment_count($user_id);
	$count++;
	update_user_meta($user_id, User_Meta::USER_COMMENT_COUNT, $count);
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
						'key'     => Comment_Meta::COMMENT_PARENT_USER_ID,
						'value'   => $user_id,
						'compare' => '=',
						'type'    => 'NUMERIC',
					],
					[
						'key'     => Comment_Meta::COMMENT_PARENT_USER_READ,
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

		$count = get_user_meta($user_id, User_Meta::USER_LIKE_COUNT, true);
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
 * @return void
 */
function add_user_like_count($user_id)
{

	//只有登陆用户, 才会更新计数
	if ($user_id)
	{

		$count = get_user_like_count($user_id);
		$count++;
		update_user_meta($user_id, User_Meta::USER_LIKE_COUNT, intval(($count)));
	}
}

/**
 * 减少用户点赞总次数
 *
 * @param int $user_id
 * @return void
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
		update_user_meta($user_id, User_Meta::USER_LIKE_COUNT, intval($count));
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

	$author = null;
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

	return $author;
}

/**
 * 获取评论作者信息
 *
 * @param int $user_id
 *
 * @return My_Custom_Comment_User|My_Custom_Deleted_User
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

add_filter('user_contactmethods', 'mikuclub\profile_custom_contact_fields');


/**
 * 移除普通用户在后台的菜单选项
 * 
 * @return void
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

add_action('admin_menu', 'mikuclub\remove_menus');

/**
 * 后台屏蔽普通用户导航栏LOGO, 评论, 新文章
 * 
 * @return void
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

//add_action( 'wp_before_admin_bar_render', 'mikuclub\annointed_admin_bar_remove', 0 );


/**
 * 禁止普通用户进入后台特定页面
 * 
 * @return void
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

add_action('admin_print_scripts-index.php', 'mikuclub\bandUserRedirection', 10, 0);
add_action('admin_print_scripts-post-new.php', 'mikuclub\bandUserRedirection', 10, 0);
add_action('admin_print_scripts-edit.php', 'mikuclub\bandUserRedirection', 10, 0);
add_action('admin_print_scripts-edit-comments.php', 'mikuclub\bandUserRedirection', 10, 0);
add_action('admin_print_scripts-upload.php', 'mikuclub\bandUserRedirection', 10, 0);
add_action('admin_print_scripts-media-new.php', 'mikuclub\bandUserRedirection', 10, 0);
add_action('admin_print_scripts-profile.php', 'mikuclub\bandUserRedirection', 10, 0);


/*=============================================================*/


/**
 * 用户登录时触发的动作
 *
 * @param string $user_name
 * @param WP_User | int $user , 因为还有通过API给客户端调用, 所以可能会是 int
 * 
 * @return void
 */
function action_on_user_login($user_name, $user)
{
}

add_action('wp_login', 'mikuclub\action_on_user_login', 10, 2);

/**
 * 用户退出登陆时触发的动作
 * 
 * @return void
 */
function action_on_user_logout()
{
	//删除消息变量
	delete_user_unread_message_total_count_session();
}

add_action('wp_logout', 'mikuclub\action_on_user_logout');


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
		$output = get_user_meta($user_id, User_Meta::USER_FAVORITE_POST_LIST, true);
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
			update_user_meta($user_id, User_Meta::USER_FAVORITE_POST_LIST, $current_favorite);

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
			update_user_meta($user_id, User_Meta::USER_FAVORITE_POST_LIST, $current_favorite);
			//文章收藏数-1
			delete_post_favorites($post_id);
		}

		$output = $current_favorite;
	}

	return $output;
}



/**
 * 获取用户未读消息总数
 * @return int;
 */
function get_user_unread_message_total_count()
{

	$total_count = 0;

	if (isset($_SESSION[Session_Cache::PRIVATE_MESSAGE_COUNT]))
	{
		$total_count += $_SESSION[Session_Cache::PRIVATE_MESSAGE_COUNT];
	}
	if (isset($_SESSION[Session_Cache::COMMENT_REPLY_COUNT]))
	{
		$total_count += $_SESSION[Session_Cache::COMMENT_REPLY_COUNT];
	}
	if (isset($_SESSION[Session_Cache::FORUM_REPLY_COUNT]))
	{
		$total_count += $_SESSION[Session_Cache::FORUM_REPLY_COUNT];
	}

	return $total_count;
}

/**
 * 移除用户未读消息数的session变量
 * 
 * @return void
 */
function delete_user_unread_message_total_count_session()
{
	unset($_SESSION[Session_Cache::PRIVATE_MESSAGE_COUNT]);
	unset($_SESSION[Session_Cache::COMMENT_REPLY_COUNT]);
	unset($_SESSION[Session_Cache::FORUM_REPLY_COUNT]);
}


/**
 * 如果用户未登陆 重定向到其他页面 (默认回到首页)
 *
 * @param string $location
 * @return void
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
 * @return void
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
 * @return int[]
 */
function get_user_followed()
{

	$output  = [];
	$user_id = get_current_user_id();

	//确保用户有登陆
	if ($user_id)
	{
		$output = get_user_meta($user_id, User_Meta::USER_FOLLOW_LIST, true);
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
			$result = update_user_meta($user_id, User_Meta::USER_FOLLOW_LIST, $user_followed);
		}
		//如果是取消关注, 确保在数组中已存在相关元素
		else if (!$is_add && in_array($user_id_to_follow, $user_followed))
		{
			//过滤掉相关元素
			$user_followed = array_filter($user_followed, function ($element) use ($user_id_to_follow)
			{
				$element = intval($element);
				return  $element !== 0 && $element !== $user_id_to_follow;
			});
			//更新
			$result = update_user_meta($user_id, User_Meta::USER_FOLLOW_LIST, $user_followed);
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
		$output = get_user_meta($user_id, User_Meta::USER_FANS_COUNT, true);
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
		else
		{
			//粉丝数-1
			$fans_count--;
		}

		//更新
		$result = update_user_meta($user_id, User_Meta::USER_FANS_COUNT, $fans_count);
	}

	return $result == true;
}


/**
 * 获取用户的黑名单
 *
 * @param int $user_id
 *
 * @return  int[] 拉黑用户ID数组
 */
function get_user_black_list($user_id)
{
	//初始化为空数组
	$output = [];

	//确保用户有登陆
	if ($user_id)
	{
		$output = get_user_meta($user_id, User_Meta::USER_BLACK_LIST, true);
		//如果不存在 重设为空数组
		if (empty($output))
		{
			$output = [];
		}
	}

	return $output;
}

/**
 * 检测目标用户ID是否在黑名单内
 *
 * @param int $user_id 当前登陆的用户ID
 * @param int $target_user_id 目标用户ID
 *
 * @return boolean 成功TRUE 失败 false
 */
function in_user_black_list($user_id, $target_user_id)
{

	$result = false;
	//确保用户和目标用户存在
	if ($user_id && $target_user_id)
	{
		//获取黑名单
		$black_list = get_user_black_list($user_id);
		//检测是否在黑名单内
		$result = in_array($target_user_id, $black_list);
	}

	return $result;
}

/**
 * 添加拉黑的用户ID到用户的黑名单
 *
 * @param int $user_id 当前登陆的用户ID
 * @param int $target_user_id 要拉黑的用户ID
 *
 * @return boolean 成功TRUE 失败 false
 */
function add_user_black_list($user_id, $target_user_id)
{

	$result = false;

	//确保用户和目标用户存在
	if ($user_id && $target_user_id && $user_id !== $target_user_id)
	{
		//获取黑名单
		$black_list = get_user_black_list($user_id);
		//如果黑名单里还不包含目标用户ID
		if (!in_array($target_user_id, $black_list))
		{
			//添加ID到黑名单里
			$black_list[] = $target_user_id;
			//更新黑名单
			$result = update_user_meta($user_id, User_Meta::USER_BLACK_LIST, $black_list);

			//增加目标用户的被拉黑数
			add_user_blacked_count($target_user_id);
		}
		//如果已经存在
		else
		{
			$result = true;
		}

		//并且从关注列表里移除
		set_user_followed($target_user_id, false);
	}

	return $result == true;
}

/**
 * 从黑名单里移除对应的用户ID
 *
 * @param int $user_id 当前登陆的用户ID
 * @param int $target_user_id 要取消拉黑的用户ID
 *
 * @return boolean 成功TRUE 失败 false
 */
function delete_user_black_list($user_id, $target_user_id)
{

	$result = false;

	//确保用户和目标用户存在
	if ($user_id && $target_user_id)
	{
		//获取黑名单
		$black_list = get_user_black_list($user_id);
		//从黑名单里移除目标ID
		$new_black_list = array_filter($black_list, function ($value) use ($target_user_id)
		{
			$value = intval($value);
			return $value !== 0 &&  $value !== $target_user_id;
		});

		//更新黑名单
		$result = update_user_meta($user_id, User_Meta::USER_BLACK_LIST, $new_black_list);

		//如果新旧黑名单有变化
		if (count($new_black_list) !== count($black_list))
		{
			//减少目标用户的被拉黑数
			delete_user_blacked_count($target_user_id);
		}
	}

	return $result == true;
}


/**
 * 获取用户被拉黑的次数
 *
 * @param int $user_id
 * @return int
 */
function get_user_blacked_count($user_id)
{
	$result = 0;

	//如果用户ID存在
	if ($user_id)
	{
		//如果不存在 重设为0
		$result = get_user_meta($user_id, User_Meta::USER_BLACKED_COUNT, true) ?: 0;
	}

	return $result;
}

/**
 * 增加用户被拉黑的次数
 * @param int $user_id 当前登陆的用户ID
 * @return void
 */
function add_user_blacked_count($user_id)
{
	//确保用户和目标用户存在
	if ($user_id)
	{
		$result = get_user_blacked_count($user_id);
		$result++;
		//更新
		update_user_meta($user_id, User_Meta::USER_BLACKED_COUNT, $result);
	}
}

/**
 * 减少用户被拉黑的次数
 * @param int $user_id 当前登陆的用户ID
 * @return void
 */
function delete_user_blacked_count($user_id)
{
	//确保用户和目标用户存在
	if ($user_id)
	{
		$result = get_user_blacked_count($user_id);
		//如果次数大于0, 减1, 否则重置为0
		$result = $result > 0 ? $result - 1 : 0;
		//更新
		update_user_meta($user_id, User_Meta::USER_BLACKED_COUNT, $result);
	}
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


/**
 * 通过挂钩 更改默认登陆cookie过期时间
 * @param int $expiration
 * @param int $user_id
 * @param bool $remember
 * @return int
 */
function set_default_auth_cookie_expiration($expiration, $user_id, $remember)
{
	//3个月过期一次
	return 7776000;
}
add_filter('auth_cookie_expiration', 'mikuclub\set_default_auth_cookie_expiration', 10, 3);
