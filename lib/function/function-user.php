<?php

namespace mikuclub;

use mikuclub\constant\Expired;
use mikuclub\constant\Post_Meta;
use mikuclub\constant\User_Capability;
use mikuclub\constant\User_Meta;

/**
 *  用户相关函数
 */



/**
 * 检查是否是黑名单用户, 如果是, 直接跳转到其他网站
 * @return void
 */
function check_blocked_user()
{
	//如果有登陆
	if (is_user_logged_in())
	{

		//获取缓存
		$is_blocked_user = Session_Cache::get(Session_Cache::IS_BLOCKED_USER);
		//如果缓存不存在
		if ($is_blocked_user === null)
		{
			//检测是否是黑名单用户
			$is_blocked_user = !User_Capability::is_regular_user();
			//设置新缓存
			Session_Cache::set(Session_Cache::IS_BLOCKED_USER, $is_blocked_user);
		}

		//如果是黑名单
		if ($is_blocked_user)
		{
			//自动跳转到第三方域名
			$redirect_site =  'https://www.mikuclub.net';
			wp_redirect($redirect_site);
			exit;
		}
	}
}


/**
 * 更新用户的自定义头像
 *
 * @param int $user_id
 * @param int $attachment_id
 * @return void
 */
function update_user_custom_avatar($user_id, $attachment_id)
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
		File_Cache::delete_cache_meta(User_Meta::USER_AVATAR, File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $user_id);
	}
}


/**
 * 获取用户头像图地址
 *
 * @param int $user_id
 * @return string 图像http地址
 */
function get_my_user_avatar($user_id)
{

	$user_avatar = '';

	if ($user_id)
	{
		//获取内存缓存

		$user_avatar = File_Cache::get_cache_meta_with_callback(
			User_Meta::USER_AVATAR,
			File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $user_id,
			Expired::EXP_1_MONTH,
			function () use ($user_id)
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

				return $user_avatar;
			}
		);

		//修正域名
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

	return <<<HTML
		<img class="avatar rounded-circle" src="{$avatar_src}" style="width: {$size}px; height: {$size}px" alt="用户头像" />
HTML;
}



/**
 * 获取用户当前等级
 *
 * @param int $user_id
 * @return string 如果无等级 则返回空字符串
 */
function get_user_level($user_id)
{

	$result = '';
	if ($user_id && function_exists('mycred_get_users_rank'))
	{
		$result = File_Cache::get_cache_meta_with_callback(
			File_Cache::USER_LEVEL,
			File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $user_id,
			Expired::EXP_6_HOURS,
			function () use ($user_id)
			{
				//重新获取等级信息
				$user_rank = mycred_get_users_rank($user_id);
				$result = $user_rank ? $user_rank->title : '';

				return $result;
			}
		);
	}

	return $result;
}


/**
 * 获取用户当前积分
 *
 * @param int $user_id
 * @return string 积分数量
 */
function get_user_points($user_id)
{

	$result = '';
	if ($user_id && function_exists('mycred_get_users_balance'))
	{
		$result = File_Cache::get_cache_meta_with_callback(
			File_Cache::USER_POINT,
			File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $user_id,
			Expired::EXP_6_HOURS,
			function () use ($user_id)
			{
				//重新获取积分
				$result = mycred_get_users_balance($user_id);
				$result = $result ? number_format($result) : '0';

				return $result;
			}
		);
	}

	return $result;
}



/**
 * 获取用户发布的文章总数量
 * 先使用缓存, 无缓存的话再重新获取
 *
 * @param int $user_id
 * @return int 文章数量
 */
function get_user_post_count($user_id)
{

	$result = 0;

	if ($user_id)
	{
		$result = File_Cache::get_cache_meta_with_callback(
			File_Cache::USER_POST_COUNT,
			File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $user_id,
			Expired::EXP_1_DAY,
			function () use ($user_id)
			{
				$result = intval(count_user_posts($user_id, 'post', true));
				return $result;
			}
		);
	}

	return intval($result);
}


/**
 * 获取用户发布的文章的查看总次数
 * 先使用缓存, 无缓存的话再重新获取
 *
 * @param int $user_id
 * @return int 查看总次数
 * 
 * @global $wpdb
 */
function get_user_post_total_views($user_id)
{
	global $wpdb;

	$result = 0;

	if ($user_id)
	{

		$result = File_Cache::get_cache_meta_with_callback(
			File_Cache::USER_POST_TOTAL_VIEW,
			File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $user_id,
			Expired::EXP_1_DAY,
			function () use ($wpdb, $user_id)
			{
				$query = <<<SQL
				SELECT 
					SUM(M.meta_value) 
				FROM 
					{$wpdb->posts} P, {$wpdb->postmeta} M 
				WHERE 
					P.post_author = {$user_id} 
				AND 
					P.post_type = 'post' 
				AND 
					P.post_status = 'publish' 
				AND 
					P.ID = M.post_id 
				AND 
					M.meta_key = 'views'
SQL;

				$result = $wpdb->get_var($query);
				$result = intval($result);

				return $result;
			}
		);
	}

	return intval($result);
}



/**
 * 获取用户发布的文章收到的总评论数
 * 先使用缓存, 无缓存的话再重新获取
 *
 * @param int $user_id
 * @return int 总评论数
 * 
 * @global $wpdb
 */
function get_user_post_total_comments($user_id)
{
	global $wpdb;

	$result = 0;
	if ($user_id)
	{

		$result = File_Cache::get_cache_meta_with_callback(
			File_Cache::USER_POST_TOTAL_COMMENT,
			File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $user_id,
			Expired::EXP_1_DAY,
			function () use ($wpdb, $user_id)
			{
				//重新计算
				$query = <<<SQL
					SELECT 
						SUM(P.comment_count) 
					FROM 
						{$wpdb->posts} P 
					WHERE 
						P.post_author = {$user_id} 
					AND 
						P.post_type = 'post'  
					AND 
						P.post_status = 'publish'
SQL;

				$result = $wpdb->get_var($query);
				$result = intval($result);

				return $result;
			}
		);
	}

	return intval($result);
}

/**
 * 获取用户发布的文章收到的总点赞数
 * 先使用缓存, 无缓存的话再重新获取
 *
 * @param int $user_id
 * @return int 总点赞数
 * 
 * @global $wpdb
 */
function get_user_post_total_likes($user_id)
{
	global $wpdb;

	$result = 0;
	if ($user_id)
	{

		$result = File_Cache::get_cache_meta_with_callback(
			File_Cache::USER_POST_TOTAL_LIKE,
			File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $user_id,
			Expired::EXP_1_DAY,
			function () use ($wpdb, $user_id)
			{
				//重新计算
				$query = <<<SQL
					SELECT 
						SUM(M.meta_value) 
					FROM 
						{$wpdb->posts} P, {$wpdb->postmeta} M 
					WHERE 
						P.post_author = {$user_id} 
					AND 
						P.post_type = 'post' 
					AND 
						P.post_status = 'publish' 
					AND 
						P.ID = M.post_id 
					AND 
						M.meta_key = 'count_like'
SQL;
				$result = $wpdb->get_var($query);
				return intval($result);
			}
		);
	}

	return intval($result);
}



/**
 * 获取用户发布的评论总数
 * 先使用缓存, 无缓存的话再重新获取
 *
 * @param int $user_id
 * @return int 评论总数
 * 
 * @global $wpdb
 */
function get_user_comment_count($user_id)
{
	global $wpdb;

	$result = 0;
	if ($user_id)
	{

		$result = File_Cache::get_cache_meta_with_callback(
			User_Meta::USER_COMMENT_COUNT,
			File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $user_id,
			Expired::EXP_6_HOURS,
			function () use ($wpdb, $user_id)
			{

				$result = get_user_meta($user_id, User_Meta::USER_COMMENT_COUNT, true);
				if ($result === '')
				{
					//重新计算
					$query = <<<SQL
					SELECT 
						COUNT(*) 
					FROM 
						{$wpdb->comments} 
					WHERE 
						user_id = {$user_id} 
					AND 
						comment_approved = 1
SQL;

					$result = $wpdb->get_var($query);

					//更新到用户列表里
					update_user_meta($user_id, User_Meta::USER_COMMENT_COUNT, $result);
				}

				$result = intval($result);
				return $result;
			}
		);
	}

	return intval($result);
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
 * 获取用户的点赞总次数
 *
 * @param int $user_id
 * @return int 点赞总次数
 */
function get_user_like_count($user_id)
{

	$result = 0;

	if ($user_id)
	{
		$result = get_user_meta($user_id, User_Meta::USER_LIKE_COUNT, true);
	}

	return intval($result);
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
		update_user_meta($user_id, User_Meta::USER_LIKE_COUNT, $count);
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
		$count--;

		//如果点赞数为负数
		if ($count < 0)
		{
			//重置为0
			$count = 0;
		}

		update_user_meta($user_id, User_Meta::USER_LIKE_COUNT, $count);
	}
}




// /**
//  * 设置用户的头像图片ID
//  *
//  * @param int $user_id
//  * @param int $attachment_id
//  * @return void
//  */
// function set_my_user_avatar_id($user_id, $attachment_id)
// {
// 	if ($user_id)
// 	{
// 		update_user_meta($user_id, User_Meta::USER_AVATAR, $attachment_id);
// 	}
// }
