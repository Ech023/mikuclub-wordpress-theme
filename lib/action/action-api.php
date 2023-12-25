<?php

namespace mikuclub;


/**
 * API接口 相关的钩子和动作
 */

//app使用的接口
add_action('rest_api_init', 'mikuclub\register_custom_app_api');

//评论的接口
add_action('rest_api_init', 'mikuclub\register_custom_comment_api');

//私信系统的接口
add_action('rest_api_init', 'mikuclub\register_custom_private_message_api');

/* 文章列表接口*/
add_action('rest_api_init', 'mikuclub\register_custom_post_list_api');

/* 文章接口*/
add_action('rest_api_init', 'mikuclub\register_custom_post_api');

/* 用户接口 */
add_action('rest_api_init', 'mikuclub\register_custom_user_metadata');

/* util接口 */
add_action('rest_api_init', 'mikuclub\register_custom_api');

/* 论坛接口 */
add_action('rest_api_init', 'mikuclub\register_custom_forum_api');


//启动REST API官方接口缓存系统
add_filter('rest_pre_dispatch', 'mikuclub\Rest_Api_Cache::get_rest_api_request_cache', 10, 2);
