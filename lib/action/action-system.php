<?php

namespace mikuclub;


/**
 * 网站系统相关的钩子和动作
 */

//移除WordPress版本号
remove_action('wp_head', 'wp_generator');

//前后文、第一篇文章和主页链接
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');

//移除前后文、第一篇文章和主页链接
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'parent_post_rel_link');
remove_action('wp_head', 'start_post_rel_link');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');

//移除feed
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);

//禁用 pingbacks, enclosures, trackbacks
remove_action('do_pings', 'do_all_pings', 10);
//去掉 _encloseme 和 do_ping 操作。
remove_action('publish_post', '_publish_post_hook', 5);

//移除文章自动保存动作
remove_action('pre_post_update', 'wp_save_post_revision');


//移除WordPress原版Emoji前后台钩子
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_styles', 'print_emoji_styles');
remove_filter('the_content_feed', 'wp_staticize_emoji');
remove_filter('comment_text_rss', 'wp_staticize_emoji');





//隐藏 顶部工具栏 admin Bar
add_filter('show_admin_bar', '__return_false');
//移除登陆页面语言切换按钮
add_filter('login_display_language_dropdown', '__return_false');

//禁用缩放尺寸
add_filter('big_image_size_threshold', '__return_false');

//WordPress禁用XML-RPC接口服务
add_filter('xmlrpc_enabled', '__return_false');
//禁用 WordPress 中的 XML-RPC Pingback 功能
add_filter('xmlrpc_methods', 'mikuclub\disable_xmlrpc_pingback_ping');

//修改前台富文本编辑器的默认字体
add_filter('tiny_mce_before_init', 'mikuclub\set_mce_editor_font_family');


//替换默认的表情符号
add_action('init', 'mikuclub\init_new_smilies');

//阻止站内PingBack
add_action('pre_ping', 'mikuclub\disable_self_ping');



//添加meta描述
add_action('wp_head', 'mikuclub\print_site_meta_description');
//添加meta关键词
add_action('wp_head', 'mikuclub\print_site_meta_keywords');

/**
 * 移除WP文章的自动保存
 */
add_action(
	'wp_print_scripts',
	/**
	 * @return void
	 */
	function ()
	{
		wp_deregister_script('autosave');
	}
);



/**
 * 关闭mce默认表情
 */
add_filter(
	'tiny_mce_plugins',
	/**
	 *
	 * @param array<string, mixed>|null $plugins
	 * @return array<string, mixed>
	 */
	function ($plugins)
	{
		$result = [];
		if (is_array($plugins))
		{
			$result = array_diff($plugins, ['wpemoji']);
		}
		return $result;
	}
);

/**
 * 修改表情的默认路径
 */
add_filter('smilies_src', 'mikuclub\fix_new_smilies_domain', 10, 2);

/**
 * 禁用响应式图片属性srcset和sizes
 */
add_filter(
	'wp_calculate_image_srcset',
	/**
	 * @param int[] $sources
	 * @return bool
	 */
	function ($sources)
	{
		return false;
	}
);

//上传文件自动重命名
add_filter('sanitize_file_name', 'mikuclub\rename_after_upload_file');
//移除不常用的图片尺寸, 节省硬盘空间
add_filter('intermediate_image_sizes_advanced', 'mikuclub\disable_unused_image_sizes');
//禁止用户上传GIF
add_filter('upload_mimes', 'mikuclub\disable_upload_mimes');
//在APP的投稿管理页面关闭REST API 缓存
add_filter('wp_rest_cache/skip_caching', 'mikuclub\disable_wp_rest_cache_on_post_manage_page');

//关闭 RSS FEED功能
add_action('do_feed_rdf', 'mikuclub\disable_feed', 1);
add_action('do_feed_rss', 'mikuclub\disable_feed', 1);
add_action('do_feed_rss2', 'mikuclub\disable_feed', 1);
add_action('do_feed_atom', 'mikuclub\disable_feed', 1);
add_action('do_feed_rss2_comments', 'mikuclub\disable_feed', 1);
add_action('do_feed_atom_comments', 'mikuclub\disable_feed', 1);


//在上传附件的时候 添加元数据
add_action('add_attachment', 'mikuclub\add_meta_data_on_attachment');
