<?php

namespace mikuclub;

/**
 * 自定义评论回复模型
 */


if ( ! class_exists( 'My_Comment_Reply' )


) {

	/**
	 * 基础评论回复类型
	 */
	class My_Comment_Reply {

		public $comment_id = '';
		public $comment_content = '';
		public $comment_date = '';
		public $comment_parent = '';
		public $comment_parent_user_read;
		public $comment_post_id = '';
		public $comment_post_title = '';
		public $comment_post_href = '';
		public $author = '';


		/*
		 * 全部升级到安卓1.2后可以删除=====================*/
		public $id = '';
		public $content = '';
		public $date = '';
		public $parent = '';
		public $post = '';
		public $post_title = '';
		public $status = '';

		/*========================*/


		function __construct( WP_Comment $comment ) {



				$this->comment_id      = $comment->comment_ID;
				$this->comment_content = $comment->comment_content;
				$this->comment_date    = $comment->comment_date;
				$this->comment_parent  = $comment->comment_parent;
				$this->comment_post_id = $comment->comment_post_ID;

				$this->comment_parent_user_read = get_comment_meta( $comment->comment_ID, Comment_Meta::COMMENT_PARENT_USER_READ, true );
				$this->comment_post_title       = get_the_title( $comment->comment_post_ID );
				$this->comment_post_href        = get_permalink( $comment->comment_post_ID );

				$this->author = get_custom_author( $comment->user_id );


				/* 全部升级到安卓1.2后可以删除=====================*/
				$this->id         = $comment->comment_ID;
				$this->content    = $comment->comment_content;
				$this->date       = $comment->comment_date;
				$this->parent     = $comment->comment_parent;
				$this->post       = $comment->comment_post_ID;
				$this->post_title = $this->comment_post_title;
				$this->status     = $this->comment_parent_user_read;
				/*========================*/

		}
	}


}