<?php

namespace mikuclub;


/**
 * 主题 相关的钩子和动作
 */

//加载样式和脚本
add_action('wp_enqueue_scripts', 'mikuclub\setup_front_end_external_css_and_script');
add_action('wp_enqueue_scripts', 'mikuclub\setup_front_end_css');
add_action('wp_enqueue_scripts', 'mikuclub\setup_front_end_script');
add_action('wp_enqueue_scripts', 'mikuclub\setup_front_end_script_variable');


//添加 自定义query变量
add_filter('query_vars', ['mikuclub\Post_Query', 'add_custom_query_vars']);