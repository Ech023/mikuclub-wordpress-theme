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
use WP_Term;

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
 * 获取置顶文章列表
 *
 *	@param int $cat_id
 * @return My_Post_Sticky_Model[]
 */
function get_sticky_post_list($cat_id)
{

    //缓存时间的键名
    $cache_meta_key = Option_Meta::STICKY_POSTS . '_' . ($cat_id ?: 0);

    //获取缓存
    $output = File_Cache::get_cache_meta_with_callback($cache_meta_key, File_Cache::DIR_POSTS . DIRECTORY_SEPARATOR . Option_Meta::STICKY_POSTS, Expired::EXP_2_HOURS, function () use ($cat_id)
    {

        //获取置顶文章id数组
        $sticky = get_option(Option_Meta::STICKY_POSTS) ?: [];

        $output = [];

        //优先获取手动置顶的文章
        $args = [
            Post_Query::POST__IN => $sticky,
            //维持 置顶id数组的排序
            Post_Query::CAT => $cat_id,
            //在有效期内获取
            Post_Query::DATE_QUERY => [
                [
                    'column' => 'post_modified',
                    'after' => '-' . Config::STICKY_POST_MANUAL_EXPIRED_DAY . ' days',
                ],
            ],
            Post_Query::POST_STATUS => Post_Status::PUBLISH,
            Post_Query::POST_TYPE => Post_Type::POST,
            Post_Query::POSTS_PER_PAGE => Config::STICKY_POST_FIRST_LIST_LENGTH,
            Post_Query::IGNORE_STICKY_POSTS => 1,
            Post_Query::ORDERBY => 'post__in',
        ];

        //查询文章数据
        $result = get_posts($args);

        $total_list_length = Config::STICKY_POST_FIRST_LIST_LENGTH + Config::STICKY_POST_SECONDARY_LIST_LENGTH;
        $missing_length = $total_list_length - count($result);
        //如果手动置顶的数量不够
        if ($missing_length > 0)
        {

            //继续获取高点赞帖子

            //创建一个时间数组用来累进的获取 最近的热高赞文章, 直到凑满需要的文章数量
            $array_additional_expired_day = [
                Config::STICKY_POST_TOP_LIKE_EXPIRED_DAY,
                Config::STICKY_POST_TOP_LIKE_EXPIRED_DAY * 2,
                Config::STICKY_POST_TOP_LIKE_EXPIRED_DAY * 4,
                Config::STICKY_POST_TOP_LIKE_EXPIRED_DAY * 8,
                Config::STICKY_POST_TOP_LIKE_EXPIRED_DAY * 16,
                Config::STICKY_POST_TOP_LIKE_EXPIRED_DAY * 32,
                Config::STICKY_POST_TOP_LIKE_EXPIRED_DAY * 64,
                Config::STICKY_POST_TOP_LIKE_EXPIRED_DAY * 128,
                Config::STICKY_POST_TOP_LIKE_EXPIRED_DAY * 256,
            ];

            foreach ($array_additional_expired_day as $additional_expired_day)
            {

                $args = [
                    //避免添加重复文章
                    Post_Query::POST__NOT_IN => array_column($result, 'ID'),
                    Post_Query::META_KEY => Post_Meta::POST_LIKE,
                    Post_Query::CAT => $cat_id,
                    Post_Query::DATE_QUERY => [
                        [
                            'column' => 'post_modified',
                            'after ' =>  '-' . $additional_expired_day . ' days',
                        ]
                    ],
                    Post_Query::POST_STATUS => Post_Status::PUBLISH,
                    Post_Query::POST_TYPE => Post_Type::POST,
                    Post_Query::IGNORE_STICKY_POSTS => 1,
                    Post_Query::POSTS_PER_PAGE => $missing_length,
                    Post_Query::ORDERBY => 'meta_value_num',
                    Post_Query::ORDER => 'DESC',
                ];

                $result = array_merge($result,  get_posts($args));

                //更新缺少的文章数量
                $missing_length = $total_list_length - count($result);
                //如果没有缺少 就结束循环
                if ($missing_length <= 0)
                {
                    break;
                }
            }
        }

        //把查询到的文章数组转换成自定义文章类
        $output = array_map(function ($element)
        {
            return new My_Post_Sticky_Model($element);
        }, $result);

        return $output;
    });

    return $output;
}



/**
 * 获取最新文章列表
 * @deprecated version
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
 * @param int $number 文章数量
 *
 * @return My_Post_Model[] 文章数组
 */
function get_hot_post_list($term_id, $meta_key, $number = Config::HOT_POST_LIST_LENGTH)
{

    $cache_meta_key =  File_Cache::HOT_POST_LIST . '_' . create_hash_string([
        $term_id,
        $meta_key,
        $number,
    ]);

    //获取缓存
    $output = File_Cache::get_cache_meta_with_callback($cache_meta_key, File_Cache::DIR_POSTS . DIRECTORY_SEPARATOR . File_Cache::HOT_POST_LIST, Expired::EXP_4_HOURS, function () use ($term_id, $meta_key, $number)
    {

        $result = [];
        $missing_length = $number;

        //创建一个时间数组用来累进的获取 最近的热点击文章, 直到凑满需要的文章数量
        $array_additional_expired_day = [
            Config::HOT_POST_EXPIRED_DAY,
            Config::HOT_POST_EXPIRED_DAY * 2,
            Config::HOT_POST_EXPIRED_DAY * 4,
            Config::HOT_POST_EXPIRED_DAY * 8,
            Config::HOT_POST_EXPIRED_DAY * 16,
            Config::HOT_POST_EXPIRED_DAY * 32,
            Config::HOT_POST_EXPIRED_DAY * 64,
            Config::HOT_POST_EXPIRED_DAY * 128,
            Config::HOT_POST_EXPIRED_DAY * 256,
        ];

        foreach ($array_additional_expired_day as $additional_expired_day)
        {
            $args = [
                //避免添加重复文章
                Post_Query::POST__NOT_IN => array_column($result, 'ID'),
                Post_Query::META_KEY => $meta_key,
                Post_Query::DATE_QUERY => [
                    [
                        'column' => 'post_modified',
                        'after' => '-' . $additional_expired_day . ' days',
                    ]
                ],
                Post_Query::POSTS_PER_PAGE => $missing_length,
                Post_Query::POST_STATUS => Post_Status::PUBLISH,
                Post_Query::POST_TYPE => Post_Type::POST,
                Post_Query::IGNORE_STICKY_POSTS => 1,
                Post_Query::ORDERBY => 'meta_value_num',
                Post_Query::ORDER => 'DESC',
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
            $result = array_merge($result, get_posts($args));

            //更新缺少的文章数量
            $missing_length = $number - count($result);
            //如果没有缺少 就结束循环
            if ($missing_length <= 0)
            {
                break;
            }
        }

        //把查询到的文章数组转换成自定义文章类
        $output = array_map(function ($element)
        {
            return new My_Post_Model($element);
        }, $result);

        return $output;
    });

    return $output;
}


/**
 * 获取相关文章列表
 *
 * @param int $post_id
 * @param int $number
 *
 * @return My_Post_Model[]
 */
function get_related_post_list($post_id, $number = Config::RELATED_POST_LIST_LENGTH)
{

    //创建缓存键值
    $cache_key = File_Cache::RELATED_POST_LIST . '_' . $post_id . '_' . $number;
    //获取缓存
    $output = File_Cache::get_cache_meta_with_callback($cache_key, File_Cache::DIR_POSTS . DIRECTORY_SEPARATOR . File_Cache::RELATED_POST_LIST, Expired::EXP_7_DAYS, function () use ($post_id, $number)
    {

        $args = [
            Post_Query::IGNORE_STICKY_POSTS => 1,
            Post_Query::ORDERBY => 'rand',
            Post_Query::POSTS_PER_PAGE => $number,
            Post_Query::POST__NOT_IN => [$post_id],
            Post_Query::POST_STATUS => Post_Status::PUBLISH,
            Post_Query::POST_TYPE => Post_Type::POST,
        ];

        //设置分类
        if (get_post_sub_cat_id($post_id))
        {
            $args[Post_Query::CAT] = get_post_sub_cat_id($post_id);
        }

        //获取标签
        $tags = get_the_tags();

        //如果有标签
        if ($tags)
        {

            //提取标签id数组
            $tag_ids = array_map(function (WP_Term $tag)
            {
                return $tag->term_id;
            }, $tags);
            //设置标签id过滤参数
            $args[Post_Query::TAG__IN] = $tag_ids;
        }

        //查询文章
        $results = get_posts($args);

        //如果结果数量不够 再获取同分类的文章
        if (count($results) < $number)
        {
            //移除标签限制
            unset($args[Post_Query::TAG__IN]);
            //修改需求数量
            $args[Post_Query::POSTS_PER_PAGE] = $number - count($results);
            //重新查询文章
            $results2 = get_posts($args);
            //合并结果
            $results = array_merge($results, $results2);
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


/**
 * 获取下载失效文章列表
 *
 * @param int|null $author
 * @param int|null $cat
 * @param int|null $paged
 * @param int $number
 * @return My_Post_Model[]
 */
function get_fail_down_post_list($author, $cat, $paged, $number = 20)
{


    $args = [
        Post_Query::IGNORE_STICKY_POSTS => 1,
        Post_Query::ORDERBY => 'meta_value_num',
        'meta_key'            => Post_Meta::POST_FAIL_TIME,
        'order'               => 'DESC',
        Post_Query::POSTS_PER_PAGE => $number,
        Post_Query::POST_STATUS => Post_Status::PUBLISH,
        Post_Query::POST_TYPE => Post_Type::POST,
    ];

    if ($author)
    {
        $args[Post_Query::AUTHOR] = $author;
    }
    if ($cat)
    {
        $args[Post_Query::CAT] = $cat;
    }
    if ($paged)
    {
        $args[Post_Query::PAGED] = $paged;
    }

    //查询文章
    $results = get_posts($args);

    //把查询到的文章数组转换成自定义文章类
    $output = array_map(function ($element)
    {
        return new My_Post_Model($element);
    }, $results);

    return $output;
}

/**
 * 获取用户收藏夹文章列表
 *
 * @param int|null $cat 
 * @param string|null $search search value
 * @param int|null $paged
 *
 * @return My_Post_Model[]
 */
function get_my_favorite_post_list($cat, $search, $paged)
{
    $output = [];

    $user_favorite = get_user_favorite();
    //如果收藏夹不是空
    if ($user_favorite)
    {
        $args = [
            Post_Query::IGNORE_STICKY_POSTS => 1,
            Post_Query::POST__IN => get_user_favorite(),
            Post_Query::ORDERBY => 'post__in',
            Post_Query::POSTS_PER_PAGE => get_option(Option_Meta::POSTS_PER_PAGE),

        ];

        if ($cat)
        {
            $args[Post_Query::CAT] = $cat;
        }

        //如果有需要搜索的内容, 添加到参数里
        if ($search)
        {
            $args[Post_Query::SEARCH] = $search;
        }

        if ($paged)
        {
            $args[Post_Query::PAGED] = $paged;
        }

        $results = get_posts($args);

        //把查询到的文章数组转换成自定义文章类
        $output = array_map(function ($element)
        {
            return new My_Post_Model($element);
        }, $results);
    }

    return $output;
}

/**
 * 获取关注用户的投稿列表
 *
 * @param int $number 数量
 *
 * @return My_Post_Model[]
 */
function get_my_followed_post_list($number)
{

    $output = [];

    //获取用户关注列表
    $user_followed = get_user_followed();

    //如果关注列表不是空
    if ($user_followed)
    {
        $args = [
            Post_Query::AUTHOR => implode(',', $user_followed),
            Post_Query::POST_STATUS => Post_Status::PUBLISH,
            Post_Query::POST_TYPE => Post_Type::POST,
            Post_Query::POSTS_PER_PAGE => $number,
            Post_Query::IGNORE_STICKY_POSTS => 1,
            Post_Query::ORDERBY => 'modified',
        ];

        $results = get_posts($args);

        //把查询到的文章数组转换成自定义文章类
        $output = array_map(function ($element)
        {
            return new My_Post_Model($element);
        }, $results);
    }

    return $output;
}
