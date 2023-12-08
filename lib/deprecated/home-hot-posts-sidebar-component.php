<?php
namespace mikuclub;

use mikuclub\constant\Post_Meta;

/**
 * 热门文章列表组件
 *
 * @param My_Post_Model[] $post_list
 * @param string $meta_key
 * @param string $title
 * @param string $title_icon
 * @param string $item_icon
 *
 * @return string html内容
 */
function home_hot_posts_sidebar_component($post_list, $meta_key, $title, $title_icon, $item_icon)
{

	$output = '';

	//初始化排行名次
	$num = 1;

	$post_list_html = '';
	foreach ($post_list as $my_post)
	{

		//获取排行用的数值
		$meta_value = get_post_meta($my_post->id, $meta_key, true);

		$post_list_html .= <<<HTML

			<div class="col">
				<div class="row">
					<div class="col-auto">
						<div class="badge text-bg-miku">{$num}</div>
					</div>
					<div class="col d-none d-xxl-block">
						<a class="image_link d-block position-relative " title="{$my_post->post_title}" href="{$my_post->post_href}" target="_blank" >
							<div class="position-absolute end-0 bottom-0 me-1 mb-1">
								<div class="right-badge bg-transparent-half text-light rounded small p-1">
											<i class="{$item_icon}"></i> {$meta_value}
								</div>
							</div>
							<img class="img-fluid w-100 h-100" src="{$my_post->post_image}" alt="{$my_post->post_title}"/>
						</a>
					</div>
					<div class="col">
						<a class="d-block overflow-hidden" title="{$my_post->post_title}" href="{$my_post->post_href}" target="_blank" style="height: 3.0rem;">
							{$my_post->post_title}
						</a>
					</div>

				</div> 
            </div>

HTML;

		$num++;
	}

	//只有在列表有内容的情况下 才会输出
	if ($post_list_html)
	{

		$output = <<< HTML

		<div class="hot-post-sidebar">
			<div class="list-header row my-3">
				<h5>
	                <i class="{$title_icon}"></i> {$title}
	            </h5>
			</div>
			<div  class="list-header my-2">
				
			</div>
			<div class="list-body row row-cols-1 gy-3">
				{$post_list_html}
			</div>
		</div>
HTML;
	}


	return $output;
}




/**
 * 随机获取一种热门类型的列表
 *
 * @param int|null $term_id //分类ID 或者 标签ID
 * @param int $number //文章数量
 *
 * @return string HTML内容
 */
function get_home_hot_posts_sidebar_component_by_random($term_id, $number = 6)
{
	$result = '';

	$index = rand(0, 2);

	if ($index == 0)
	{

		//获取近期点击最多的文章列表

		$range_day = 21;
		//统计的键名
		$meta_key = Post_Meta::POST_VIEWS;

		$title = '热门';
		//标题的图标
		$title_icon = 'fa-solid fa-fire';
		//元素的图标
		$item_icon = 'fa-solid fa-eye';

		//获取文章列表
	
		$post_list = get_hot_post_list($term_id, $meta_key, $number);
		//转换成html输出
		$result = home_hot_posts_sidebar_component($post_list, $meta_key, $title, $title_icon, $item_icon);
	}
	else if ($index == 1)
	{

		//获取近期评分最多的文章列表

		$range_day = 28;
		//统计的键名
		$meta_key = Post_Meta::POST_LIKE;

		$title = '推荐';
		//标题的图标
		$title_icon = 'fa-solid fa-heart';
		//元素的图标
		$item_icon = 'fa-solid fa-star';


		//获取文章列表
		
		$post_list = get_hot_post_list($term_id, $meta_key, $number);
		//转换成html输出
		$result = home_hot_posts_sidebar_component($post_list, $meta_key, $title, $title_icon, $item_icon);
	}
	else
	{

		//获取近期评论最多的文章列表

		//统计基础周期 ;
		$range_day = 28;
		//统计的键名
		$meta_key = Post_Meta::POST_COMMENT_COUNT;

		$title = '评论榜';
		//标题的图标
		$title_icon = 'fa-solid fa-comments';
		//元素的图标
		$item_icon = 'fa-solid fa-comment';

		//获取文章列表
		$post_list = get_hot_post_list($term_id,  $meta_key, $number);
		//转换成html输出
		$result = home_hot_posts_sidebar_component($post_list, $meta_key, $title, $title_icon, $item_icon);
	}

	return $result;
}
