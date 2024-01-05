<?php

namespace mikuclub;

use mikuclub\constant\User_Meta;
use WP_User;
use WP_User_Query;

/**
 * 后台用户列表额外数据列
 */
class User_Extra_Data_Column
{
	//用户注册时间
	const REGISTERED = 'registered';

	const ID = 'id';
	const NICKNAME = 'nickname';

	/**
	 * 注册新的数据列头部
	 *
	 * @param array<string, mixed> $columns
	 * @return array<string, mixed>
	 */
	public static function add_new_column_head($columns)
	{
		unset($columns['name']);
		$columns['id'] = 'ID';
		$columns['nickname'] = '昵称';
		$columns[static::REGISTERED] = '注册时间';
		$columns[User_Meta::USER_LAST_LOGIN] = '上次登录';
		$columns[User_Meta::USER_POINT] = '积分';

		return $columns;
	}


	/**
	 * 注册新的数据列内容
	 * 
	 * @param string $value      Custom column output. Default empty.
	 * @param string $column_name Column name.
	 * @param int    $user_id     ID of the currently-listed user.
	 * @return string
	 * 
	 */
	public static function add_new_column_body($value, $column_name, $user_id)
	{
		$value = '';

		switch ($column_name)
		{
			case static::ID:
				// $user = get_userdata($user_id);
				$value = $user_id;
				break;

			case static::NICKNAME:
				$user = get_userdata($user_id);
				$value = $user ? $user->nickname : '';
				break;

			case static::REGISTERED:
				$user = get_userdata($user_id);
				$value = ($user && $user->user_registered) ? get_date_from_gmt($user->user_registered) : '';

				break;
			case User_Meta::USER_LAST_LOGIN:
				$value = get_user_meta($user_id, User_Meta::USER_LAST_LOGIN, true);
				// $user = get_userdata($user_id);
				// $value = ($user && $user->last_login) ? $user->last_login : '';
				break;

			case User_Meta::USER_POINT:
				$value = get_user_meta($user_id, User_Meta::USER_POINT, true);
				break;
		}


		return $value;
	}

	/**
	 * 在列表中添加 使用注册时间进行排序的链接
	 * 
	 * @param array<string, mixed> $columns
	 * @return array<string, mixed>
	 */
	public static function add_new_column_sortable($columns)
	{
		$custom = [
			// meta column id => sortby value used in query
			static::ID => static::ID,
			static::NICKNAME => static::NICKNAME,
			static::REGISTERED => static::REGISTERED,
			User_Meta::USER_POINT => User_Meta::USER_POINT,
		];

		return wp_parse_args($custom, $columns);
	}

	/**
	 * 在请求中 支持自定义排序 (解析参数前)
	 * 
	 * @param WP_User_Query $user_query
	 * @return void
	 */
	public static function add_new_column_orderby_pre_get($user_query)
	{
		$orderby = $user_query->get('orderby') ?? '';
		switch ($orderby)
		{
			case static::REGISTERED:
				$user_query->set('orderby', 'user_registered');

				break;
			case User_Meta::USER_POINT:
				$user_query->set('meta_key', $orderby);
				$user_query->set('orderby', 'meta_value_num');
				break;
		}
	}

	/**
	 * 更新用户最后一次登录等时间
	 *
	 * @param string  $user_login Username.
	 * @param WP_User $user       WP_User object of the logged-in user.
	 * @return void
	 */
	public static function update_user_last_login_time($user_login, $user)
	{
		update_user_meta($user->ID, User_Meta::USER_LAST_LOGIN, current_time('mysql'));
	}
}
