<?php

namespace mikuclub;

use mikuclub\constant\Expired;
use WP_User;

/**
 * JWT 相关的函数
 */


/**
 * JWT API 登陆跳过极验证专用
 * 通过使用 session变量 来跳过极验证
 *
 * @param string $default_header
 *
 * @return string
 */
function jwt_auth_skip_gee_verify($default_header)
{

	$_SESSION['login_from_api'] = true;

	return $default_header;
}

add_action('jwt_auth_cors_allow_headers', 'mikuclub\jwt_auth_skip_gee_verify', 100, 1);

/**
 * 修改 JWT API登陆 token令牌的过期时间
 * 默认为 7天,  改为180天
 * 
 * @return int
 */
function modify_jwt_auth_expire()
{

	$expire_days = 180;

	return time() + (Expired::EXP_1_DAY * $expire_days);
}

add_action('jwt_auth_expire', 'mikuclub\modify_jwt_auth_expire');


/**
 * 修改 JWT API登陆后 返回的数据
 *
 * @param array<string, mixed> $data
 * @param WP_User $user
 *
 * @return array<string, mixed>
 */
function modify_jwt_auth_response($data, $user)
{
	$data['id']          = $user->ID;
	$data['user_login']  = $user->data->user_login;
	$data['user_meta']   = get_user_meta($user->ID, '');
	$data['avatar_urls'] = get_my_user_avatar($user->ID);

	return $data;
}

add_action('jwt_auth_token_before_dispatch', 'mikuclub\modify_jwt_auth_response', 10, 2);