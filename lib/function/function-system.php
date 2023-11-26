<?php

namespace mikuclub;

use mikuclub\constant\Expired;

/**
 *  网站系统相关函数
 */



/**
 * 自定义表情符号和对应的图片
 *
 * @return void
 */
function init_new_smilies()
{
	//读取全局变量
	global $wpsmiliestrans;
	//表情符号与图片的对应关系(可自定义修改)
	$wpsmiliestrans = [
		':mrgreen:' => 'icon_mrgreen.gif',
		':neutral:' => 'icon_neutral.gif',
		':twisted:' => 'icon_twisted.gif',
		':arrow:'   => 'icon_arrow.gif',
		':shock:'   => 'icon_eek.gif',
		':smile:'   => 'icon_smile.gif',
		':???:'     => 'icon_confused.gif',
		':cool:'    => 'icon_cool.gif',
		':evil:'    => 'icon_evil.gif',
		':grin:'    => 'icon_biggrin.gif',
		':idea:'    => 'icon_idea.gif',
		':oops:'    => 'icon_redface.gif',
		':razz:'    => 'icon_razz.gif',
		':roll:'    => 'icon_rolleyes.gif',
		':wink:'    => 'icon_wink.gif',
		':cry:'     => 'icon_cry.gif',
		':eek:'     => 'icon_surprised.gif',
		':lol:'     => 'icon_lol.gif',
		':mad:'     => 'icon_mad.gif',
		':sad:'     => 'icon_sad.gif',
		'8-)'       => 'icon_01.gif',
		'8-O'       => 'icon_02.gif',
		':-('       => 'icon_03.gif',
		':-)'       => 'icon_04.gif',
		':-?'       => 'icon_05.gif',
		':-D'       => 'icon_06.gif',
		':-P'       => 'icon_07.gif',
		':-o'       => 'icon_08.gif',
		':-x'       => 'icon_09.gif',
		':-|'       => 'icon_10.gif',
		';-)'       => 'icon_11.gif',
		'8O'        => 'icon_eek.gif',
		':('        => 'icon_sad.gif',
		':)'        => 'icon_smile.gif',
		':?'        => 'icon_confused.gif',
		':D'        => 'icon_biggrin.gif',
		':P'        => 'icon_razz.gif',
		':o'        => 'icon_surprised.gif',
		':x'        => 'icon_mad.gif',
		':|'        => 'icon_neutral.gif',
		';)'        => 'icon_wink.gif',
		':!:'       => 'icon_exclaim.gif',
		':?:'       => 'icon_question.gif',
	];
}





/**
 * 阻止站内文章Pingback
 *
 * @param string[] $links
 * @return void
 */
function disable_self_ping(&$links)
{
	$home = get_home_url();
	foreach ($links as $l => $link)
	{
		if (0 === strpos($link, $home))
		{
			unset($links[$l]);
		}
	}
}


/**
 * 禁用 WordPress 中的 XML-RPC Pingback 功能
 * 
 * @param array<string, mixed> $methods
 * @return array<string, mixed>
 */
function disable_xmlrpc_pingback_ping($methods)
{
	unset($methods['pingback.ping']);

	return $methods;
}

/**
 * 关闭 RSS Feeds.
 *
 * @return void
 */
function disable_feed()
{
	wp_die('No feed available');
}


/**
 * 修改前台富文本编辑器的默认字体
 * tiny编辑器默认字体
 *
 * @param array<string,mixed> $in
 * @return array<string,mixed>
 **/
function set_mce_editor_font_family($in)
{

	$in['content_style'] = ".mce-content-body {font-family:'微软雅黑',Arial;} !important";

	return $in;
}



/**
 * 上传文件自动重命名
 *
 * @param string $filename
 * @return string 重新命名后的文件
 */
function rename_after_upload_file($filename)
{

	$info = pathinfo($filename);
	//获取后缀名
	$ext = $info['extension'] ??  '';

	//根据当前时间重命名文件
	$filename = date("Y-m-d_H-i-s") . '_' . mt_rand(0, 999);

	//如果后缀名存在
	if ($ext)
	{
		$filename .=  '.' . $ext;
	}

	return $filename;
}


/**
 *  移除不常用的图片尺寸, 节省硬盘空间
 *
 * @param array<string, mixed> $sizes
 * @return array<string, mixed>
 */
function disable_unused_image_sizes($sizes)
{

	unset($sizes['medium_large']); // disable 768px size images
	unset($sizes['1536x1536']); // disable 2x medium-large size
	unset($sizes['2048x2048']); // disable 2x large size

	return $sizes;
}



/**
 * 禁止用户上传GIF
 * 
 * @param array<string, mixed> $existing_mimes
 * @return array<string, mixed>
 */
function disable_upload_mimes($existing_mimes = array())
{
	unset($existing_mimes['gif']);
	return $existing_mimes;
}



/**
 * 关闭投稿管理页面的REST API 缓存
 * @param bool $skip
 * @return bool
 */
function disable_wp_rest_cache_on_post_manage_page($skip)
{
	//如果存在作者id 和 文章状态参数 关闭缓存
	if (isset($_REQUEST['author']) && isset($_REQUEST['status']))
	{
		$skip = true;
	}
	return $skip;
}


/**
 * 通过API上传图片的时候 添加自定义元数据
 *
 * @param int $post_id
 * @return void
 **/
function add_meta_data_on_attachment($post_id)
{
	$meta_key = Input_Validator::get_request_value('meta_key', Input_Validator::TYPE_STRING);
	$meta_value = Input_Validator::get_request_value('meta_value', Input_Validator::TYPE_STRING);

	if ($meta_key && $meta_value)
	{
		//添加元数据到对应的附件里
		update_post_meta($post_id, $meta_key, $meta_value);
	}
}



/**
 * 获取网站发布的文章总数
 * @return int
 */
function get_site_post_count()
{
	//获取缓存
	$count = File_Cache::get_cache_meta_with_callback(
		File_Cache::SITE_POST_COUNT,
		'',
		Expired::EXP_7_DAYS,
		function ()
		{
			//重新计算
			$count = wp_count_posts()->publish;
			return intval($count);
		}
	);

	return intval($count);
}

/**
 * 获取网站评论总数
 * @return int
 */
function get_site_comment_count()
{

	//从内存中获取
	$count = File_Cache::get_cache_meta_with_callback(
		File_Cache::SITE_COMMENT_COUNT,
		'',
		Expired::EXP_7_DAYS,
		function ()
		{
			//重新计算
			$count = wp_count_comments()->total_comments;
			return intval($count);
		}
	);

	return intval($count);
}

/**
 * 获取网站分类总数
 * @return int
 */
function get_site_category_count()
{

	//获取缓存
	$count = File_Cache::get_cache_meta_with_callback(
		File_Cache::SITE_CATEGORY_COUNT,
		'',
		Expired::EXP_7_DAYS,
		function ()
		{
			//重新计算
			$count = wp_count_terms('category');
			return intval($count);
		}
	);

	return intval($count);
}

/**
 * 获取网站标签总数
 * @return int
 */
function get_site_tag_count()
{

	//获取缓存
	$count = File_Cache::get_cache_meta_with_callback(
		File_Cache::SITE_TAG_COUNT,
		'',
		Expired::EXP_7_DAYS,
		function ()
		{
			//重新计算
			$count = wp_count_terms('post_tag');
			return intval($count);
		}
	);

	return intval($count);
}

/**
 * 生成网站页面的标题
 *
 * @return string
 */
function create_site_title()
{
	$result =  get_option('blogname');

	$paged = intval(get_query_var('paged'));

	//如果是首页
	if (is_home())
	{
		//如果是第一页
		if ($paged > 1)
		{
			$result = "最新发布 第{$paged}页 | " . $result;
		}
		//如果不是第一页
		else
		{
			//网站名称 + 描述
			$result = $result . ' | ' . get_option('blogdescription');
		}
	}
	//如果不是首页
	else
	{

		//如果未登录 访问成人分类 和成人文章 输出404内容
		if (!is_user_logged_in() && is_adult_category())
		{
			$result = '页面不存在' . ' | ' . $result;
		}
		else
		{
			$title = wp_title('', false, '');
			//如果页数大于1
			if ($paged > 1)
			{
				$title .= " 第{$paged}页";
			}
			$result =  $title . ' | ' . $result;
		}
	}

	return $result;
}




/**
 * 把数值 转换成hash随机数
 *
 * @param array<mixed,mixed>|object $input
 * @return string
 */
function create_hash_string($input)
{
	//如果是对象就强制转换成数组
	if (is_object($input))
	{
		$input = get_object_vars($input);
	}

	//如果是数组
	if (is_array($input))
	{
		//按照键名重新排序
		ksort($input);
	}

	//转换成json字符串
	$value = md5(json_encode($input));

	return $value;
}


/**
 * 把函数echo输出转换成string返回
 *
 * @param string $function_name
 * @return string
 */
function echo_to_string($function_name)
{
	ob_start();
	call_user_func($function_name);
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}



/**
 * 以自定义的方式  获取 autoload option 数组 , 添加文件缓存系统, 避免重复请求
 *
 * @param array|null $alloptions
 * @param boolean $force_cache
 * @return void
 *//*
function get_custom_cached_alloptions($alloptions = null, $force_cache = false)
{
	
	global $wpdb;

	//用局部函数保存数据
	static $result = null;

	//获取缓存
	$meta_cache_key = 'autoload_options';


	//如果缓存无效
	if (!$result)
	{
		
	
		//读取文件缓存 60秒
		$result = File_Cache::get_cache_meta($meta_cache_key, '', 60);
		//如果不存在
		if (!$result)
		{

	
			$alloptions_db = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE autoload = 'yes'");
			if (!$alloptions_db)
			{
				$alloptions_db = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options");
			}


			$result = array();
			foreach ((array) $alloptions_db as $o)
			{
				$result[$o->option_name] = $o->option_value;
			}
			//保存文件缓存
			File_Cache::set_cache_meta($meta_cache_key, '', $result);
		}
	}

	return $result;
}*/
//add_filter('pre_wp_load_alloptions', 'mikuclub\get_custom_cached_alloptions', 10, 2);
