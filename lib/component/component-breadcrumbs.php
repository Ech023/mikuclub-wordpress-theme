<?php

namespace mikuclub;

/**
 * 面包屑导航
 * @return string
 */
function print_breadcrumbs_component()
{

	global $wp_query;


	$output = '';

	$item_start        = '<li class="breadcrumb-item">';
	$item_start_active = '<li class="breadcrumb-item active">';
	$item_end  = '</li>'; // 在当前链接后插入

	$local_html = '';
	$home       = get_home_url();

	//如果是 主页-最新发布页面
	if (is_home())
	{
		$local_html = $item_start_active . '全站排行' . $item_end;
	} //标签页
	else if (is_tag())
	{
		$local_html = $item_start_active . '标签 ' . single_tag_title('', false) . $item_end;
	}
	else if (is_page())
	{
		$local_html = $item_start_active . get_the_title() . $item_end;
	}
	else if (is_search())
	{
		$local_html = $item_start_active . '搜索页' . $item_end;
	}

	//如果是分类 或者 文章页
	else if (is_category() || is_single())
	{

		//如果是分类
		if (is_category())
		{
			$current_cat_id = $wp_query->get_queried_object()->term_id;
			$current_cat = (object)get_category($current_cat_id);
			$local_html = $item_start_active . $current_cat->name . $item_end;
		}
		//如果是文章页
		else
		{

			$categories = get_the_category();
			if ($categories)
			{
				$current_cat = $categories[0];
				$current_cat_link = get_category_link($current_cat->term_id);

				$local_html = <<<HTML
					{$item_start}
						<a href="{$current_cat_link}">
							{$current_cat->name}
						</a>
					{$item_end}
HTML;
			}
			else
			{
				$current_cat = false;
			}
		}

		//如果有父分类 就循环添加
		while (is_object($current_cat) && $current_cat->parent != 0)
		{

			//获取父分类
			$current_cat = get_category($current_cat->parent);

			//确保只有在分类获取成功的时候才添加
			if (is_object($current_cat))
			{
				$current_cat_link = get_category_link($current_cat->term_id);

				$local_html = <<<HTML
					{$item_start}
						<a href="{$current_cat_link}">
							{$current_cat->name}
						</a>
					{$item_end}
					{$local_html}
HTML;

			}
		}
	}

	//如果是在分页并且大于1 添加页面数字说明
	$paged = intval(get_query_var(Post_Query::PAGED));
	if ($paged > 1)
	{
		$local_html .= ' 第 ' . $paged . ' 页';
	}

	if ($local_html)
	{
		$output = <<<HTML
			<div class="my-2">
				<nav style="--bs-breadcrumb-divider: '>';">
					<ol class="breadcrumb bg-transparent mb-0">
						$item_start
						<a href="{$home}">
							<i class="fa-solid fa-house"></i>
						</a>
						$item_end
						$local_html
						$item_end
					</ol>
				</nav>
			</div>

HTML;
	}


	return $output;
}
