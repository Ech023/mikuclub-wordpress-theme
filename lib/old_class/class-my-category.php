<?php

namespace mikuclub;

use WP_Term;

/**
 * 自定义分类模型
 */

if (!class_exists('My_Category'))
{

	class My_Category
	{
		/**
		 * @var int
		 */
		public $term_id;

		/**
		 *
		 * @var string
		 */
		public $name;

		public function __construct(WP_Term $term)
		{

			$this->term_id = $term->term_id;
			$this->name    = $term->name;
		}
	}
}
