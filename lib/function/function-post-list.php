<?php

namespace mikuclub;

use Exception;
use mikuclub\constant\Category;
use mikuclub\constant\Config;
use mikuclub\constant\Expired;
use mikuclub\constant\Option_Meta;
use mikuclub\constant\Page_Type;
use mikuclub\constant\Post_Meta;
use mikuclub\Post_Query;
use mikuclub\constant\Post_Status;
use mikuclub\constant\Post_Type;
use WP_Post;
use WP_Query;



/**
 * 从wp query里获取默认的文章列表
 * 转换成自定义文章格式
 * @return My_Post_Model[]
 * 
 * @global $wp_query
 */
function get_wp_query_post_list()
{

    global $wp_query;

    $result = [];

    //如果存在文章数组
    if (is_array($wp_query->posts) && count($wp_query->posts) > 0)
    {
        //把查询到的文章数组转换成自定义文章类
        $result = array_map(function (WP_Post $post)
        {
            return new My_Post_Model($post);
        }, $wp_query->posts);
    }

    return $result;
}



/**
 * 获取文章列表
 *
 * @param array<string, mixed> $query_vars 查询参数
 * @return My_Post_Model[]
 */
function get_post_list($query_vars)
{
    $page_type = $query_vars[Post_Query::CUSTOM_PAGE_TYPE] ?? '';
    //如果存在页面类型, 就创建对应的缓存子文件夹
    $group = $page_type ? DIRECTORY_SEPARATOR . $page_type : '';


    //创建缓存键值
    $cache_key = File_Cache::POST_LIST . '_' . create_hash_string($query_vars);

    //获取缓存
    $array_post = File_Cache::get_cache_meta($cache_key, File_Cache::DIR_POSTS . $group, Expired::EXP_15_MINUTE);

    //如果不存在 或者 有禁用缓存参数 或者
    if (empty($array_post) || isset($query_vars[Post_Query::CUSTOM_NO_CACHE]))
    {

        //根据场景 修正查询参数
        $query_vars = set_post_list_query_vars($query_vars);

        $results = get_posts($query_vars);

        //把查询到的文章数组转换成自定义文章类
        $array_post = array_map(function ($element)
        {
            return new My_Post_Model($element);
        }, $results);


        //只有在 没有设置搜索参数 或者 搜索参数短于100字 才会设置缓存
        if (!isset($query_vars[Post_Query::SEARCH]) || (strlen($query_vars[Post_Query::SEARCH]) <= 100))
        {
            File_Cache::set_cache_meta($cache_key,  File_Cache::DIR_POSTS . $group, $array_post);
        }
    }


    return $array_post;
}



/**
 * 修正文章列表的请求参数
 *
 * @param array<string, mixed> $query_vars
 * @return array<string, mixed>
 */
function set_post_list_query_vars($query_vars)
{
    //获取页面参数
    $page_type = $query_vars[Post_Query::CUSTOM_PAGE_TYPE] ?? '';

    /*设置默认的文章显示数量*/
    $query_vars[Post_Query::POSTS_PER_PAGE] = get_option(Option_Meta::POSTS_PER_PAGE);
    //$query_vars['orderby']        = 'modified'; //使用最后修改时间作为默认排序


    //如果有设置自定义排序
    $custom_orderby = $query_vars[Post_Query::CUSTOM_ORDERBY] ?? '';
    if ($custom_orderby)
    {
        $query_vars[Post_Query::META_KEY] = $custom_orderby;
        $query_vars[Post_Query::ORDERBY]  = 'meta_value_num';
    }

    //如果有设置自定义日期范围
    $custom_order_data_range = $query_vars[Post_Query::CUSTOM_ORDER_DATA_RANGE] ?? '';
    //有设置自定义日期范围
    if ($custom_order_data_range)
    {
        $query_vars[Post_Query::DATE_QUERY] =  [
            [
                'after' => $custom_order_data_range . ' month ago'
            ]
        ];
    }


    //如果页面参数是 作者页
    if ($page_type === Page_Type::AUTHOR)
    {
        $custom_search = $query_vars[Post_Query::CUSTOM_SEARCH] ?? '';
        //如果有自定义搜索
        if ($custom_search)
        {
            //替换默认搜索
            $query_vars[Post_Query::SEARCH] = $custom_search;
        }
    }

    //如果页面参数是 主页
    if ($page_type === Page_Type::HOME)
    {
        $query_vars[Post_Query::CAT] = Category::NO_ADULT_CATEGORY;
    }
    //如果不是主页 并且用户未登陆 
    else if (!is_user_logged_in())
    {
        //获取现有cat查询参数
        $cat = $query_vars[Post_Query::CAT] ?? '';
        //如果不是空, 在结尾追加
        if ($cat)
        {
            $cat .= ',' . Category::NO_ADULT_CATEGORY;
        }
        //否则重新设置
        else
        {
            $cat = Category::NO_ADULT_CATEGORY;
        }

        $query_vars[Post_Query::CAT] = $cat;
    }

    return $query_vars;
}




/**
 * 获取置顶文章列表
 *
 *	@param int $cat_id
 * @return My_Post_Sticky_Model[]
 */
function get_sticky_post_list($cat_id)
{

    //置顶文章长度
    $posts_per_page = Config::STICKY_POST_LIST_LENGTH;
    //缓存时间的键名
    $cache_meta_key = Option_Meta::STICKY_POSTS . '_' . ($cat_id ?: 0);


    //获取缓存
    $output = File_Cache::get_cache_meta_with_callback($cache_meta_key, File_Cache::DIR_POSTS . DIRECTORY_SEPARATOR . Option_Meta::STICKY_POSTS, Expired::EXP_2_HOURS, function () use ($cat_id, $posts_per_page)
    {

        //获取置顶文章id数组
        $sticky = get_option(Option_Meta::STICKY_POSTS);

        $output = [];

        //如果置顶文章大于0
        if (is_array($sticky) && count($sticky) > 0)
        {

            $args = [
                Post_Query::POST__IN => $sticky,
                //维持 置顶id数组的排序
                Post_Query::ORDERBY => 'post__in',
                Post_Query::IGNORE_STICKY_POSTS => 1,
                Post_Query::CAT => $cat_id,
                Post_Query::POSTS_PER_PAGE => $posts_per_page,
                Post_Query::POST_STATUS => Post_Status::PUBLISH,
                Post_Query::POST_TYPE => Post_Type::POST,
            ];

            //查询文章数据
            $results = get_posts($args);

            //把查询到的文章数组转换成自定义文章类
            $output = array_map(function ($element)
            {
                return new My_Post_Sticky_Model($element);
            }, $results);
        }

        return $output;
    });

    return $output;
}



/**
 * 获取最新文章列表
 *
 * @param int $cat_id 分类id
 * @param int $number 数量
 *
 * @return My_Post_Model[] 自定义文章列表
 */
function get_recently_post_list($cat_id, $number)
{

    $output = File_Cache::get_cache_meta_with_callback(File_Cache::RECENTLY_POST_LIST . '_' . ($cat_id ?: 0), File_Cache::DIR_POSTS . DIRECTORY_SEPARATOR . File_Cache::RECENTLY_POST_LIST, Expired::EXP_2_HOURS, function () use ($cat_id, $number)
    {
        //文章查询参数
        $args = [
            Post_Query::CAT => $cat_id,
            Post_Query::POSTS_PER_PAGE => $number,
            Post_Query::POST_STATUS => Post_Status::PUBLISH,
            Post_Query::POST_TYPE => Post_Type::POST,
        ];

        // //如果存在 分类ID
        // if ($cat_id > 0)
        // {
        // 	//设置该分类过滤
        // 	$args['cat'] = $cat_id;
        // }
        // else
        // {
        // 	//排除成人区
        // 	$args['cat'] = Category::NO_ADULT_CATEGORY;
        // }

        $results = get_posts($args);

        //把查询到的文章数组转换成自定义文章类
        $output = array_map(function ($element)
        {
            return new My_Post_Model($element);
        }, $results);

        return $output;
    });

    return $output;
}



/**
 *
 * 获取热门文章列表 (根据文章元数据统计)
 *
 * @param int|null $term_id 分类ID 或者 标签ID
 * @param string $meta_key 要统计的元数据名称
 * @param int $range_day 统计周期
 * @param int $number 文章数量
 *
 * @return My_Post_Model[] 文章数组
 */
function get_hot_post_list($term_id, $meta_key, $range_day, $number = Config::HOT_POST_LIST_LENGTH)
{

    $cache_meta_key =  File_Cache::HOT_POST_LIST . '_' . create_hash_string([
        $term_id,
        $meta_key,
        $range_day,
        $number,
    ]);

    //获取缓存
    $output = File_Cache::get_cache_meta_with_callback($cache_meta_key, File_Cache::DIR_POSTS . DIRECTORY_SEPARATOR . File_Cache::HOT_POST_LIST, Expired::EXP_2_HOURS, function ($term_id, $meta_key, $range_day, $number)
    {
        //当前时间
        $now = time();
        //统计的周期长度 (秒数)
        $time_range = Expired::EXP_1_DAY * $range_day;
        //开始统计的周期
        $after_time = $now - $time_range;

        $args = [
            Post_Query::META_KEY => $meta_key,
            Post_Query::ORDERBY => 'meta_value_num',
            Post_Query::ORDER => 'DESC',
            Post_Query::DATE_QUERY => [
                [
                    'after ' => [
                        'year'  => date('Y', $after_time),
                        'month' => date('n', $after_time),
                        'day'   => date('j', $after_time),
                    ],
                ]
            ],
            Post_Query::POSTS_PER_PAGE => $number,
            Post_Query::POST_STATUS => Post_Status::PUBLISH,
            Post_Query::POST_TYPE => Post_Type::POST,
        ];

        //如果是标签页
        if (is_tag())
        {
            //使用tag id参数
            $args[Post_Query::TAG_ID] = $term_id;
        }
        //其他情况
        else
        {
            //使用cat id 参数
            $args[Post_Query::CAT] = $term_id;
        }

        //查询文章
        $results = get_posts($args);

        //如果查询到的文章数量低于 列表需要的长度  就加长统计周期, 重新获取, 最多重试3次
        for ($i = 2; $i < 5 && count($results) < $number; $i++)
        {
            //翻倍统计周期
            $after_time = $now - $time_range * $i;
            //重设开始统计的日期位置
            $args[Post_Query::DATE_QUERY] = [
                [
                    'after ' => [
                        'year'  => date('Y', $after_time),
                        'month' => date('n', $after_time),
                        'day'   => date('j', $after_time),
                    ],
                ]
            ];
            //重新获取文章
            $results = get_posts($args);
        }

        //把查询到的文章数组转换成自定义文章类
        $output = array_map(function ($element)
        {
            return new My_Post_Model($element);
        }, $results);

        return $output;
    });

    return $output;
}
