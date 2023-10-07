<?php

namespace mikuclub;

use mikuclub\constant\User_Capability;

/**
 *  用户相关函数
 */



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

	}
}

/**
 * 检查是否是黑名单用户, 如果是, 直接跳转到其他网站
 * @return void
 */
function check_blocked_user()
{
	//获取缓存
	$is_blocked_user = Session_Cache::get(Session_Cache::IS_BLOCKED_USER);
	//如果缓存不存在
	if ($is_blocked_user === null)
	{
		//检测是否是黑名单用户
		$is_blocked_user = !current_user_can(User_Capability::READ);
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
