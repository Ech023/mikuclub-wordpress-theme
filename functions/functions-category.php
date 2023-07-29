<?php

/**
 * 检测分类是否有子分类
 *
 * @param int $cat_id
 *
 * @return bool
 */
function has_sub_categories($cat_id)
{
	$has_sub_categories = false;

	if ($cat_id)
	{
		//获取子分类数组
		$sub_categories = get_term_children($cat_id, 'category');
		//子分类数组不是空的, 并且 不是错误对象
		if ($sub_categories && !is_wp_error($sub_categories))
		{
			$has_sub_categories = true;
		}
	}

	return $has_sub_categories;
}


/**
 * 获取子分类列表
 * @return My_Category[]
 */
function get_main_category_list()
{

	//获取缓存
	$categories = get_cache_meta(MAIN_CATEGORY_LIST, '', EXPIRED_1_DAY);
	//无缓存的情况
	if (empty($categories))
	{
		$args = [
			'orderby' => 'name',
			'order'   => 'ASC',
			'parent'  => 0
		];

		$result     = get_categories($args);
		$categories = [];
		foreach ($result as $category)
		{
			$categories[] = new My_Category($category);
		}

		set_cache_meta(MAIN_CATEGORY_LIST, '', $categories);
	}

	return $categories;
}

/**
 * 获取分类的下辖子分类
 *
 * @param int $cat_id
 * @return My_Category[]
 */
function get_main_category_children($cat_id)
{

	//获取缓存
	$cache_key  = 'category_children_' . $cat_id;
	$categories = get_cache_meta($cache_key, '', EXPIRED_7_DAYS);
	//无缓存的情况
	if (empty($categories) && $cat_id)
	{
		$args = [
			'parent' => $cat_id,
		];

		$result     = get_categories($args);
		$categories = [];
		foreach ($result as $category)
		{
			$categories[] = new My_Category($category);
		}

		set_cache_meta($cache_key, '', $categories);
	}

	return $categories;
}
