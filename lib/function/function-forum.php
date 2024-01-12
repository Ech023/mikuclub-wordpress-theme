<?php

namespace mikuclub;

use mikuclub\constant\Config;
use mikuclub\constant\Expired;
use mikuclub\constant\Topic_meta;

/**
 * 论坛相关的函数
 */



/**
 * woforo论坛显示附件图片预览
 *
 * @param string $content
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

		$settings['editor_height'] = 100;
	}

	return $settings;
}

/**
 * 如果是论坛页面, 输出 wpforo 自定义表情按钮要用到的 js代码
 *
 * @return string
 */
function wpforo_custom_editor_smiley_js_code()
{

	global $wpsmiliestrans;

	$result = '';

	//如果存在 is_wpforo_page 函数
	if (function_exists('is_wpforo_page'))
	{

		if (is_wpforo_page() && get_option('use_smilies'))
		{
			$keys = array_map('strlen', array_keys($wpsmiliestrans));
			array_multisort($keys, SORT_ASC, $wpsmiliestrans);
			$smilies = array_unique($wpsmiliestrans);

			$smileySettings = array(
				'smilies' => $smilies,
				'src_url' => apply_filters('smilies_src', includes_url('images/smilies/'), '', site_url())
			);

			$json_smiley_settings =  json_encode($smileySettings);

			$result .= <<<HTML
                <script>
                    window._smileySettings = {$json_smiley_settings}
                </script>
HTML;
		}
	}

	return $result;
}


/**
 * 获取论坛通知统计数
 * @return int
 * @global $wpdb
 */
function get_wpforo_notification_unread_count()
{
	global $wpdb;

	$result = 0;

	$user_id = get_current_user_id();

	if ($user_id)
	{

		$result = Session_Cache::get(Session_Cache::USER_FORUM_NOTIFICATION_UNREAD_COUNT);
		//如果缓存不存在
		if ($result === null)
		{

			//消息类型
			$type = 'new_reply';
			$itemtype = 'alert';


			$query = <<<SQL
                SELECT 
                    COUNT(*)  
                FROM  
                    mm_wpforo_activity 
                WHERE 
					type = '{$type}'
				AND
                    itemtype = '{$itemtype}' 
                AND 
                    userid = {$user_id} 

SQL;

			$result = intval($wpdb->get_var($query));
			//设置缓存
			Session_Cache::set(Session_Cache::USER_FORUM_NOTIFICATION_UNREAD_COUNT, $result);
		}
	}

	return intval($result);
}



/**
 * 获取最新发布的主题帖列表
 *
 * @param int $posts_per_page
 * @return My_Wpforo_Topic_Model[]  主题帖列表
 * 
 * @global $wpdb
 */
function get_recent_forum_topic_list($posts_per_page = Config::RECENT_FORUM_TOPIC_LIST_LENGTH)
{

	global $wpdb;

	//获取缓存
	$result = File_Cache::get_cache_meta_with_callback(
		File_Cache::RECENT_FORUM_TOPIC_LIST,
		File_Cache::DIR_FORUM,
		Expired::EXP_30_MINUTE,
		function () use ($wpdb, $posts_per_page)
		{
			$query = <<<SQL
				SELECT 
					topicid,
					forumid,
					first_postid,
					userid,
					title,
					slug,
					created,
					modified,
					last_post,
					posts,
					votes,
					answers,
					views,
					meta_key,
					meta_desc,
					type,
					solved,
					closed,
					has_attach,
					private,
					status,
					name,
					email,
					prefix,
					tags
				FROM  
					mm_wpforo_topics 
				ORDER BY 
					topicid DESC 
				LIMIT 0 , {$posts_per_page}
SQL;

			$query_results = $wpdb->get_results($query);

			//My_Wpforo_Topic_Model
			$result = array_map(function ($object)
			{
				return new My_Wpforo_Topic_Model($object);
			}, $query_results);

			return $result;
		}
	);

	return $result;
}




/**
 * 更新主题相关的 预览图元数据
 *
 * @param array<string,mixed> $args
 * [
 * 	'body' => 内容,
 * 	'topicid' => 主题ID,
 * 	'forumid' => 论坛板块ID,
 * 	'first_postid' => 帖子ID,
 * ]
 * @return void
 */
function update_topic_attach_meta($args)
{

	if (
		isset($args['body'])
		&&
		isset($args['topicid'])
		&&
		isset($args['forumid'])
		&&
		isset($args['first_postid'])
	)
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
			// @phpstan-ignore-next-line
			$attach = \WPF_ATTACH()->get_attach($id_attach);

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


/**
 * 获取主题帖子的图片预览 (在插件wpforo中使用)
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
	//只显示前第一张图
	$images_count = 1;


	$postid = $thread['first_postid'] ?? 0;

	$url = $thread['url'] ?? '';

	$last_post_content = $thread['last_post']['body'] ?? '';

	//移除所有html标签
	$last_post_content = wp_strip_all_tags(substr($last_post_content, 0, $content_length));
	//移除所有[]方括号的标签
	$last_post_content = preg_replace('#\[[^>]*]([^\[\]]+?)\[/[^>]*]#isu', '', $last_post_content);



	$thumbnail_html = '';

	$array_topic_thumbnail_src = WPF()->postmeta->get_postmeta($postid, Topic_meta::ARRAY_THUMBNAIL_SRC, true);

	if (is_array($array_topic_thumbnail_src) && count($array_topic_thumbnail_src) > 0)
	{
		//截取前几个图
		$array_topic_thumbnail_src = array_slice($array_topic_thumbnail_src, 0, $images_count);

		$first_image = true;
		foreach ($array_topic_thumbnail_src as $topic_thumbnail_src)
		{
			//第一张图永远显示, 后续图片只在宽屏显示
			$display_class = $first_image ? 'd-block' : 'd-none d-md-block';

			$first_image = false;

			//幻灯片链接
			//<a href="{$topic_thumbnail_src}" data-gallery="#wpf-content-blueimp-gallery">

			$thumbnail_html .= <<<HTML
				<div class="me-1 {$display_class} ">
					<a href="{$url}">
						<img class="img-fluid" src="{$topic_thumbnail_src}" style="max-width: 250px; max-height: 140px" />
					</a>
				</div>
HTML;
		}
	}

	//添加最后一个帖子的回复内容
	$result = <<<HTML
		<div class="row my-2">
			<div class="col-12 mt-1 mb-2">
				<div class="text-truncate">
					{$last_post_content}
				</div>
			</div>
			<div class="col-12">
				<div class="d-flex overflow-hidden">
					{$thumbnail_html}
				</div>
			</div>
		</div>
HTML;


	return $result;
}



/**
 * 在wpforo获取参数的时候 触发过滤器, 用来禁用不必要的数据库请求
 *
 * @deprecated 暂时未使用
 * 
 * @param mixed $value
 * @param string $option
 * @param mixed $default
 * @param bool $cache
 * @return mixed
 * 
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


/**
 * 修正论坛消息通知里的链接域名
 * @param array<int, string> $array_notification
 * @return array<int, string>
 */
function fix_wpforo_notification_list_link($array_notification)
{
	$array_notification = array_map(function ($notification_content)
	{
		return fix_site_domain_with_current_domain($notification_content);
	}, $array_notification);

	return $array_notification;
}

/**
 * 修正论坛帖子的域名
 *
 * @param array<string,mixed> $topic
 * @param string $var
 * @return array<string,mixed>
 */
function fix_wpforo_topic_link($topic, $var)
{
	if (is_array($topic))
	{
		if (isset($topic['url']))
		{
			$topic['url'] = fix_site_domain_with_current_domain($topic['url']);
		}
		if (isset($topic['full_url']))
		{
			$topic['full_url']  = fix_site_domain_with_current_domain($topic['full_url']);
		}
		if (isset($topic['short_url']))
		{
			$topic['short_url'] = fix_site_domain_with_current_domain($topic['short_url']);
		}
	}

	return $topic;
}

/**
 * 获取用户收到的论坛回复列表
 *
 * @param int $paged 当前页码
 * @param int $number_per_page 每页显示数量
 *
 * @return My_Wpforo_Reply_Model[] 私信列表
 * 
 * @global $wpdb
 */
function get_wpforo_reply_list($paged = 1, $number_per_page = Config::NUMBER_FORUM_REPLY_PER_PAGE)
{

	$result = [];

	$user_id = get_current_user_id();

	//必须拥有当前用户id
	if ($user_id)
	{
		$cache_key = implode('_', [
			File_Cache::USER_FORUM_RELY_LIST,
			$paged,
			$number_per_page
		]);
		$result = File_Cache::get_cache_meta_with_callback($cache_key, File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $user_id, Expired::EXP_15_MINUTE, function () use ($paged, $number_per_page, $user_id)
		{
			global $wpdb;

			//计算数据表列 的偏移值 来达到分页效果
			$offset = ($paged - 1) * $number_per_page;

			$query = <<<SQL
				(
					/* 直接回复用户创建的主题的 */
					SELECT 
						mm_wpforo_posts.postid,
						mm_wpforo_posts.parentid,
						mm_wpforo_posts.forumid,
						mm_wpforo_posts.topicid,
						mm_wpforo_posts.userid,
						mm_wpforo_posts.title,
						mm_wpforo_posts.body,
						mm_wpforo_posts.created,
						mm_wpforo_posts.modified,
						mm_wpforo_posts.likes,
						mm_wpforo_posts.votes,
						mm_wpforo_posts.is_answer,
						mm_wpforo_posts.is_first_post,
						mm_wpforo_posts.status,
						mm_wpforo_posts.name,
						mm_wpforo_posts.email,
						mm_wpforo_posts.private,
						mm_wpforo_posts.root
					FROM 
						mm_wpforo_posts
					WHERE
		
						topicid IN
						(
							/* 获取用户自己发的主题ID */
							SELECT 
								topicid
							FROM 
								mm_wpforo_topics 
							WHERE
								status = 0
							AND
								userid = {$user_id}
						)
					AND
						parentid = 0
					AND
						status = 0
					AND
						private = 0
					AND
						userid <> {$user_id}
				)
				UNION
				(
					/* 回复了用户发表的回复的 */
					SELECT 
						mm_wpforo_posts.postid,
						mm_wpforo_posts.parentid,
						mm_wpforo_posts.forumid,
						mm_wpforo_posts.topicid,
						mm_wpforo_posts.userid,
						mm_wpforo_posts.title,
						mm_wpforo_posts.body,
						mm_wpforo_posts.created,
						mm_wpforo_posts.modified,
						mm_wpforo_posts.likes,
						mm_wpforo_posts.votes,
						mm_wpforo_posts.is_answer,
						mm_wpforo_posts.is_first_post,
						mm_wpforo_posts.status,
						mm_wpforo_posts.name,
						mm_wpforo_posts.email,
						mm_wpforo_posts.private,
						mm_wpforo_posts.root
					FROM 
						mm_wpforo_posts
					WHERE
						parentid IN
						(
							/* 获取用户自己的回复帖子ID */
							SELECT
								postid 
							FROM
								mm_wpforo_posts 
							WHERE
								status = 0
							AND
								userid = 1
						)
					AND
						status = 0
					AND
						private = 0
					AND
						userid <> {$user_id}
				)
				ORDER BY 
					created DESC 
				LIMIT 
					{$offset} , {$number_per_page}
	
SQL;

			$query_results = $wpdb->get_results($query);

			//转换成 My_Private_Message_Model
			$result = array_map(function ($object)
			{
				$model = new My_Wpforo_Reply_Model($object);

				return $model;
			}, $query_results);

			// var_dump($result);

			return $result;
		});
	}

	return $result;
}
