<?php

namespace mikuclub;

/**
 * 自定义文章简化版模型
 */


if ( ! class_exists( 'My_Post_Base_Model' )
     && ! class_exists( 'My_Post_Hot' )
     && ! class_exists( 'My_Post_Sticky' )
     && ! class_exists( 'My_Post_Slim' )


) {

	/**
	 * 基础文章类型
	 */
	abstract class My_Post_Base_Model {

		public $id;
		public $post_title;
		public $post_href;
		public $post_image;

		function __construct( WP_Post $post ) {


			$this->id         = $post->ID;
			$this->post_title = $post->post_title;
			$this->post_href  = get_permalink( $post->ID );

		}

	}

	/**
	 * 热门文章模型
	 */
	class My_Post_Hot extends My_Post_Base_Model {


		public $post_views;

		public function __construct( WP_Post $post ) {
			parent::__construct( $post );

			$this->post_image = get_thumbnail_src( $post->ID );
			$this->post_views = get_post_views( $post->ID );

		}

	}


	/**
	 * 幻灯片文章模型
	 */
	class My_Post_Sticky extends My_Post_Base_Model {

		public function __construct( WP_Post $post ) {
			parent::__construct( $post );

			$this->post_image = get_images_large_size( $post->ID )[0];

		}

	}

	/**
	 * 普通文章列表模型
	 */
	class My_Post_Slim extends My_Post_Base_Model {

		public $post_views;
		public $post_likes;
		public $post_comments;
		public $post_favorites;
		public $post_shares;
		public $post_status;

		public $post_author;
		public $post_cat_id;
		public $post_cat_name;
		public $post_cat_href;
		public $post_date;
		public $post_modified_date;

		public function __construct( WP_Post $post ) {
			parent::__construct( $post );


			$this->post_image = get_thumbnail_src( $post->ID );

			$this->post_views     = get_post_views( $post->ID );
			$this->post_likes     = get_post_like( $post->ID );
			$this->post_comments  = get_post_comments( $post->ID );
			$this->post_favorites = get_post_favorites( $post->ID );
			$this->post_shares    = get_post_shares( $post->ID );

			$this->post_status = $post->post_status;

			$this->post_author = get_custom_author( $post->post_author );

			$this->post_cat_id   = get_post_sub_cat_id( $post->ID );
			$this->post_cat_name = get_cat_name( $this->post_cat_id );
			$this->post_cat_href = get_category_link( $this->post_cat_id );

			$this->post_date = get_the_date( Config::DATE_FORMAT_SHORT, $post );
			$this->post_modified_date = get_the_modified_date( Config::DATE_FORMAT_SHORT, $post );

		}


	}



	/**
	 * wpforo论坛主题
	 */
	class My_Wpforo_topic {

		public $id;
		public $post_date;
		public $post_title;
		public $post_href;

		public function __construct( $result_object ) {


			$this->id           = $result_object->topicid ;
			$this->post_date    =  $result_object->modified;
			$this->post_title = $result_object->title;
			$this->post_href  =wpforo_topic($this->id, 'url');

		}

	}


}