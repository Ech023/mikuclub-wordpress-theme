<?php

namespace mikuclub;

use mikuclub\constant\Topic_meta;

/**
 * woforo论坛显示附件图片预览
 *
 * @param string $content
 *
 * @return string
 */
function wpforo_default_attachment_image_embed($content)
{
	if (preg_match_all('|<a class=\"wpforo\-default\-attachment\" href\=\"([^\"\']+)\"[^><]*>.+?<\/a>|is', $content, $data, PREG_SET_ORDER))
	{
		foreach ($data as $array)
		{
			if (isset($array[1]))
			{
				$file = $array[1];
				$e    = strtolower(substr(strrchr($file, '.'), 1));

				if (in_array($e, ['jpg', 'jpeg', 'png', 'gif', 'webp']))
				{
					$filename = explode('/', $file);
					$filename = end($filename);
					$html     = '<a href="' . esc_url($file) . '" target="_blank"><img class="wpforo-default-image-attachment" src="' . esc_url($file) . '" alt="' . esc_attr($filename) . '" title="' . esc_attr($filename) . '" /></a>';
					$content  = str_replace($array[0], $html, $content);
				}
			}
		}
	}

	return $content;
}

add_filter('wpforo_content_after', 'mikuclub\wpforo_default_attachment_image_embed', 11);


/**
 * 添加附件文件上传文本说明
 *
 * @param int $forumid
 *//*
function wpforo_attach_file_suggestion($forumid = null)
{

	if (WPF()->perm->can_attach($forumid))
	{ ?>
		<div class="wpf-default-attachment-text my-2">
			<p class="my-2">外链图片使用方式: 复制粘贴图片的外链地址到文本框里即可</p>
			<p class="my-2">本地图片附件支持的格式 : JPG, JPEG, PNG</p>
		</div>
<?php
	}
}*/

//add_action('wpforo_topic_form_extra_fields_after', 'mikuclub\wpforo_attach_file_suggestion');
//add_action('wpforo_reply_form_extra_fields_after', 'mikuclub\wpforo_attach_file_suggestion');
//add_action('wpforo_portable_form_extra_fields_after', 'mikuclub\wpforo_attach_file_suggestion');

/**
 * 获取论坛通知统计数
 * @return int
 */
function get_user_forum_notification_count()
{

	$user_id = get_current_user_id();
	$count = 0;

	//必须拥有当前用户id
	if ($user_id)
	{

		//消息类型
		$itemtype = 'alert';
		//统计数量
		$row_count = 100;

		global $wpdb;
		$query = " SELECT COUNT(*)  FROM  mm_wpforo_activity WHERE itemtype = '{$itemtype}' AND userid = {$user_id} ORDER BY id DESC LIMIT 0 , {$row_count}";
		$count = $wpdb->get_var($query);

		//如果错误 重设为0
		if (!$count)
		{
			$count = 0;
		}
	}

	return $count;
}





/**
 * 获取最新发布的主题帖列表
 *
 * @param int $posts_per_page
 *
 * @return My_Wpforo_topic[]  主题帖列表
 */
function get_recent_forums_topic($posts_per_page = 8)
{

	global $wpdb;
	$query = " SELECT *  FROM  mm_wpforo_topics ORDER BY topicid DESC LIMIT 0 , {$posts_per_page}";
	$results = $wpdb->get_results($query);

	$topic_list = [];
	foreach ($results as $object)
	{
		//转换成自定义回复类
		$topic_list[] = new My_Wpforo_topic($object);
	}

	return $topic_list;
}


/**
 * 自定义 wpforo 文本编辑器的按钮, 添加自定义表情按钮
 *
 * @param array<string, mixed> $settings
 * @param string $editor
 * @return array<string, mixed>
 */
function wpforo_custom_editors($settings, $editor)
{

	if ($editor === 'post')
	{
		//$settings['tinymce']['toolbar1'] = 'fontsizeselect,bold,italic,underline,forecolor,bullist,numlist,alignleft,aligncenter,alignright,link,unlink,blockquote,pre,wpf_spoil,pastetext,source_code,emoticons';
		$settings['tinymce']['toolbar1'] = 'smiley,link,unlink,wpf_spoil,source_code,removeformat,undo,redo';

		//调用自定义表情按钮插件里的js文件
		$settings['external_plugins']['smiley']     =  get_home_url() . '/wp-content/plugins/tinymce-smiley-button/plugin.js?ver=1.00';

		$settings['editor_height']       = 100;
	}

	return $settings;
}

add_filter('wpforo_editor_settings', 'mikuclub\wpforo_custom_editors', 2, 2);

/**
 * 输出 wpforo 自定义表情按钮要用到的 js代码
 *
 * @return string
 */
function wpforo_custom_editor_smiley_js_code()
{

	global $wpsmiliestrans;

	$result = '';

	if (get_option('use_smilies'))
	{
		$keys = array_map('strlen', array_keys($wpsmiliestrans));
		array_multisort($keys, SORT_ASC, $wpsmiliestrans);
		$smilies = array_unique($wpsmiliestrans);
		$smileySettings = array(
			'smilies' => $smilies,
			'src_url' => apply_filters('smilies_src', includes_url('images/smilies/'), '', site_url())
		);
		$result .= '<script>window._smileySettings = ' . json_encode($smileySettings) . '</script>';
	}

	return $result;
}

/**
 * 在创建新主题的时候保存对应的附近图片数据
 *
 * @param array<string,mixed> $args
 * [
 * 	'body' => 内容,
 * 	'topicid' => 主题ID,
 * 	'forumid' => 论坛板块ID,
 * 	'first_postid' => 帖子ID,
 * ]
 * @param array<string,mixed> $forum
 * @return void
 */
function wpforo_custom_add_post($args, $forum)
{
	update_topic_attach_meta($args);
}
add_action('wpforo_after_add_topic', 'mikuclub\wpforo_custom_add_post', 10, 2);

/**
 * 在更新主题的时候保存对应的附近图片数据
 *
 * 
 * @param array<string,mixed> $a
 * [
 * 	'body' => 内容,
 * 	'topicid' => 主题ID,
 * 	'forumid' => 论坛板块ID,
 * 	'first_postid' => 帖子ID,
 * ]
 * @param array<string,mixed> $args
 * @param array<string,mixed> $forum
 * @return void
 */
function wpforo_custom_edit_post($a, $args, $forum)
{
	update_topic_attach_meta($a);
}
add_action('wpforo_after_edit_topic', 'mikuclub\wpforo_custom_edit_post', 10, 3);


/**
 * 更新主题的元数据来保存预览图信息
 *
 * @param array<string,mixed> $args
 * @return void
 */
function update_topic_attach_meta($args)
{

	if (isset($args['body']) && isset($args['topicid']) && isset($args['forumid']) && isset($args['first_postid']))
	{
		$content = $args['body'];
		$topicid = $args['topicid'];
		$forumid = $args['forumid'];
		$postid = $args['first_postid'];

		$array_attach_url = [];
		$array_attach_thumbnail_url = [];

		$matches = null;
		preg_match_all('#\[attach]([^\[\]]+?)\[/attach]#isu', $content, $matches);

		//遍历每个找到的附近图片ID
		foreach ($matches[1] as $id_attach)
		{

			//获取图片附件
			//@phpstan-ignore-next-line
			$attach = WPF_ATTACH()->get_attach($id_attach);
			//如果附件存在
			if ($attach)
			{
				//提取URL
				$attach_url  = preg_replace('#^https?\:#is', '', $attach['fileurl']);

				//把url转换成文件路径
				$filepath = wpforo_fix_upload_dir($attach_url);
				//如果原图存在
				if (file_exists($filepath))
				{
					//保存url到数组
					$array_attach_url[] = $attach_url;
				}

				//添加缩微图类型的前缀
				$attach_thumbnail_url = str_replace(basename($attach_url), 'thumbnail/' . basename($attach_url), $attach_url);
				//把url转换成文件路径
				$thumbnail_path = wpforo_fix_upload_dir($attach_thumbnail_url);
				//如果缩微图存在
				if (file_exists($thumbnail_path))
				{
					//保存url到数组
					$array_attach_thumbnail_url[] = $attach_thumbnail_url;
				}
			}
		}

		//新的wpforo postmeta
		$new_post_meta = [
			'postid'        => $postid,
			'forumid'       => $forumid,
			'topicid'       => $topicid,
			'status'        => 0,
			'private'       => 0,
			'is_first_post' => 1,
		];

		//如果有有效的图片地址
		if (count($array_attach_url) > 0)
		{

			$new_post_meta['metakey'] = Topic_meta::ARRAY_ATTACHMENT_SRC;
			$new_post_meta['metavalue'] = $array_attach_url;

			//检测是否已存在相关数据
			$exist_post_meta = WPF()->postmeta->exists($postid, Topic_meta::ARRAY_ATTACHMENT_SRC);

			//如果不存在
			if ($exist_post_meta === false)
			{
				//插入
				$id_postmeta = WPF()->postmeta->add($new_post_meta);
			}
			//如果已存在
			else
			{
				//更新
				$id_postmeta = WPF()->postmeta->edit($new_post_meta, [
					'postid'        => $postid,
					'metakey' => Topic_meta::ARRAY_ATTACHMENT_SRC,
				]);
			}
		}
		else
		{
			//删除对应的元数据
			WPF()->postmeta->delete([
				'postid'        => $postid,
				'metakey' => Topic_meta::ARRAY_ATTACHMENT_SRC,
			]);
		}


		//如果有有效的缩微图地址
		if (count($array_attach_thumbnail_url) > 0)
		{
			//插入
			$new_post_meta['metakey'] = Topic_meta::ARRAY_THUMBNAIL_SRC;
			$new_post_meta['metavalue'] = $array_attach_thumbnail_url;

			//检测是否已存在相关数据
			$exist_post_meta = WPF()->postmeta->exists($postid, Topic_meta::ARRAY_THUMBNAIL_SRC);

			//如果不存在
			if ($exist_post_meta === false)
			{
				//插入
				$id_postmeta = WPF()->postmeta->add($new_post_meta);
			}
			//如果已存在
			else
			{
				//更新
				$id_postmeta = WPF()->postmeta->edit($new_post_meta, [
					'postid'        => $postid,
					'metakey' => Topic_meta::ARRAY_THUMBNAIL_SRC,
				]);
			}
		}
		else
		{
			//删除对应的元数据
			WPF()->postmeta->delete([
				'postid'        => $postid,
				'metakey' => Topic_meta::ARRAY_THUMBNAIL_SRC,
			]);
		}
	}
}


/*

do_action( 'wpforo_after_add_topic', $args, $forum );
do_action( 'wpforo_after_edit_topic', $a, $args, $forum );
do_action( 'wpforo_after_delete_topic', $topic );
do_action( 'wpforo_after_move_topic', $topic, $forumid );
do_action( 'wpforo_after_merge_topic', $target, $current, $postids, $to_target_title, $append );
*/

/**
 * 获取主题的附近图片预览
 *
 * @param array<string,mixed> $thread
 * [
 * 	"topicid": "22",
 *  "forumid": "4",
 *  "first_postid": "22",
 * 	"last_post" : array['body']
 * ]
 * 
 * @return string
 */
function get_wpforo_post_excerpt_and_thumbnail_images($thread)
{

	//截取前300个文字
	$content_length = 300;
	//只显示前3张图
	$images_count = 1;


	$result = '<div class="row my-2">';

	$postid = $thread['first_postid'] ?? 0;

	$url = $thread['url'] ?? '';

	$last_post_content = $thread['last_post']['body'] ?? '';




	//移除所有html标签
	$last_post_content = wp_strip_all_tags(substr($last_post_content, 0, $content_length));
	//移除所有[]方括号的标签
	$last_post_content = preg_replace('#\[[^>]*]([^\[\]]+?)\[/[^>]*]#isu', '', $last_post_content);

	//添加最后一个帖子的回复内容
	$result .= <<<HTML
		<div class="col-12 mt-1 mb-2">
			<div class="text-truncate">
				{$last_post_content}
			</div>
		</div>
HTML;


	$result .= '<div class="col-12"><div class="d-flex overflow-hidden">';

	$array_topic_thumbnail_src = WPF()->postmeta->get_postmeta($postid, Topic_meta::ARRAY_THUMBNAIL_SRC, true);

	if (is_array($array_topic_thumbnail_src) && count($array_topic_thumbnail_src) > 0)
	{
		//截取前几个图
		$array_topic_thumbnail_src = array_slice($array_topic_thumbnail_src, 0, $images_count);

		$first_image = true;
		foreach ($array_topic_thumbnail_src as $topic_thumbnail_src)
		{
			//默认只在宽屏显示
			$display_class = 'd-none d-md-block';

			//第一张图永远显示
			if ($first_image)
			{
				$display_class = 'd-block';
				$first_image = false;
			}

			//幻灯片链接
			//<a href="{$topic_thumbnail_src}" data-gallery="#wpf-content-blueimp-gallery">

			$result .= <<<HTML
			<div class="me-1 {$display_class} ">
				
				<a href="{$url}">
					<img class="img-fluid" src="{$topic_thumbnail_src}" style="max-width: 250px; max-height: 140px"/>
				</a>
			</div>
HTML;
		}
	}






	$result .= '</div></div>';
	$result .= '</div>';

	return $result;
}

/**
 * 在wpforo获取参数的时候 触发过滤器, 用来禁用不必要的数据库请求
 *
 * @param mixed $value
 * @param string $option
 * @param mixed $default
 * @param bool $cache
 * @return mixed
 */
function add_filter_on_wpforo_get_option($value, $option, $default, $cache)
{
	static $wpforo_version_result = null;

	$result = $value;

	//如果参数是 版本号,  
	if ($option === 'wpforo_version')
	{
		if ($wpforo_version_result === null)
		{
			//获取当前网址
			$url_path = basename($_SERVER['REQUEST_URI']);
			//如果当前页面不是论坛页, 设置参数值为 false, 来避免在其他页面 运行不必要的wpforo函数
			if (stripos($url_path, 'forums') === false)
			{
				$wpforo_version_result = false;
			}
			else
			{
				$wpforo_version_result = $value;
			}

			$result = $wpforo_version_result;
		}
	}

	return $result;
}
//add_filter('wpforo_get_option', 'mikuclub\add_filter_on_wpforo_get_option', 10, 4);
