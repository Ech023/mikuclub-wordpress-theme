<?php

namespace mikuclub;

use mikuclub\constant\Comment_Meta;
use WP_Comment;

/**
 * 自定义评论回复模型
 */



/**
 * 扩展 子评论回复类
 */
class My_Comment_Reply_Model extends My_Comment_Model
{

	/**
	 * @var int
	 */
	public $comment_parent_user_read;

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
	 * @var My_User_Model
	 */
	public $author;

	//用来兼容 安卓 APP 的老参数
	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var string
	 */
	public $content;
	/**
	 * @var string
	 */
	public $date;
	/**
	 * @var int
	 */
	public $parent;
	/**
	 * @var int
	 */
	public $post;
	/**
	 * @var string
	 */
	public $post_title;
	/**
	 * @var int
	 */
	public $status;

	/**
	 * @param WP_Comment|null $comment
	 */
	function __construct($comment = null)
	{
		parent::__construct($comment);

		$this->comment_parent_user_read = get_comment_meta($this->comment_id, Comment_Meta::COMMENT_PARENT_USER_READ, true) ? 1 : 0;
		$this->comment_post_title       = get_the_title($this->comment_post_id);
		$this->comment_post_href        = get_permalink($this->comment_post_id);

		//替换原版author
		$this->author = get_custom_user(intval($comment->user_id));

		//用来兼容 安卓 APP
		$this->id         = $this->comment_id;
		$this->content    = $this->comment_content;
		$this->date       = $this->comment_date;
		$this->parent     = $this->comment_parent;
		$this->post       = $this->comment_post_id;
		$this->post_title = $this->comment_post_title;
		$this->status     = $this->comment_parent_user_read;
	}
}
