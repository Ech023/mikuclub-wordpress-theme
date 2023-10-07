<?php

namespace mikuclub;

use mikuclub\constant\Category;
use mikuclub\constant\Expired;
use WP_Term;

// /**
//  * 检测分类ID是否是主分类
//  *
//  * @param int $category_id
//  * @return boolean
//  */
// function is_main_category($category_id)
// {

// 	$result = true;

// 	if ($category_id)
// 	{
// 		//获取分类
// 		$category = get_category($category_id);

// 		//如果不是主分类
// 		if ($category instanceof WP_Term)
// 		{
// 			$result = $category->parent ? true : false;
// 		}
// 	}

// 	return $result;
// }

/**
 * 检测是否是成人区分类
 * @return bool
 */
function is_adult_category()
{

	$result = false;

	//如果是在分类页
	if (is_category())
	{
		$result = (array_search(get_queried_object_id(), Category::get_array_adult()) !== false);
	}
	//如果是文章页
	else if (is_single())
	{
		$result = in_category(Category::get_array_adult());
	}

	return $result;
}

/**
 * 检测分类是否有子分类
 *
 * @param int $cat_id
 *
 * @return bool
 */
function has_sub_category($cat_id)
{
	$result = false;

	if ($cat_id)
	{
		//获取子分类数组
		$sub_categories = get_term_children($cat_id, 'category');
		//子分类数组不是空的, 并且 不是错误对象
		if ($sub_categories && !is_wp_error($sub_categories))
		{
			$result = true;
		}
	}

	return $result;
}

/**
 * 检测当前子分类所属的主分类ID
 *
 * @param int $category_id
 * @return int 如果没有主分类将返回 0
 */
function get_parent_category_id($category_id)
{

	$result = 0;

	if ($category_id)
	{
		//获取分类
		$category = get_category($category_id);

		//如果不是主分类
		if ($category instanceof WP_Term)
		{
			$result = $category->parent;
		}
	}

	return $result;
}



/**
 * 获取分类列表
 * @return My_Category_Model[]
 */
function get_main_category_list()
{

	//获取缓存
	$result = File_Cache::get_cache_meta(File_Cache::MAIN_CATEGORY_LIST, File_Cache::DIR_CATEGORY, Expired::EXP_1_DAY);
	//无缓存的情况
	if (empty($result))
	{
		$args = [
			'orderby' => 'name',
			'order'   => 'ASC',
			'parent'  => 0
		];

		$array_category_object = get_categories($args);
		//转换成category model
		$result = array_map(function (WP_Term $category)
		{
			return new My_Category_Model($category);
		}, $array_category_object);

		File_Cache::set_cache_meta(File_Cache::MAIN_CATEGORY_LIST, File_Cache::DIR_CATEGORY, $result);
	}

	return $result;
}



/**
 * 获取特定分类的子分类列表
 *
 * @param int $cat_id
 * @return My_Category_Model[]
 */
function get_sub_category_list($cat_id)
{
	$result = [];

	if ($cat_id)
	{

		//获取缓存
		$cache_key  = File_Cache::SUB_CATEGORY_LIST . '_' . $cat_id;
		$result = File_Cache::get_cache_meta($cache_key, File_Cache::DIR_CATEGORY, Expired::EXP_7_DAYS);
		//无缓存的情况
		if (empty($result))
		{
			$args = [
				'orderby' => 'name',
				'order'   => 'ASC',
				'parent' => $cat_id,
			];

			$array_category_object     = get_categories($args);
			//转换成category model
			$result = array_map(function (WP_Term $category)
			{
				return new My_Category_Model($category);
			}, $array_category_object);

			File_Cache::set_cache_meta($cache_key, File_Cache::DIR_CATEGORY, $result);
		}
	}

	return $result;
}
