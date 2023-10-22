<?php

namespace mikuclub;

use mikuclub\constant\Category;
use mikuclub\constant\Config;
use mikuclub\constant\Expired;
use mikuclub\constant\Option_Meta;
use mikuclub\constant\Post_Meta;
use mikuclub\Post_Query;
use mikuclub\constant\Post_Status;
use mikuclub\constant\Post_Type;
use WP_Query;









/**
 * 获取相关文章列表
 *
 * @param int $post_id
 * @param int $count
 *
 * @return My_Post_Model[]
 */
function get_related_post_list($post_id, $count)
{

	//创建缓存键值
	$cache_key = File_Cache::RELATED_POST_LIST . '_' . $post_id . '_' . $count;
	//获取缓存
	$related_post_list = File_Cache::get_cache_meta($cache_key, File_Cache::DIR_POSTS, Expired::EXP_7_DAYS);

	if (empty($related_post_list))
	{

		$args = [
			'post_status'         => Post_Status::PUBLISH,
			'ignore_sticky_posts' => 1,
			'orderby'             => 'rand',
			'posts_per_page'      => $count,
			'post__not_in'        => [$post_id],
		];

		//设置分类
		if (get_post_sub_cat_id($post_id))
		{
			$args['cat'] = get_post_sub_cat_id($post_id);
		}

		//获取标签
		$tags = get_the_tags();

		//如果有标签
		if ($tags)
		{

			//提取标签id数组
			$tag_ids = array_map(function ($tag)
			{
				return $tag->term_id;
			}, $tags);
			//设置标签id过滤参数
			$args['tag__in'] = $tag_ids;
		}

		//查询文章
		$results = get_posts($args);

		//如果结果数量不够 再获取同分类的文章
		if (count($results) < $count)
		{
			//移除标签限制
			unset($args['tag__in']);
			//修改需求数量
			$args['posts_per_page'] = $count - count($results);
			//重新查询文章
			$results2 = get_posts($args);
			//合并结果
			$results = array_merge($results, $results2);
		}

		//把查询到的文章数组转换成自定义文章类
		$related_post_list = array_map(function ($element)
		{
			return new My_Post_Model($element);
		}, $results);

		File_Cache::set_cache_meta($cache_key, File_Cache::DIR_POSTS, $related_post_list);
	}

	return $related_post_list;
}


/**
 * 获取下载失效文章列表
 *
 * @return My_Post_Model[]
 */
function get_fail_down_post_list()
{


	$args = [
		'posts_per_page'      => 20,
		'ignore_sticky_posts' => '1',
		'orderby'             => 'meta_value_num',
		'meta_key'            => Post_Meta::POST_FAIL_TIME,
		'order'               => 'DESC',
	];

	//如果有设置作者ID
	if (isset($_GET['author_id']) && isset($_GET['author_id']))
	{
		$args['author'] = $_GET['author_id'];
	}

	if (isset($_GET['offset']) && isset($_GET['offset']))
	{
		$args['paged'] = $_GET['offset'];
	}

	if (isset($_GET['category']) && isset($_GET['category']))
	{
		$args['cat'] = $_GET['category'];
	}


	//查询文章
	$results = get_posts($args);

	//把查询到的文章数组转换成自定义文章类
	return array_map(function ($element)
	{
		return new My_Post_Model($element);
	}, $results);
}








/**
 * 获取用户收藏夹文章列表
 *
 * @param int $paged
 * @param string $search search value
 * @param int $cat 
 *
 * @return My_Post_Model[]
 */
function get_my_favorite_post_list($paged, $search, $cat)
{

	$user_favorite = get_user_favorite();
	//如果收藏夹未空, 直接返回空数组
	if (empty($user_favorite))
	{
		return [];
	}


	$args = [
		'posts_per_page'      => get_option('posts_per_page'),
		'ignore_sticky_posts' => '1',
		'orderby'             => 'post__in',
		'post__in'            => get_user_favorite(),
		'paged'               => $paged
	];

	//如果有需要搜索的内容, 添加到参数里
	if ($search)
	{
		$args['s'] = $search;
	}

	if ($cat)
	{
		$args['cat'] = $cat;
	}

	$results = get_posts($args);

	//把查询到的文章数组转换成自定义文章类
	return array_map(function ($element)
	{
		return new My_Post_Model($element);
	}, $results);
}


/**
 * 获取关注用户的投稿列表
 *
 * @param int $count 数量
 *
 * @return My_Post_Model[]
 */
function get_my_followed_post_list($count)
{

	$post_list = [];

	//获取用户关注列表
	$user_followed = get_user_followed();

	//如果关注列表不是空
	if ($user_followed)
	{
		$args    = [
			'posts_per_page'      => $count,
			'ignore_sticky_posts' => '1',
			'author'              => implode(',', $user_followed),
		];
		$results = get_posts($args);
		//把查询到的文章数组转换成自定义文章类
		$post_list = array_map(function ($element)
		{
			return new My_Post_Model($element);
		}, $results);
	}

	return $post_list;
}
