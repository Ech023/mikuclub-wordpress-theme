<?php

namespace mikuclub;

use mikuclub\constant\Config;
use WP_Post;


/**
 * 基础文章类型
 */
class My_Post_Model
{

	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $post_title;

	/**
	 * @var string
	 */
	public $post_href;

	/**
	 * @var string
	 */
	public $post_image;
	/**
	 * @var int
	 */
	public $post_views;

	/**
	 * @var int
	 */
	public $post_likes;

	/**
	 * @var int
	 */
	public $post_comments;

	/**
	 * @var int
	 */
	public $post_favorites;

	/**
	 * @var int
	 */
	public $post_shares;

	/**
	 * @var string
	 */
	public $post_status;

	/**
	 *
	 * @var int
	 */
	public $post_author_id;

	/**
	 *
	 * @var My_User_Model
	 */
	public $post_author;

	/**
	 * @var int
	 */
	public $post_cat_id;

	/**
	 * @var string
	 */
	public $post_cat_name;

	/**
	 * @var string
	 */
	public $post_cat_href;

	/**
	 * @var string
	 */
	public $post_date;

	/**
	 * @var string
	 */
	public $post_modified_date;



	/**
	 *
	 * @param WP_Post $post
	 */
	function __construct($post)
	{

		$this->id         = $post->ID;
		$this->post_title = $post->post_title;
		$this->post_href  = get_permalink($post->ID);
		$this->post_image = Post_Image::get_thumbnail_src($post->ID);

		$this->post_views = get_post_views($post->ID);
		$this->post_likes     = get_post_like($post->ID);
		$this->post_comments  = get_post_comments($post->ID);
		$this->post_favorites = get_post_favorites($post->ID);
		$this->post_shares    = get_post_shares($post->ID);

		$this->post_status = $post->post_status;

		$this->post_author_id = intval($post->post_author);
		$this->post_author = get_custom_user($this->post_author_id);

		$this->post_cat_id   = get_post_sub_cat_id($post->ID);
		$this->post_cat_name = get_cat_name($this->post_cat_id);
		$this->post_cat_href = get_category_link($this->post_cat_id);

		$this->post_date = get_the_date(Config::DATE_FORMAT_SHORT, $post);
		$this->post_modified_date = get_the_modified_date(Config::DATE_FORMAT_SHORT, $post);

		
	}
}

