<?php

namespace mikuclub;


/**
 * 网站系统相关的钩子和动作
 */



 //移除文章自动保存动作
 remove_action('pre_post_update', 'wp_save_post_revision');



/**
 * 移除WP文章的自动保存
 *
 * @return void
 */
function disable_wp_post_autosave()
{
	wp_deregister_script('autosave');
}
 add_action('wp_print_scripts', 'mikuclub\disable_wp_post_autosave');