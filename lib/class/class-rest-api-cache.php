<?php

namespace mikuclub;

use mikuclub\constant\Config;
use mikuclub\constant\Expired;

/**
 * Wordpress 官方API缓存系统
 */
class Rest_Api_Cache
{
    //缓存支持的请求方式
    const REQUEST_METHODS = [
        'get',
    ];


    const REST_API_ROOT = '/wp-json/wp/v2/';
    //缓存支持的PATH路径
    const POSTS = 'posts';
    const COMMENTS = 'comments';

    /**
     * 如果请求有效拦截API请求, 直接返回缓存, 如果无效, 不做任何事情
     *
     * @return void
     */
    public static function get_rest_api_request_catch()
    {
        //如果是无效/是不能缓存的REST API请求
        if (static::is_valid_rest_api_request_to_cache() === false)
        {
            return;
        }

        $endpoint = static::get_endpoint_by_request_uri();
        $cache_result = null;
        switch ($endpoint)
        {
            case static::POSTS:
                $cache_result = static::get_rest_api_posts_cache();
                break;
            case static::COMMENTS:
                $cache_result = static::get_rest_api_comments_cache();
                break;
        }

        //如果成功获取到缓存
        if($cache_result){




        }

        // // Catch the headers after serving.
		// add_filter( 'rest_pre_serve_request', [ $this, 'save_cache_headers' ], 9999, 4 );

		// // Catch the result after serving.
		// add_filter( 'rest_pre_echo_response', [ $this, 'save_cache' ], 1000, 3 );
    }


    /**
     * 获取文章列表的缓存
     *
     * @return object[]|null
     */
    protected static function get_rest_api_posts_cache()
    {
        $result = null;

        //不能包含 author, 并且请求参数不能是空
        if ($_REQUEST && !isset($_REQUEST['author']))
        {
            $cache_key = File_Cache::WP_REST_POSTS . '_' . create_hash_string($_REQUEST);
            $result = File_Cache::get_cache_meta($cache_key, File_Cache::DIR_WP_REST_POSTS, Expired::EXP_15_MINUTE);
        }

        return $result;
    }

    /**
     * 获取评论列表的缓存
     *
     * @return object[]|null
     */
    protected static function get_rest_api_comments_cache()
    {
        $result = null;

        $post_id = $_REQUEST['post'] ?? 0;
        //请求参数不能是空 必须包含 post_id 
        if ($_REQUEST && $post_id)
        {
            $cache_key = File_Cache::WP_REST_COMMENTS . '_' . create_hash_string($_REQUEST);
            $result = File_Cache::get_cache_meta($cache_key, File_Cache::DIR_WP_REST_COMMENTS . DIRECTORY_SEPARATOR . File_Cache::WP_REST_COMMENTS, Expired::EXP_1_DAY);
        }

        return $result;
    }


    /**
     * 判断是否是需要缓存的REST API请求
     * 
     * @return boolean
     */
    protected static function is_valid_rest_api_request_to_cache()
    {
        $result = true;

        //如果是不支持请求方式
        if (!in_array($_SERVER['REQUEST_METHOD'], static::REQUEST_METHODS, true))
        {
            $result = false;
        }
        // //如果没有匹配到任何 接口
        // else if (empty(static::get_endpoint_by_request_uri()))
        // {
        //     $result = false;
        // }


        return $result;
    }

    /**
     * 通过解析请求的URL来判断对应的ENDPOINT
     *
     * @return string
     */
    protected static function get_endpoint_by_request_uri()
    {
        $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        //检测路径是否是 支持REST API的官方接口
        $result = '';
        foreach ([
            static::POSTS,
            static::COMMENTS
        ] as $endpoint)
        {
            if (strpos($request_path, static::REST_API_ROOT . $endpoint) !== false)
            {
                $result = $endpoint;
                break;
            }
        }

        return $result;
    }
}
