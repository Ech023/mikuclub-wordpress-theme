<?php

namespace mikuclub;


/**
 * 主题设置页 相关的钩子和动作
 */

//添加主题设置页
add_action('admin_menu', 'mikuclub\add_theme_config_page');

//添加自定义CSS
add_action('admin_enqueue_scripts', 'mikuclub\admin_custom_style');

//添加主题菜单
add_action('after_setup_theme', 'mikuclub\add_theme_nav_menus');

//在仪表盘主页添加自定义部件
add_action('wp_dashboard_setup', 'mikuclub\custom_dashboard_widgets');

//后台自定义CSS和JS
add_action('admin_enqueue_scripts', 'mikuclub\custom_admin_script');



/*添加后台用户列表自定义数据列*/
add_filter('manage_users_columns', [
    'mikuclub\User_Extra_Data_Column',
    'add_new_column_head'
]);
add_action('manage_users_custom_column', [
    'mikuclub\User_Extra_Data_Column',
    'add_new_column_body'
], 15, 3);

add_filter('manage_users_sortable_columns', [
    'mikuclub\User_Extra_Data_Column',
    'add_new_column_sortable'
]);

add_action('pre_get_users', [
    'mikuclub\User_Extra_Data_Column',
    'add_new_column_orderby_pre_get'
]);




//更新用户最后登陆时间
add_action('wp_login', ['mikuclub\User_Extra_Data_Column', 'update_user_last_login_time'], 10, 2);


//自定义用户个人资料信息
add_filter('user_contactmethods', 'mikuclub\user_custom_contact_fields');

//移除用户在后台的菜单选项
add_action('admin_menu', 'mikuclub\remove_menus');


//禁止普通用户进入后台特定页面
add_action('admin_print_scripts-index.php', 'mikuclub\prevent_user_access_wp_admin', 10, 0);
add_action('admin_print_scripts-post-new.php', 'mikuclub\prevent_user_access_wp_admin', 10, 0);
add_action('admin_print_scripts-edit.php', 'mikuclub\prevent_user_access_wp_admin', 10, 0);
add_action('admin_print_scripts-edit-comments.php', 'mikuclub\prevent_user_access_wp_admin', 10, 0);
add_action('admin_print_scripts-upload.php', 'mikuclub\prevent_user_access_wp_admin', 10, 0);
add_action('admin_print_scripts-media-new.php', 'mikuclub\prevent_user_access_wp_admin', 10, 0);
add_action('admin_print_scripts-profile.php', 'mikuclub\prevent_user_access_wp_admin', 10, 0);
