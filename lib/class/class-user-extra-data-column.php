<?php

namespace mikuclub;

use WP_User;

/**
 * 后台用户列表额外数据列
 */
class User_Extra_Data_Column
{
	//用户注册时间
	const REGISTER_DATE = 'registerdate';
	//最后登陆时间
	const LAST_LOGIN_DATE = 'last_login';

	/**
	 * 注册新的数据列头部
	 *
	 * @param array<string, mixed> $columns
	 * @return array<string, mixed>
	 */
	public static function add_new_column_head($columns)
	{
		$columns[static::REGISTER_DATE] = '注册时间';
		$columns[static::LAST_LOGIN_DATE] = '上次登录';

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
		//如果当前列是注册时间
		if ($column_name === static::REGISTER_DATE || $column_name === static::LAST_LOGIN_DATE)
		{
			//获取对应的数据内容
			$user         = get_userdata($user_id);

			if ($column_name === static::REGISTER_DATE)
			{
				$value = $user->user_registered ? get_date_from_gmt($user->user_registered) : '';
			}
			else if ($column_name === static::LAST_LOGIN_DATE)
			{
				$value = $user->last_login ?? '';
			}
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
			static::REGISTER_DATE => 'registered',
		];

		return wp_parse_args($custom, $columns);
	}

	/**
	 * 在请求中 支持使用注册时间进行排序
	 * 
	 * @param array<string, mixed> $vars
	 * @return array<string, mixed>
	 */
	public static function add_new_column_orderby($vars)
	{
		//如果请求参数里包含 注册时间排序
		if (isset($vars['orderby']) && $vars['orderby'] === static::REGISTER_DATE)
		{
			//转换为对应的请求参数
			$vars = array_merge($vars, [
				'meta_key' => static::REGISTER_DATE,
				'orderby'  => 'meta_value'
			]);
		}

		return $vars;
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
		update_user_meta($user->ID, static::LAST_LOGIN_DATE, current_time('mysql'));
	}
}
