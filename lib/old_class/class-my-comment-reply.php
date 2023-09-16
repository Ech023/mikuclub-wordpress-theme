<?php

namespace mikuclub;

use mikuclub\constant\Comment_Meta;
use WP_Comment;

/**
 * 自定义评论回复模型
 */


if (!class_exists('My_Comment_Reply'))
{

	/**
	 * 基础评论回复类型
	 */
	class My_Comment_Reply
	{

		/**
		 * Undocumented variable
		 *
		 * @var int
		 */
		public $comment_id;

		/**
		 * @var string
		 */
		public $comment_content;

		/**
		 * @var string
		 */
		public $comment_date;

		/**
		 * Parent comment ID
		 * @var int
		 */
		public $comment_parent;

		/**
		 * @var int
		 */
		public $comment_parent_user_read;

		/**
		 * @var int
		 */
		public $comment_post_id;

		/**
		 * @var string
		 */
		public $comment_post_title;

		/**
		 * @var string
		 */
		public $comment_post_href;

		/**
		 *
		 * @var My_System_User|My_User
		 */
		public $author;


		function __construct(WP_Comment $comment)
		{

			$this->comment_id      = intval($comment->comment_ID);
			$this->comment_content = $comment->comment_content;
			$this->comment_date    = $comment->comment_date;
			$this->comment_parent  = intval($comment->comment_parent);
			$this->comment_post_id = intval($comment->comment_post_ID);

			$this->comment_parent_user_read = get_comment_meta($this->comment_id, Comment_Meta::COMMENT_PARENT_USER_READ, true) ? 1 : 0;
			$this->comment_post_title       = get_the_title($this->comment_post_id);
			$this->comment_post_href        = get_permalink($this->comment_post_id);

			$this->author = get_custom_author(intval($comment->user_id));
		}
	}
}
