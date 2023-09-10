<?php

namespace mikuclub;

use WP_Comment;

/**
 * 自定义评论模型
 */


if (!class_exists('My_Comment'))
{

	/**
	 * 基础评论类型
	 */
	class My_Comment
	{

		/**
		 * @var int
		 */
		public $comment_id;

		/**
		 * @var int
		 */
		public $comment_post_id;

		/**
		 * @var string
		 */
		public $comment_date;

		/**
		 * @var string
		 */
		public $comment_content;

		/**
		 * @var string
		 */
		public $comment_approved;

		/**
		 * @var string
		 */
		public $comment_agent;

		/**
		 * Parent comment ID
		 * @var int
		 */
		public $comment_parent;

		/**
		 * @var My_Custom_Comment_User|My_Custom_Deleted_User
		 */
		public $author;

		/**
		 *
		 * @var My_Comment[]
		 */
		public $children;

		/**
		 * 
		 * @var int
		 */
		public $comment_likes;

		function __construct(WP_Comment $comment)
		{



			$this->comment_id       = intval($comment->comment_ID);
			$this->comment_post_id  = intval($comment->comment_post_ID);
			$this->comment_date     = $comment->comment_date;
			$this->comment_content  = convert_smilies($comment->comment_content);
			$this->comment_approved = $comment->comment_approved;
			$this->comment_agent    = $comment->comment_agent;
			$this->comment_parent   = intval($comment->comment_parent);

			$this->author = get_custom_comment_user($comment->user_id);

			$this->children = [];
			//获取子评论
			$comment_children = $comment->get_children();
			//如果不是空的
			if (is_array($comment_children) && $comment_children)
			{
				//递归创建评论实例
				foreach ($comment_children as $comment_child)
				{
					$this->children[] = new My_Comment($comment_child);
				}
			}

			$this->comment_likes = get_comment_likes($this->comment_id);
		}
	}
}
