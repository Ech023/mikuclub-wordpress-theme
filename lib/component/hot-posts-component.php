<?php
namespace mikuclub;
/**
 * 热门文章列表组件
 *
 * @param My_Post_Hot[] $post_list
 * @param string $meta_key
 * @param string $title
 * @param string $title_icon
 * @param string $item_icon
 *
 * @return string html内容
 */
function hot_posts_component( $post_list, $meta_key, $title, $title_icon, $item_icon ) {

	$output = '';

	//初始化排行名次
	$num = 1;

	$post_list_html = '';
	foreach ( $post_list as $my_post ) {

		//获取排行用的数值
		$meta_value = get_post_meta( $my_post->id, $meta_key, true );

		$post_list_html .= <<<HTML

			<div class="col card border-0 my-1">
							<div class="card-img-container position-relative">
								<div class="position-absolute end-0 bottom-0 me-1 mb-1">
							            <div class="right-badge bg-transparent-half text-light rounded small p-1">
							                      <i class="{$item_icon}"></i> {$meta_value}
					                   </div>
								</div>
							    <img class="card-img-top" src="{$my_post->post_image}" alt="{$my_post->post_title}"/>
							</div>
							
							<div class="card-body  py-2 px-0 overflow-hidden post-title text-2-rows">
								 <a class="card-link stretched-link " title="{$my_post->post_title}" href="{$my_post->post_href}" target="_blank">
								    {$my_post->post_title}
								</a>
							</div>
			                  
            </div>

HTML;

		$num ++;
	}

	//只有在列表有内容的情况下 才会输出
	if ( $post_list_html ) {

		$output = <<< HTML

		<div class="hot-post-list my-4">
			<div  class="list-header row my-3">
				<h4 class="col">
	                <i class="{$title_icon}"></i> {$title}
	            </h4>
			</div>
			<div class="list-body row row-cols-2 row-cols-md-4 gy-2">
					{$post_list_html}
			</div>
		</div>
HTML;

	}


	return $output;

}

/**
 * 获取近期最多点击数的文章列表
 *
 * @param int $number //文章数量
 *
 * @return string
 */

function hot_posts_most_views( $number = 6 ) {

	//统计基础周期 是  10天;
	$range_day = 14;
	//统计的键名
	$meta_key = Post_Meta::POST_VIEWS;

	$title = '热门';
	//标题的图标
	$title_icon = 'fa-solid fa-fire';
	//元素的图标
	$item_icon = 'fa-solid fa-eye';

	//获取文章列表
	$term_id = get_queried_object() ? get_queried_object()->term_id : null;
	$post_list = get_hot_post_list($term_id, $meta_key, $number, $range_day );
	//转换成html输出
	return hot_posts_component( $post_list, $meta_key, $title, $title_icon, $item_icon );

}


/**
 * 获取近期评分最多的文章列表
 *
 * @param int $number //文章数量
 *
 * @return string
 */
function hot_posts_most_rating( $number = 6 ) {

	//统计基础周期 是  10天;
	$range_day = 21;
	//统计的键名
	$meta_key = Post_Meta::POST_LIKE;

	$title = '推荐';
	//标题的图标
	$title_icon = 'fa-solid fa-heart';
	//元素的图标
	$item_icon = 'fa-solid fa-star';


	//获取文章列表
	$term_id = get_queried_object() ? get_queried_object()->term_id : null;
	$post_list = get_hot_post_list($term_id, $meta_key, $number, $range_day );
	//转换成html输出
	return hot_posts_component( $post_list, $meta_key, $title, $title_icon, $item_icon );


}


/**
 * 获取近期评论最多的文章列表
 *
 * @param int $number //文章数量
 *
 * @return string
 */
function hot_posts_most_comments( $number = 6 ) {


	//统计基础周期 是  10天;
	$range_day = 21;
	//统计的键名
	$meta_key = Post_Meta::POST_COMMENT_COUNT;

	$title = '评论榜';
	//标题的图标
	$title_icon = 'fa-solid fa-comments';
	//元素的图标
	$item_icon = 'fa-solid fa-comment';

	//获取文章列表
	$term_id = get_queried_object() ? get_queried_object()->term_id : null;
	$post_list = get_hot_post_list($term_id,  $meta_key, $number, $range_day );
	//转换成html输出
	return hot_posts_component( $post_list, $meta_key, $title, $title_icon, $item_icon );

}

/**
 * 随机获取一种热门类型的列表
 *
 * @param int $number //文章数量
 *
 * @return string HTML内容
 */
function get_hot_list_by_random( $number = 6 ) {

	$index = rand( 0, 2 );
	if ( $index == 0 ) {
		$output = hot_posts_most_views( $number );
	}
	else if ( $index == 1 ) {
		$output = hot_posts_most_rating( $number );
	}
	else {
		$output = hot_posts_most_comments( $number );
	}

	return $output;

}