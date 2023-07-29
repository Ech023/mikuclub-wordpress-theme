<?php


/**
 * 自定义评论模型
 */


if ( ! class_exists( 'My_Comment' )


) {

	/**
	 * 基础评论类型
	 */
	class My_Comment {

		public $comment_id = '';
		public $comment_post_id = '';
		public $comment_date = '';
		public $comment_content = '';
		public $comment_approved = '';
		public $comment_agent = '';
		public $comment_parent = '';

		public $author = '';
		public $children = [];

		public $comment_likes = 0;

		function __construct( WP_Comment $comment ) {



				$this->comment_id       = $comment->comment_ID;
				$this->comment_post_id  = $comment->comment_post_ID;
				$this->comment_date     = $comment->comment_date;
				$this->comment_content  = convert_smilies( $comment->comment_content );
				$this->comment_approved = $comment->comment_approved;
				$this->comment_agent    = $comment->comment_agent;
				$this->comment_parent   = $comment->comment_parent;

				$this->author = get_custom_comment_user( $comment->user_id );

				//获取子评论
				$comment_children = $comment->get_children();
				//如果不是空的
				if ( is_array( $comment_children ) && $comment_children ) {
					//递归创建评论实例
					foreach ( $comment_children as $comment_child ) {
						$this->children[] = new My_Comment( $comment_child );
					}
				}

				$this->comment_likes = get_comment_likes($comment->comment_ID);


		}
	}


}