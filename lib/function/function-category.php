<?php

namespace mikuclub;

use mikuclub\constant\Category;
use mikuclub\constant\Expired;



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

