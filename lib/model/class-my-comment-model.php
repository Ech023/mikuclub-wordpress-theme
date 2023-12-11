<?php

namespace mikuclub;

use WP_Comment;

/**
 * 自定义评论模型
 */




/**
 * 基础评论类
 */
class My_Comment_Model
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
	 * @var My_User_Model
	 */
	public $author;

	/**
	 *
	 * @var My_Comment_Model[]
	 */
	public $children = [];

	/**
	 * 
	 * @var int
	 */
	public $comment_likes;

	/**
	 * 是否是置顶评论
	 * @var bool
	 */
	public $comment_is_sticky = false;

	/**
	 * @param WP_Comment|null $comment
	 */
	function __construct($comment = null)
	{

		if ($comment instanceof WP_Comment)
		{
			$this->comment_id       = intval($comment->comment_ID);
			$this->comment_post_id  = intval($comment->comment_post_ID);
			$this->comment_date     = $comment->comment_date;
			$this->comment_content  = convert_smilies($comment->comment_content);
			$this->comment_approved = $comment->comment_approved;
			$this->comment_agent    = $comment->comment_agent;
			$this->comment_parent   = intval($comment->comment_parent);
			$this->comment_likes = get_comment_like($this->comment_id);

			$this->author = get_custom_user(intval($comment->user_id));

			//获取子评论
			$this->children = array_values(array_map(function (WP_Comment $children_comment)
			{
				return new My_Comment_Model($children_comment);
			}, $comment->get_children()));

			
		}
	}
}
