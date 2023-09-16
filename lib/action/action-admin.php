<?php

namespace mikuclub;


/**
 * 主题设置页 相关的钩子和动作
 */

//添加主题设置页
add_action('admin_menu', 'mikuclub\add_theme_config_page');

//添加自定义CSS
add_action('admin_enqueue_scripts', 'mikuclub\admin_custom_style');
