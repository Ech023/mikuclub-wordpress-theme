<?php

namespace mikuclub;

use WP_Error;
use WP_REST_Request;

/**
 *  获取论坛回复列表API
 *
 * @param WP_REST_Request $data 
 * [
 * 	'paged' => 页数, 
 * ]
 *
 * @return My_Wpforo_Reply_Model[]|WP_Error
 */
function api_get_wpforo_reply_list($data)
{
    $result = execute_with_try_catch_wp_error(function () use ($data)
    {
        $paged = Input_Validator::get_array_value($data, 'paged', Input_Validator::TYPE_INT, false) ?: 1;

        $result = get_wpforo_reply_list($paged);

        return $result;
    });

    return $result;
}


/**
 * 注册自定义 api 接口
 * 
 * @return void
 */
function register_custom_forum_api()
{


    register_rest_route('utils/v2', '/forum_reply_list', [
        [
            'methods'             => 'GET',
            'callback'            => 'mikuclub\api_get_wpforo_reply_list',
            'permission_callback' => 'is_user_logged_in',
        ],
    ]);
}
