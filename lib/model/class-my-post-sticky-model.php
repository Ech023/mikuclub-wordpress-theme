<?php

namespace mikuclub;

use WP_Post;


/**
 * 幻灯片置顶文章模型
 */
class My_Post_Sticky_Model extends My_Post_Model
{

	/**
	 * 封面大图地址
	 * @var string
	 */
	public $post_image_large;

	/**
	 *
	 * @param WP_Post $post
	 */
	public function __construct($post)
	{
		parent::__construct($post);

		$array_image_large_src = Post_Image::get_array_image_large_src($post->ID);
		$this->post_image_large = $array_image_large_src[0] ?? '';
	}
}
