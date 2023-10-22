<?php

namespace mikuclub;

/**
 * WORDPRESS主查询相关的钩子和动作
 */


//修改query主查询的变量
add_action('pre_get_posts', 'mikuclub\set_wp_query_custom_query_var');

//添加 自定义query变量支持
add_filter('query_vars', ['mikuclub\Post_Query', 'add_custom_query_vars']);
