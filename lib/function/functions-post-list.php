<?php

namespace mikuclub;

use mikuclub\constant\Category;
use mikuclub\constant\Expired;
use mikuclub\constant\Option_Meta;
use mikuclub\constant\Post_Meta;
use mikuclub\constant\Post_Query;
use mikuclub\constant\Post_Status;
use WP_Query;

/**
 * 为 未登陆用户 移除魔法区文章的显示
 *
 * @param WP_Query $wp_query
 * @return void
 */
function on_pre_get_main_posts($wp_query)
{

	//只在主查询中生效
	if ($wp_query->is_main_query())
	{

		//排除置顶文章
		set_query_var('ignore_sticky_posts', 1);

		//修改默认排序
		/*
		if ( ! get_query_var( 'orderby' ) ) {
			//使用最后修改时间作为默认排序
			set_query_var( 'orderby', 'modified' );
		}*/


		//只有在不是页面 + 有自定义排序变量 的时候
		if (!is_page() && get_query_var(Post_Query::CUSTOM_ORDERBY))
		{
			set_query_var('meta_key', get_query_var(Post_Query::CUSTOM_ORDERBY));
			set_query_var('orderby', 'meta_value_num');
		}

		//只有在不是页面 + 有设置自定义日期范围
		if (!is_page() && get_query_var(Post_Query::CUSTOM_ORDER_DATA_RANGE))
		{
			$date_query = [
				[
					'after' => get_query_var(Post_Query::CUSTOM_ORDER_DATA_RANGE) . ' month ago'
				]
			];
			set_query_var('date_query', $date_query);
		}

		//如果是作者页面 并且 设置了内部搜索键值
		if (is_author() && get_query_var(Post_Query::AUTHOR_INTERNAL_SEARCH))
		{
			//添加搜索参数
			set_query_var('s', get_query_var(Post_Query::AUTHOR_INTERNAL_SEARCH));
		}

		//主页+未设置分类过滤 默认移除成人区分类
		if (is_home() && (empty(get_query_var('page_type')) || get_query_var('page_type') == 'home'))
		{
			set_query_var('cat', Category::NO_ADULT_CATEGORY);
		}

		//如果是搜索页
		if (is_search())
		{
			set_query_var('post_type', 'post');
		}


		//如果用户未登陆 排除 魔法区分类
		exclude_adult_category_for_not_logged_user();
	}
}

add_action('pre_get_posts', 'mikuclub\on_pre_get_main_posts');


/**
 * 主查询内 如果用户未登陆 排除魔法区分类
 * 
 * @return void
 **/
function exclude_adult_category_for_not_logged_user()
{


	//未登录用户 并且不在首页
	if (!is_user_logged_in() && !is_home() && !is_singular())
	{

		//获取现有cat查询参数, 追加或者 重新设置
		$cat = get_query_var('cat');
		if ($cat)
		{
			$cat .= ',-' . Category::ADULT_CATEGORY;
		}
		else
		{
			$cat = Category::NO_ADULT_CATEGORY;
		}
		set_query_var('cat', $cat);
	}
}


/**
 * 获取置顶文章列表
 *
 * @param number $number 文章数量
 *
 * @return My_Post_Sticky[]
 */
function get_sticky_posts($number)
{

	//如果是在分类则获取他们对应的id
	if (is_category())
	{
		$term_id = get_queried_object()->term_id;
	}
	else
	{
		// 否则在全站范围选择
		$term_id = '-1120';
	}

	//缓存时间的键名
	$cache_meta_key = Option_Meta::STICKY_POSTS . '_' . $term_id . '_' . $number;
	//获取缓存
	$output = File_Cache::get_cache_meta($cache_meta_key, File_Cache::DIR_POSTS, Expired::EXP_2_HOURS);

	//如果缓存无效, 就重新计算
	if (empty($output))
	{

		//获取置顶文章id数组
		$sticky = get_option(Option_Meta::STICKY_POSTS);

		$output = [];

		//只有在 拥有符合条件的置顶文章数组情况下 才会显示
		if ($sticky && is_array($sticky) && count($sticky) >= $number)
		{

			$args = [
				'post__in'            => $sticky,
				'ignore_sticky_posts' => 1,
				'cat'                 => $term_id,
				'orderby'             => 'post__in',
				'posts_per_page'      => $number,
			];
			//查询文章数据
			$results = get_posts($args);

			//把查询到的文章数组转换成自定义文章类
			$output = array_map(function ($element)
			{
				return new My_Post_Sticky($element);
			}, $results);

			//创建新缓存
			File_Cache::set_cache_meta($cache_meta_key, File_Cache::DIR_POSTS, $output);
		}
	}

	return $output;
}


/**
 *
 * 获取特定分类下的文章id列表
 *
 * @param int|null $term_id 分类ID 或者 标签ID
 * @param string $meta_key 要统计的元数据名称
 * @param int $number 文章数量
 * @param int $range_day 统计周期
 *
 * @return My_Post_Hot[] 文章数组
 */
function get_hot_post_list($term_id, $meta_key, $number, $range_day)
{



	//$term_id = get_queried_object()->term_id;


	//如果分类/标签id获取失败
	if (!$term_id)
	{
		//设置成 魔法区ID
		$term_id = Category::NO_ADULT_CATEGORY;
	}

	//缓存时间的键名
	$cache_meta_key = [
		$meta_key,
		$term_id,
		$number,
		$range_day
	];

	$sanitize_file_name =  preg_replace('/[^a-z0-9-]+/', '_', strtolower(implode('_', array_values($cache_meta_key))));
	$cache_meta_key =  File_Cache::HOT_POST_LIST . '_' . $sanitize_file_name;


	//获取缓存
	$output = File_Cache::get_cache_meta($cache_meta_key, File_Cache::DIR_POSTS, Expired::EXP_2_HOURS);

	//如果缓存已过期 将返回 空字符串
	if (empty($output))
	{

		//当前时间
		$now = time();
		//统计的周期长度 (秒数)
		$time_range = Expired::EXP_1_DAY * $range_day;
		//开始统计的周期
		$after_time = $now - $time_range;


		$args = [
			'posts_per_page' => $number,
			'meta_key'       => $meta_key,
			'order'          => 'DESC',
			'orderby'        => 'meta_value_num',
			'post_status'    => Post_Status::PUBLISH,
			'post_type'      => 'post',
			'date_query'     => [
				[
					'after ' => [
						'year'  => date('Y', $after_time),
						'month' => date('n', $after_time),
						'day'   => date('j', $after_time),
					],
				]
			],
		];

		//如果是首页 设置分类过滤
		if (is_home())
		{
			$args['cat'] = $term_id;
		}
		//如果在分类页
		else if (is_category())
		{
			$args['cat'] = $term_id;
		}
		//如果是标签页
		else if (is_tag())
		{
			$args['tag_id'] = $term_id;
		}

		//查询文章
		$results = get_posts($args);

		//如果查询到的文章数量低于 列表需要的长度  就加长统计周期, 重新获取, 最多重试3次
		for ($i = 2; $i < 5 && count($results) < $number; $i++)
		{
			//翻倍统计周期
			$after_time = $now - $time_range * $i;
			//重设开始统计的日期位置
			$args['date_query'][0]['after'] = [
				'year'  => date('Y', $after_time),
				'month' => date('n', $after_time),
				'day'   => date('j', $after_time),
			];
			//重新获取文章
			$results = get_posts($args);
		}

		//把查询到的文章数组转换成自定义文章类
		$output = array_map(function ($element)
		{
			return new My_Post_Hot($element);
		}, $results);

		//创建新缓存
		File_Cache::set_cache_meta($cache_meta_key, File_Cache::DIR_POSTS, $output);
	}

	return $output;
}



/**
 * 获取主页最新文章列表
 *
 * @param int $cat_id 分类id
 * @param int $count 数量
 *
 * @return My_Post_Hot[] 自定义文章列表
 */
function get_cat_recently_post_list($cat_id, $count)
{

	//文章查询参数
	$args = [
		'posts_per_page' => $count,
		'post_status'    => Post_Status::PUBLISH,
		'post_type'      => 'post',
	];

	//如果存在 分类ID
	if ($cat_id > 0)
	{
		//设置该分类过滤
		$args['cat'] = $cat_id;
	}
	else
	{
		//排除成人区
		$args['cat'] = Category::NO_ADULT_CATEGORY;
	}

	$results = get_posts($args);

	//把查询到的文章数组转换成自定义文章类
	return array_map(function ($element)
	{
		return new My_Post_Hot($element);
	}, $results);
}


/**
 * 获取相关文章列表
 *
 * @param int $post_id
 * @param int $count
 *
 * @return My_Post_Hot[]
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
			return new My_Post_Hot($element);
		}, $results);

		File_Cache::set_cache_meta($cache_key, File_Cache::DIR_POSTS, $related_post_list);
	}

	return $related_post_list;
}


/**
 * 获取下载失效文章列表
 *
 * @return My_Post_Slim[]
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
		return new My_Post_Slim($element);
	}, $results);
}


/**
 * 获取通用文章列表
 * 转换成自定义文章格式
 *
 * @param array<string, mixed> $query_vars 查询参数
 *
 * @return My_Post_Slim[]
 */
function get_post_list($query_vars)
{

	//根据键值重新排序数组, 避免cache键值错乱
	//ksort($query_vars);



	//$sanitize_file_name =  preg_replace('/[^a-z0-9-]+/', '_', strtolower(implode('_', array_values($query_vars))));


	//创建缓存键值
	$cache_key = File_Cache::POST_LIST . '_' . create_hash_string($query_vars);



	//获取缓存
	$my_post_slim_list = File_Cache::get_cache_meta($cache_key, File_Cache::DIR_POSTS, Expired::EXP_15_MINUTE);

	//如果不存在 或者 有禁用缓存参数 或者
	if (empty($my_post_slim_list) || isset($query_vars['no_cache']))
	{

		//根据场景 修正查询参数
		$query_vars = fix_query_vars($query_vars);

		$results = get_posts($query_vars);

		//把查询到的文章数组转换成自定义文章类
		$my_post_slim_list = array_map(function ($element)
		{
			return new My_Post_Slim($element);
		}, $results);

		//暂时开启搜索缓存
		//只有在 没有设置搜索参数 或者 搜索参数短于100字 才会设置缓存
		if (!isset($query_vars['s']) || (strlen($query_vars['s']) <= 100))
		{
			File_Cache::set_cache_meta($cache_key,  File_Cache::DIR_POSTS, $my_post_slim_list);
		}
	}


	return $my_post_slim_list;
}

/**
 * 修正请求参数
 *
 * @param array<string, mixed> $query_vars
 * @return array<string, mixed>
 */
function fix_query_vars($query_vars)
{

	/*默认文章显示数量*/
	$query_vars['posts_per_page'] = get_option('posts_per_page');
	//$query_vars['orderby']        = 'modified'; //使用最后修改时间作为默认排序

	//如果有设置自定义排序
	if (array_key_exists(Post_Query::CUSTOM_ORDERBY, $query_vars) && $query_vars[Post_Query::CUSTOM_ORDERBY])
	{
		$query_vars['meta_key'] = $query_vars[Post_Query::CUSTOM_ORDERBY];
		$query_vars['orderby']  = 'meta_value_num';
	}

	//如果有设置自定义日期范围
	if (array_key_exists(Post_Query::CUSTOM_ORDER_DATA_RANGE, $query_vars) && $query_vars[Post_Query::CUSTOM_ORDER_DATA_RANGE])
	{
		$date_query               = [
			[
				'after' => $query_vars[Post_Query::CUSTOM_ORDER_DATA_RANGE] . ' month ago'
			]
		];
		$query_vars['date_query'] = $date_query;
	}


	//如果是主页. 排除魔法分类
	if (array_key_exists('page_type', $query_vars) && $query_vars['page_type'] == 'home')
	{
		$query_vars['cat'] = Category::NO_ADULT_CATEGORY;
	}

	//如果是作者页, 并且设置了内部搜索功能
	if (array_key_exists('page_type', $query_vars) && $query_vars['page_type'] == 'author' && array_key_exists(Post_Query::AUTHOR_INTERNAL_SEARCH, $query_vars) &&  $query_vars[Post_Query::AUTHOR_INTERNAL_SEARCH])
	{
		$query_vars['s'] = $query_vars[Post_Query::AUTHOR_INTERNAL_SEARCH];
	}

	//未登录用户 并且 不是首页
	if (!is_user_logged_in() && array_key_exists('page_type', $query_vars) && $query_vars['page_type'] != 'home')
	{
		//排除魔法区分类
		//获取现有cat查询参数, 追加或者 重新设置
		if ($query_vars['cat'])
		{
			$query_vars['cat'] .= ',-' . Category::ADULT_CATEGORY;
		}
		else
		{
			$query_vars['cat'] = Category::NO_ADULT_CATEGORY;
		}
	}

	return $query_vars;
}


/**
 * 获取默认主文章列表
 * 转换成自定义文章格式
 * @return My_Post_Slim[]
 */
function get_default_post_list()
{

	global $wp_query;

	$my_post_slim_list = [];

	if ($wp_query->posts)
	{
		//把查询到的文章数组转换成自定义文章类
		$my_post_slim_list = array_map(function ($element)
		{
			return new My_Post_Slim($element);
		}, $wp_query->posts);
	}

	return $my_post_slim_list;
}


/**
 * 获取用户收藏夹文章列表
 *
 * @param int $paged
 * @param string $search search value
 * @param int $cat 
 *
 * @return My_Post_Slim[]
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
		return new My_Post_Slim($element);
	}, $results);
}


/**
 * 获取关注用户的投稿列表
 *
 * @param int $count 数量
 *
 * @return My_Post_Hot[]
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
			return new My_Post_Hot($element);
		}, $results);
	}

	return $post_list;
}
