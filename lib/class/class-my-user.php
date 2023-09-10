<?php

namespace mikuclub;

use WP_User;

/**
 * 自定义用户模型
 */


if (
	!class_exists('My_User_Base_Model')
	&& !class_exists('My_User')
	&& !class_exists('My_System_User')
	&& !class_exists('My_Custom_Comment_User')
	&& !class_exists('My_Custom_Deleted_User')

)
{

	/**
	 * 基础用户类原型 (创建空用户实例的时候需要)
	 */
	abstract class My_User_Base_Model
	{

		/**
		 * @var int
		 */
		public $id;
		/**
		 * @var string
		 */
		public $user_login;
		/**
		 * @var string
		 */
		public $display_name;
		/**
		 * @var string
		 */
		public $user_href;
		/**
		 * @var string
		 */
		public $user_image;
		/**
		 * @var string
		 */
		public $user_description;
	}


	/**
	 * 标准用户对象
	 */
	class My_User extends My_User_Base_Model
	{

		/**
		 * My_User constructor.
		 * @param WP_User $user
		 */
		function __construct(WP_User $user)
		{


			$this->id               = $user->ID;
			$this->user_login       = $user->user_login;
			$this->display_name     = $user->display_name;
			$this->user_href        = get_author_posts_url($user->ID);
			$this->user_image       = get_my_user_avatar($user->ID);
			$this->user_description = get_user_meta($user->ID, 'description', true);
		}
	}


	class My_System_User extends My_User_Base_Model
	{

		public function __construct()
		{

			$this->id           = 0;
			$this->display_name = $this->user_login = $this->user_description = "系统通知";
			$this->user_image   = get_home_url() . '/img/网站系统消息头像.jpg';
		}
	}

	/**
	 * 自定义评论作者类
	 * 增加用户等级 和 勋章数组
	 */
	class My_Custom_Comment_User extends My_User
	{
		/**
		 * @var string
		 */
		public $user_level;

		/**
		 * @var array<int, array<string, mixed>>
		 */
		public $user_badges;

		public function __construct($user)
		{

			parent::__construct($user);

			$this->user_level  = get_user_level($user->ID);
			$this->user_badges = get_user_badges($user->ID);
		}
	}

	/**
	 * 已被删除用户的模板
	 */
	class My_Custom_Deleted_User extends My_User_Base_Model
	{
		/**
		 * @var string
		 */
		public $user_level;

		/**
		 * @var array<int, array<string, mixed>>
		 */
		public $user_badges;

		public function __construct()
		{

			$this->id               = 0;
			$this->user_login       = 'unknown';
			$this->display_name     = '该用户已注销';
			$this->user_href        = '';
			$this->user_image       = get_my_user_default_avatar();
			$this->user_description = '';
			$this->user_level       = '';
			$this->user_badges      = [];
		}
	}
}
