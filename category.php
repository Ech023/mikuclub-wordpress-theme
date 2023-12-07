<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;

use function mikuclub\print_adult_404_content_for_no_logging_user;
use function mikuclub\print_breadcrumbs_component;

use function mikuclub\get_hot_list_by_random;
use function mikuclub\get_sub_category_list;
use function mikuclub\get_theme_option;
use function mikuclub\has_sub_category;
use function mikuclub\hot_posts_most_comments;
use function mikuclub\hot_posts_most_rating;
use function mikuclub\is_adult_category;
use function mikuclub\post_list_component;


get_header();

$cat_id = get_queried_object_id();

$output = '';

//如果未登录 访问成人分类 和成人文章 输出404内容
if (!is_user_logged_in() && is_adult_category())
{
	$output = print_adult_404_content_for_no_logging_user();
}
else
{
	$breadcrumbs = print_breadcrumbs_component();

	$sticky_post_slide =  print_sticky_post_slide_component($cat_id);
	$post_list_header = print_post_list_header_component();
	$post_list_component = post_list_component();

	$ad_banner = '';
	if (get_theme_option(Admin_Meta::CATEGORY_TOP_ADSENSE_ENABLE))
	{
		$ad_banner = '<div class="pop-banner text-center my-2 pb-2 border-bottom">' . get_theme_option(Admin_Meta::CATEGORY_TOP_ADSENSE) . '</div>';
	}

	$sub_category_component = '';
	//是有子分类的主分类 + 只在第一页
	if (has_sub_category($cat_id)  && !get_query_var(Post_Query::PAGED))
	{

		$array_sub_category = get_sub_category_list($cat_id);
		if ($array_sub_category)
		{
			$sub_category_items = array_reduce($array_sub_category, function ($result, $category)
			{
				$cat_link = get_category_link($category->term_id);
				$result .= <<<HTML
					<div class="col-auto">
						<a class="btn btn-sm btn-light-2 px-4" href="{$cat_link}">
							{$category->name}
						</a>
					</div>
HTML;
				return $result;
			}, '');

			$sub_category_component = <<<HTML

					<div class="my-2 border-bottom pb-2">
						<div class="mb-2">
							<h5>
								<i class="fa-solid fa-compass me-2"></i> 子分区
							</h5>
						</div>
						<div class="row g-2">
							{$sub_category_items}
						</div>
					</div>
HTML;
		}
	}


	$output = <<<HTML

	{$breadcrumbs}

	{$sticky_post_slide}

	{$ad_banner}

	{$sub_category_component}

	{$post_list_header}

	{$post_list_component}
	
	
HTML;
}

echo $output;

//get_sidebar(); 
get_footer();
