<?php

namespace mikuclub;

use WP_Term;

/**
 * 自定义分类模型
 */

class My_Category_Model
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

	/**
	 * @param WP_Term|null $term
	 */
	public function __construct($term = null)
	{
		if ($term instanceof WP_Term)
		{
			$this->term_id = $term->term_id;
			$this->name    = $term->name;
		}
	}
}