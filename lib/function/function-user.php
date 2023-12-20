<?php

namespace mikuclub;

use mikuclub\constant\Expired;
use mikuclub\constant\Post_Meta;
use mikuclub\constant\User_Meta;

/**
 *  用户相关函数
 */


/**
 * 根据id获取用户信息
 *
 * @param int $user_id
 *
 * @return My_User_Model
 */
function get_custom_user($user_id)
{

	//如果是0, 创建系统用户实例
	if ($user_id == 0)
	{
		$author = My_User_Model::create_system_user();
	}
	else
	{
		$author = File_Cache::get_cache_meta_with_callback(
			File_Cache::USER_DATA . '_' . $user_id,
			File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $user_id,
			Expired::EXP_1_DAY,
			function () use ($user_id)
			{
				$user = get_userdata($user_id);
				if ($user)
				{
					//创建自定义用户类
					$author = new My_User_Model($user);
				}
				//如果用户不存在, 创建已删除用户模板
				else
				{
					$author = My_User_Model::create_deleted_user();
				}
				return $author;
			}
		);
	}


	return $author;
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

		//清空该用户的缓存
		File_Cache::delete_user_cache_meta_by_user_id($user_id);
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
		$user_avatar = fix_image_domain_with_file_1($user_avatar);
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

/**
 * 获取用户收藏夹 (文章id列表)
 * @return int[]
 */
function get_user_favorite()
{

	$result  = [];
	$user_id = get_current_user_id();

	//确保用户有登陆
	if ($user_id)
	{
		$result = get_user_meta($user_id, User_Meta::USER_FAVORITE_POST_LIST, true) ?: [];
		$result = array_map(function ($element)
		{
			return intval($element);
		}, $result);
	}

	return array_values($result);
}

/**
 * 添加文章id到收藏夹
 *
 * @param int $post_id
 * @return boolean 成功TRUE 失败 false
 */
function add_user_favorite($post_id)
{

	$result = false;

	$user_id = get_current_user_id();

	//确保用户有登陆 并且有 文章id
	if ($user_id && $post_id)
	{
		$user_favorite = get_user_favorite();
		//只有在文章id不存在于数组中的时候 , 避免重复添加
		if (!in_array($post_id, $user_favorite))
		{
			//在头部添加新收藏的文章id
			array_unshift($user_favorite, $post_id);
			//更新数组
			$result = update_user_meta($user_id, User_Meta::USER_FAVORITE_POST_LIST, $user_favorite);

			//文章收藏数+1
			add_post_favorites($post_id);
		}
	}

	return boolval($result);
}

/**
 * 从收藏夹移除文章id
 *
 * @param int $post_id
 * @return boolean 成功TRUE 失败 false
 */
function delete_user_favorite($post_id)
{

	$result = false;

	$user_id = get_current_user_id();

	//确保用户有登陆 并且有 文章id
	if ($user_id && $post_id)
	{

		$user_favorite = get_user_favorite();
		//只有在文章id存在于数组中的时候 
		if (in_array($post_id, $user_favorite))
		{
			//从收藏列表里移除对应的文章ID
			$user_favorite = array_values(array_filter($user_favorite, function ($element) use ($post_id)
			{
				return $element !== 0 && $element !== $post_id;
			}));

			//更新数组
			$result = update_user_meta($user_id, User_Meta::USER_FAVORITE_POST_LIST, $user_favorite);

			//文章收藏数-1
			delete_post_favorites($post_id);
		}
	}

	return boolval($result);
}



/**
 * 获取用户的关注数组
 * @return int[]
 */
function get_user_followed()
{

	$result  = [];
	$user_id = get_current_user_id();

	//确保用户有登陆
	if ($user_id)
	{
		$result = get_user_meta($user_id, User_Meta::USER_FOLLOW_LIST, true) ?: [];
		$result = array_map(function ($element)
		{
			return intval($element);
		}, $result);
	}

	return array_values($result);
}


/**
 * 检测是否已被用户关注
 * @param int $user_id_to_follow
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
 * 添加新作者到关注数组
 *
 * @param int $user_id_to_follow
 * @return boolean 成功TRUE 失败 false
 */
function add_user_followed($user_id_to_follow)
{

	$result = false;
	$user_id = get_current_user_id();

	if ($user_id && $user_id_to_follow)
	{

		$user_followed = get_user_followed();
		//只有在作者id不存在于数组中的时候 , 避免重复添加
		if (!in_array($user_id_to_follow, $user_followed))
		{
			//添加
			$user_followed[] = $user_id_to_follow;
			//更新
			$result = update_user_meta($user_id, User_Meta::USER_FOLLOW_LIST, $user_followed);
		}
	}

	return boolval($result);
}

/**
 * 从关注数组里移除作者ID
 *
 * @param int $user_id_to_follow
 * @return boolean 成功TRUE 失败 false
 */
function delete_user_followed($user_id_to_follow)
{

	$result = false;

	$user_id = get_current_user_id();
	//确保用户有登陆
	if ($user_id && $user_id_to_follow)
	{
		$user_followed = get_user_followed();
		//如果是取消关注, 确保在数组中已存在相关元素
		if (in_array($user_id_to_follow, $user_followed))
		{
			//过滤掉相关元素
			$user_followed = array_values(array_filter($user_followed, function ($element) use ($user_id_to_follow)
			{
				return  $element !== 0 && $element !== $user_id_to_follow;
			}));
			//更新
			$result = update_user_meta($user_id, User_Meta::USER_FOLLOW_LIST, $user_followed);
		}
	}

	return boolval($result);
}


/**
 * 获取用户的黑名单
 *
 * @param int $user_id
 * @return int[] 拉黑用户ID数组
 */
function get_user_black_list($user_id)
{
	//初始化为空数组
	$result = [];

	//确保用户有登陆
	if ($user_id)
	{
		$result = get_user_meta($user_id, User_Meta::USER_BLACK_LIST, true) ?: [];
		$result = array_map(function ($element)
		{
			return intval($element);
		}, $result);
	}

	return array_values($result);
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

		//从用户自己的关注列表里移除该作者
		delete_user_followed($target_user_id);
	}

	return boolval($result);
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
		$new_black_list = array_values(array_filter($black_list, function ($element) use ($target_user_id)
		{
			return $element !== 0 && $element !== $target_user_id;
		}));

		//更新黑名单
		$result = update_user_meta($user_id, User_Meta::USER_BLACK_LIST, $new_black_list);

		//如果新旧黑名单有变化
		if (count($new_black_list) !== count($black_list))
		{
			//减少目标用户的被拉黑数
			delete_user_blacked_count($target_user_id);
		}
	}

	return boolval($result);
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

	return intval($result);
}

/**
 * 增加用户被拉黑的次数
 * @param int $user_id 当前登陆的用户ID
 * @return boolean 成功TRUE 失败 false
 */
function add_user_blacked_count($user_id)
{
	$result = false;

	//确保用户和目标用户存在
	if ($user_id)
	{
		$count = get_user_blacked_count($user_id);
		$count++;

		//更新
		$result = update_user_meta($user_id, User_Meta::USER_BLACKED_COUNT, $count);
	}

	return boolval($result);
}

/**
 * 减少用户被拉黑的次数
 * @param int $user_id 当前登陆的用户ID
 * @return boolean 成功TRUE 失败 false
 */
function delete_user_blacked_count($user_id)
{
	$result = false;

	//确保用户和目标用户存在
	if ($user_id)
	{
		$count = get_user_blacked_count($user_id);
		$count--;
		//如果数量为负数, 重置为0;
		if ($count < 0)
		{
			$count = 0;
		}

		//更新
		$result = update_user_meta($user_id, User_Meta::USER_BLACKED_COUNT, $count);
	}

	return boolval($result);
}


/**
 * 获取用户的粉丝数量
 *
 * @param int $user_id
 * @return  int
 */
function get_user_fans_count($user_id)
{

	$result = 0;

	//确保用户有登陆
	if ($user_id)
	{
		$result = get_user_meta($user_id, User_Meta::USER_FANS_COUNT, true) ?: 0;
	}

	return intval($result);
}


/**
 * 增加用户的粉丝数
 *
 * @param int $user_id
 * @return boolean 成功TRUE 失败 false
 */
function add_user_fans_count($user_id)
{
	$result = false;
	//确保用户有登陆
	if ($user_id)
	{
		$fans_count = get_user_fans_count($user_id);
		//+1
		$fans_count++;
		//更新
		$result = update_user_meta($user_id, User_Meta::USER_FANS_COUNT, $fans_count);
	}
	return boolval($result);
}

/**
 * 减少用户的粉丝数
 *
 * @param int $user_id
 * @return boolean 成功TRUE 失败 false
 */
function delete_user_fans_count($user_id)
{
	$result = false;
	//确保用户有登陆
	if ($user_id)
	{
		$fans_count = get_user_fans_count($user_id);

		$fans_count--;

		//如果为负数, 重置为0
		if ($fans_count < 0)
		{
			$fans_count = 0;
		}
		//更新
		$result = update_user_meta($user_id, User_Meta::USER_FANS_COUNT, $fans_count);
	}
	return boolval($result);
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

	$result = File_Cache::get_cache_meta_with_callback(
		File_Cache::USER_BADGE,
		File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $user_id,
		Expired::EXP_6_HOURS,
		function () use ($user_id)
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
	);

	return $result;
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
