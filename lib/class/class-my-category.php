<?php

namespace mikuclub;

/**
 * 自定义分类模型
 */

if ( ! class_exists( 'My_Category' )

) {

	class My_Category {

		public $term_id='';
		public $name='';

		public function __construct( WP_Term $term ) {



				$this->term_id = $term->term_id;
				$this->name    = $term->name;

		}


	}


}