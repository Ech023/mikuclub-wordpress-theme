<?php

namespace mikuclub;

use mikuclub\constant\Config;
use mikuclub\constant\Expired;
use mikuclub\constant\Post_Meta;
use WP_Error;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Wordpress 官方API缓存系统
 */
class Rest_Api_Cache
{
    //缓存支持的请求方式
    const REQUEST_METHODS = [
        'GET',
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
    public static function get_rest_api_request_cache()
    {
     
        //如果是无效/是不能缓存的REST API请求方式
        if (static::is_valid_rest_api_request_to_cache() === false)
        {
            return;
        }
        //如果是不支持的接口
        $endpoint = static::get_endpoint_by_request_uri();
        if (empty($endpoint))
        {
            return;
        }



        $cache_key = static::get_cache_key($endpoint);
        $cache_directory = static::get_cache_directory($endpoint);
        $cache_expired = static::get_cache_expired($endpoint);

        $cache_result = null;
        //如果能正确生成缓存参数
        if ($cache_key && $cache_directory && $cache_expired)
        {
            $cache_result = File_Cache::get_cache_meta($cache_key, $cache_directory, $cache_expired);

            //如果成功获取到缓存
            if (is_null($cache_result) === false)
            {

                $result = wp_json_encode($cache_result);

                //如果JSON解析有错误
                if (JSON_ERROR_NONE !== json_last_error())
                {
                    //获取JSON错误信息
                    $json_error_message = json_last_error_msg();

                    status_header(500);
                    $json_error_obj = new WP_Error(
                        'rest_encode_error',
                        $json_error_message,
                        ['status' => 500]
                    );

                    $result = rest_convert_error_to_response($json_error_obj);
                    $result = wp_json_encode($result->data);
                }
                else
                {
                    header('Rest-Api-Cache: true');
                    header('Content-Type: application/json; charset=UTF-8');
                }


                echo $result;
                exit;
            }
            //如果没有缓存
            else
            {

                //挂载保存缓存的钩子
                // // Catch the headers after serving.
                // add_filter( 'rest_pre_serve_request', [ $this, 'save_cache_headers' ], 9999, 4 );

                // // Catch the result after serving.
                add_filter('rest_pre_echo_response', 'mikuclub\Rest_Api_Cache::set_rest_api_request_cache', 1000, 3);
            }
        }
    }

    /**
     * 保存API请求的结果
     *
     * @param array<string, mixed> $result Response data to send to the client.
     * @param WP_REST_Server $server Server instance.
     * @param WP_REST_Request $request Request used to generate the response.
     * @return array<string, mixed>
     */
    public static function set_rest_api_request_cache($result, $server, $request)
    {
        $endpoint = static::get_endpoint_by_request_uri();
        //如果是支持的接口
        if ($endpoint)
        {
            $cache_key = static::get_cache_key($endpoint);
            $cache_directory = static::get_cache_directory($endpoint);

            if ($cache_key && $cache_directory)
            {
                //创建缓存
                File_Cache::set_cache_meta($cache_key, $cache_directory, $result);
            }
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

        $request_method = $_SERVER['REQUEST_METHOD'] ?? '';
        $request_method =  strtoupper($request_method);

        //如果是不支持请求方式
        if (!in_array($request_method, static::REQUEST_METHODS, true))
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
        $result = '';

        $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        //检测路径是否是 支持REST API的官方接口
        if ($request_path)
        {
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
        }

        return $result;
    }

    /**
     * 根据接口名 来 缓存键名
     *
     * @param string $endpoint
     * @return string
     */
    protected static function get_cache_key($endpoint)
    {
        $result = '';

        if ($endpoint)
        {
            switch ($endpoint)
            {
                case static::POSTS:

                    //如果不存在 禁用缓存 和 搜索参数 或者 author参数, 或者 参数ID 不是 当前用户ID
                    if (!isset($_REQUEST[Post_Query::CUSTOM_NO_CACHE]) && !isset($_REQUEST['search'])  && (!isset($_REQUEST['author']) ||  intval($_REQUEST['author']) !== get_current_user_id()))
                    {
    
                        $result = File_Cache::WP_REST_POSTS;
                        $result .= '_' . create_hash_string($_REQUEST);
                    }
                    break;

                case static::COMMENTS:
                    // 确保拥有post参数
                    if (isset($_REQUEST['post']))
                    {
                        $result  = File_Cache::WP_REST_COMMENTS;
                        $result .= '_' . create_hash_string($_REQUEST);
                    }

                    break;
            }
        }

        return $result;
    }

    /**
     * 根据接口名 来 缓存文件夹路径
     *
     * @param string $endpoint
     * @return string
     */
    protected static function get_cache_directory($endpoint)
    {

        $result = '';

        if ($endpoint)
        {
            switch ($endpoint)
            {
                case static::POSTS:
                    $result = File_Cache::DIR_WP_REST_POSTS;
                    break;

                case static::COMMENTS:
                    $post_id = $_REQUEST['post'] ?? 0;
                    $result  = File_Cache::DIR_WP_REST_COMMENTS . DIRECTORY_SEPARATOR . $post_id;
                    break;
            }
        }

        return $result;
    }

    /**
     * 根据接口名 来获取 缓存有效时间
     *
     * @param string $endpoint
     * @return int
     */
    protected static function get_cache_expired($endpoint)
    {

        $result = 0;

        if ($endpoint)
        {
            switch ($endpoint)
            {
                case static::POSTS:
                    $result = Expired::EXP_15_MINUTE;
                    break;

                case static::COMMENTS:
                    $result = Expired::EXP_1_DAY;
                    break;
            }
        }

        return $result;
    }
}
