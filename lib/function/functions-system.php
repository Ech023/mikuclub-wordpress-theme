<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Expired;
use WP_User;

/**
 *  网站系统相关函数
 */

/**
 * 解义输出 网站选项值
 *
 * @param string $option_name 键名
 *
 * @return string|bool 键值, 如果未找到则返回false
 */
function dopt($option_name)
{
	$result = false;
	//如果 键名 存在
	if ($option_name)
	{
		$result = get_option($option_name);
		//如果键值 是 字符串 进行额外反引用处理
		if (is_string($result) /*|| is_numeric($option) || is_bool($option)*/)
		{
			$result = stripslashes($result);
		}
		
	}


	return $result;
}


/**
 * 去除冗余代码
 * 
 * @return void
 */
function action_after_setup_theme()
{
	//去除头部冗余代码
	remove_action('wp_head', 'feed_links_extra', 3);
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'index_rel_link');
	remove_action('wp_head', 'wp_generator');

	//禁用 pingbacks, enclosures, trackbacks
	remove_action('do_pings', 'do_all_pings', 10);
	//去掉 _encloseme 和 do_ping 操作。
	remove_action('publish_post', '_publish_post_hook', 5);

	//自定义封面
	//add_theme_support( 'custom-background' );
	//缩略图设置
	//add_theme_support( 'post-thumbnails' );

	//隐藏 顶部工具栏 admin Bar
	add_filter('show_admin_bar', '__return_false');
	//关闭XML-RPC
	add_filter('xmlrpc_enabled', '__return_false');


	/**
	 * 生成页面关键字 给搜索引擎使用
	 *
	 * @return void
	 */
	function print_keywords()
	{
		global $s, $post;
		$keywords = '';
		if (is_single())
		{

			//如果不是魔法区 并且是登陆用户
			if (!is_adult_category() || is_user_logged_in())
			{

				$post_tags = get_the_tags($post->ID);
				if ($post_tags)
				{
					foreach ($post_tags as $tag)
					{
						$keywords .= $tag->name . ', ';
					}
				}
				$post_categories = get_the_category($post->ID);
				foreach ($post_categories as $category)
				{
					$keywords .= $category->cat_name . ', ';
				}
				$keywords = substr_replace($keywords, '', -2);
			}
		}
		elseif (is_home())
		{
			$keywords = dopt(Admin_Meta::SITE_KEYWORDS);
		}
		elseif (is_tag())
		{
			$keywords = single_tag_title('', false);
		}
		elseif (is_category())
		{
			$keywords = single_cat_title('', false);
		}
		elseif (is_search())
		{
			$keywords = esc_html($s);
		}
		else
		{
			$keywords = trim(wp_title('', false));
		}

		if ($keywords)
		{
			echo "<meta name=\"keywords\" content=\"$keywords\">\n";
		}
	}

	add_action('wp_head', 'mikuclub\print_keywords');


	/**
	 * 网站页面 给搜索引擎用的描述
	 * @return void
	 */
	function deel_description()
	{

		global $s, $post;
		$description = '';
		$blog_name   = get_bloginfo('name');

		if (is_singular())
		{

			$text        = $post->post_content;
			$description = trim(str_replace(array(
				"\r\n",
				"\r",
				"\n",
				"　",
				" "
			), " ", str_replace("\"", "'", strip_tags($text))));
			if (!($description))
			{
				$description = $blog_name . "-" . trim(wp_title('', false));
			}
		}
		elseif (is_home())
		{
			$description = dopt(Admin_Meta::SITE_DESCRIPTION); // 首頁要自己加
		}
		elseif (is_tag())
		{
			$description = $blog_name . "'" . single_tag_title('', false) . "'";
		}
		elseif (is_category())
		{
			$description = trim(strip_tags(category_description()));
		}
		elseif (is_archive())
		{
			$description = $blog_name . "'" . trim(wp_title('', false)) . "'";
		}
		elseif (is_search())
		{
			$description = $blog_name . ": '" . esc_html($s) . "' 的搜索結果";
		}
		else
		{

			$description = $blog_name . "'" . trim(wp_title('', false)) . "'";
		}

		//如果是魔法区并且用户未登陆
		if (is_adult_category() && !is_user_logged_in())
		{
			//清空描述
			$description = '';
		}

		$description = mb_substr($description, 0, 220, 'utf-8');

		echo '<meta name="description" content="' . $description . '">';
	}

	//生成页面描述
	add_action('wp_head', 'mikuclub\deel_description');

	/**
	 * 阻止站内文章Pingback
	 *
	 * @param string[] $links
	 * @return void
	 */
	function deel_noself_ping(&$links)
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
	//阻止站内PingBack
	add_action('pre_ping', 'mikuclub\deel_noself_ping');

	/**
	 * @param array<string, mixed> $methods
	 * @return array<string, mixed>
	 */
	function remove_xmlrpc_pingback_ping($methods)
	{
		unset($methods['pingback.ping']);

		return $methods;
	}
	add_filter('xmlrpc_methods', 'mikuclub\remove_xmlrpc_pingback_ping');


	//定义 网站菜单导航栏
	if (function_exists('register_nav_menus'))
	{
		register_nav_menus([
			'nav'           => __('网站导航'),
			'pagemenu'      => __('页面导航'),
			'top_left_menu' => __('顶部左菜单'),
			'bottom_menu'   => __('底部菜单'),
		]);
	}


	//移除登陆页面语言切换按钮
	add_filter('login_display_language_dropdown', '__return_false');
}

add_action('after_setup_theme', 'mikuclub\action_after_setup_theme');


//WordPress禁用XML-RPC接口服务
add_filter('xmlrpc_enabled', '__return_false');


/**
 * 替换原版默认表情
 * @return void
 */
function custom_emojis()
{

	//移除WordPress4.2版本更新所带来的Emoji前后台钩子同时挂上主题自带的表情路径
	remove_action('wp_head', 'print_emoji_detection_script', 7);
	remove_action('admin_print_scripts', 'print_emoji_detection_script');
	remove_action('wp_print_styles', 'print_emoji_styles');
	remove_action('admin_print_styles', 'print_emoji_styles');
	remove_filter('the_content_feed', 'wp_staticize_emoji');
	remove_filter('comment_text_rss', 'wp_staticize_emoji');
	remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	add_filter('tiny_mce_plugins', 'mikuclub\disable_emojis_tinymce');
	add_filter('smilies_src', 'mikuclub\custom_smilies_src', 10, 2);

	/**
	 * 默认表情修正
	 *
	 * @param array<string, mixed>|null $plugins
	 * @return array<string, mixed>
	 */
	function disable_emojis_tinymce($plugins)
	{
		if (is_array($plugins))
		{
			return array_diff($plugins, ['wpemoji']);
		}
		else
		{
			return array();
		}
	}

	//自定义表情地址
	/**
	 * Undocumented function
	 *
	 * @param string $old
	 * @param string $img
	 * @return string
	 */
	function custom_smilies_src($old, $img)
	{
		return get_bloginfo('template_directory') . '/img/smilies/' . $img;
	}



	init_new_smilies();
}
add_action('init', 'mikuclub\custom_emojis');

/**
 * 自定义表情对应图片
 *
 * @return void
 */
function init_new_smilies()
{
	global $wpsmiliestrans;
	//默认表情文本与表情图片的对应关系(可自定义修改)
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
 * 修改前台富文本编辑器的默认字体
 * tiny编辑器默认字体
 *
 * @param array<string,mixed> $in
 * @return array<string,mixed>
 **/
function editor_custom_font($in)
{

	$in['content_style'] = ".mce-content-body {font-family:'微软雅黑',Arial;} !important";

	return $in;
}

add_filter('tiny_mce_before_init', 'mikuclub\editor_custom_font');


/* 邮件功能管理开始*/
add_filter('wp_password_change_notification_email', '__return_false'); //关闭密码变更通知
add_filter('password_change_email', '__return_false'); //关闭密码修改用户邮件
add_filter('wp_new_user_notification_email_admin', '__return_false'); //关闭新用户注册站长邮件
add_filter('wp_new_user_notification_email', '__return_false'); //关闭新用户注册用户邮件
//add_filter( 'send_email_change_email', '__return_false' ); //关闭邮件变更通知

/**
 * 修改默认发信人名称
 *
 * @param mixed $email
 * @return mixed
 */
function deel_res_from_name($email)
{
	$wp_from_name = get_option('blogname');

	return $wp_from_name;
}

add_filter('wp_mail_from_name', 'mikuclub\deel_res_from_name');

/**
 * 自定义忘记密码邮件通知
 *
 * @param string $message 默认重置邮件文本
 * @param string $key 重置密钥
 *
 * @return bool
 */
function reset_password_message($message, $key)
{


	//通过邮箱获取用户
	if (strpos($_POST['user_login'], '@'))
	{
		$user = get_user_by('email', trim($_POST['user_login']));
	} //或通过用户名来获取用户
	else
	{

		$login = trim($_POST['user_login']);
		$user  = get_user_by('login', $login);
	}

	$user_login = $user->user_login;

	/*
	$msg = __( '嗨, 有人要求重设您在初音社帐号的密码：' ) . "\r\n\r\n";
	$msg .= __( '用户名：'.$user_login ) . "\r\n\r\n";
	$msg .= __( '若这不是您本人要求的，请忽略本邮件，一切如常。' ) . "\r\n\r\n";
	$msg .= __( '要重置您的密码，请打开下面的链接：' ) . "\r\n\r\n";
	//$msg .= '<a href="'.get_option('home' ).'/wp-login.php?action=rp&key='.$key.'&login='.rawurlencode($user_login).'" target="_blank">'.get_option('home' ).'/wp-login.php?action=rp&key='.$key.'&login='.rawurlencode($user_login).'</a>\r\n\r\n\r\n"';
	//$msg .= "www.mikuclub.org/wp-login.php?action=rp&key=$key&login=". rawurlencode($user_login)."\r\n";
	$msg .= network_site_url( 'wp-login.php?action=rp&key='.$key.'&login=' . rawurlencode( $user_login ) ) . "\r\n\r\n";
	$msg .= __( '提示: 如果链接显示失效 请手动复制下方的地址 然后复制到地址栏访问' ) . "\r\n";
	$msg .= __( get_site_url().'wp-login.php?action=rp&key='.$key.'&login=' . rawurlencode( $user_login ) ) . "\r\n\r\n\r\n";
	$msg .= __( '初音社' ) . "\r\n";
	$msg .= __( '联系邮箱: hexie2109@gmail.com' ) . "\r\n";

	return $msg;*/

	$url = get_site_url() . '/wp-login.php?action=rp&key=' . $key . '&login=' . rawurlencode($user_login);

	$mailcontent = <<< HTML

	<div>
		<h2>嗨, 有人要求重设您在初音社帐号的密码</h2>
		<hr/><br/>
		<h4>用户ID: {$user->user_login} , 用户昵称: {$user->display_name}</h4>
		<p>要重置您的密码，请打开下面的链接：</p>
		<br/>
		<p><a href="{$url}" title="密码重置">重置密码</a> </p>
		<br/>
		<p>或者复制下面地址到浏览器地址栏来访问</p>
		<br/>
		<p>{$url}</p>
		<br/>	
		<h4>若这不是您本人要求的，请忽略本邮件，一切如常。</h4>
		<br/>
		<h4>初音社 | 联系邮箱 hexie2109@gmail.com</h4>
		<br/>
		<br/>	
		<br/>
	
	</div>

HTML;


	$headers = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

	wp_mail($user->user_email, '[初音社] 密码重社', $mailcontent, $headers);

	//返回false 屏蔽默认的密码重置邮件
	return false;
}

add_filter('retrieve_password_message', 'mikuclub\reset_password_message', 10, 2);


/**
 * 移除谷歌字体
 */
if (!function_exists('remove_wp_open_sans') && !function_exists('remove_wp_open_sans2'))
{
	/**
	 * @return void
	 */
	function remove_wp_open_sans()
	{
		wp_deregister_style('open-sans');
		wp_register_style('open-sans', false);
	}

	add_action('admin_enqueue_scripts', 'mikuclub\remove_wp_open_sans');
	add_action('login_init', 'mikuclub\remove_wp_open_sans');

	/**
	 * 去除谷歌字体2
	 * @return void
	 */
	function remove_wp_open_sans2()
	{
		wp_deregister_style('open-sans');
		wp_register_style('open-sans', false);
		wp_enqueue_style('open-sans', '');
	}

	add_action('init', 'mikuclub\remove_wp_open_sans2');
}





/**
 * 在RSS自定义文章输出格式
 *
 * @param string $content
 *
 * @return string
 */
function print_post_for_rss($content)
{

	global $post;
	$thumbnail_src = get_thumbnail_src($post->ID); //获取缩略图
	$post_link     = get_permalink($post);

	$content = <<< HTML

	<div>
		<div>
			<img src="{$thumbnail_src}" alt="{$post->post_title}" />
		</div>
		<h3>
			<a href="{$post_link}" title="{$post->post_title}">{$post->post_title}</a>
		</h3>
	</div>

HTML;


	return $content;
}

add_filter('the_content_feed', 'mikuclub\print_post_for_rss');





//WordPress 后台用户列表显示注册时间
class RRHE
{

	/**
	 * Register the column - Registered
	 *
	 * @param array<string, mixed> $columns
	 * @return array<string, mixed>
	 */
	public static function registerdate($columns)
	{
		$columns['registerdate'] = __('注册时间', 'registerdate');

		return $columns;
	}


	/**
	 * Display the column content
	 * 
	 * @param string $value      Custom column output. Default empty.
	 * @param string $column_name Column name.
	 * @param int    $user_id     ID of the currently-listed user.
	 * @return string
	 * 
	 */
	public static function registerdate_columns($value, $column_name, $user_id)
	{
		if ('registerdate' != $column_name)
		{
			return $value;
		}
		$user         = get_userdata($user_id);
		$registerdate = get_date_from_gmt($user->user_registered);

		return $registerdate;
	}

	/**
	 * @param array<string, mixed> $columns
	 * @return array<string, mixed>
	 */
	public static function registerdate_column_sortable($columns)
	{
		$custom = [
			// meta column id => sortby value used in query
			'registerdate' => 'registered'
		];

		return wp_parse_args($custom, $columns);
	}

	/**
	 * @param array<string, mixed> $vars
	 * @return array<string, mixed>
	 */
	public static function registerdate_column_orderby($vars)
	{
		if (isset($vars['orderby']) && 'registerdate' == $vars['orderby'])
		{
			$vars = array_merge($vars, [
				'meta_key' => 'registerdate',
				'orderby'  => 'meta_value'
			]);
		}

		return $vars;
	}
}

// Actions
add_filter('manage_users_columns', [
	'mikuclub\RRHE',
	'registerdate'
]);
add_action('manage_users_custom_column', [
	'mikuclub\RRHE',
	'registerdate_columns'
], 15, 3);
add_filter('manage_users_sortable_columns', [
	'mikuclub\RRHE',
	'registerdate_column_sortable'
]);
add_filter('request', [
	'mikuclub\RRHE',
	'registerdate_column_orderby'
]);


/**
 * 更新用户最后一次登录等时间
 *
 * @param string  $user_login Username.
 * @param WP_User $user       WP_User object of the logged-in user.
 * @return void
 */
function update_user_last_login_time($user_login, $user)
{

	update_user_meta($user->ID, 'last_login', current_time('mysql'));
}

add_action('wp_login', 'mikuclub\update_user_last_login_time', 10, 2);


/**
 * 添加一个新栏目“上次登录”
 *
 * @param array<string, mixed> $columns
 * @return array<string, mixed>
 */
function add_last_login_column($columns)
{
	$columns['last_login'] = '上次登录';
	unset($columns['name']);

	return $columns;
}

add_filter('manage_users_columns', 'mikuclub\add_last_login_column');


/**
 * 显示登录时间到新增栏目
 * 
 * @param string $value      Custom column output. Default empty.
 * @param string $column_name Column name.
 * @param int    $user_id     ID of the currently-listed user.
 * @return string
 */
function add_last_login_column_value($value, $column_name, $user_id)
{

	$user = get_userdata($user_id);

	if ('last_login' == $column_name && $user->last_login)
	{

		return get_user_meta($user_id, 'last_login', true);
	}

	return $value;
}

add_action('manage_users_custom_column', 'mikuclub\add_last_login_column_value', 10, 3);


/**
 * 忘记密码页 自定义提示信息
 * 
 * @return void
 */
function custom_lostpassword_message()
{
	echo '
	<p class="my-2 text-muted small">
		如果显示邮件发送成功, 却没在邮箱里看到的话, 请检查下垃圾箱.
	</p>
	<p class="my-2 text-muted small">
		如果提交后提示错误的话, 请过5分钟再试试, 多次无效的情况, 可以手动发邮件到 mikuclub@qq.com 主题填下: 找回密码 内容注明 用户名或邮箱.
	</p>';
}

add_action('lostpassword_form', 'mikuclub\custom_lostpassword_message');


//WordPress 仪表盘显示待审核的文章列表
/**
 * 在仪表盘主页显示自定义文章列表
 * 
 * @return void
 */
function custom_dashboard_widgets()
{

	//必须是管理员 才能看到
	if (current_user_is_admin())
	{
		add_meta_box('pending_posts_dashboard_widget', '待审文章', 'mikuclub\pending_posts_dashboard_widget_function', 'dashboard', 'normal', 'core');
		add_meta_box('fail_down_posts_dashboard_widget', '失效文章', 'mikuclub\fail_down_posts_dashboard_widget_function', 'dashboard', 'normal', 'core');
	}
}

add_action('wp_dashboard_setup', 'mikuclub\custom_dashboard_widgets');


/**
 * 待审文章列表
 * @return void
 */
function pending_posts_dashboard_widget_function()
{

	$args = [
		'posts_per_page' => 20,
		'post_type'      => 'post',
		'post_status'    => 'pending',
	];

	$post_list = get_posts($args);

	//判断是否有待审文章
	if ($post_list)
	{

		$post_list_html = <<<HTML
        
        <div style="margin-bottom: 15px;">
            <span></span>
            <span style="float: right">
                <a href="javascript:void(0)" onclick="open_all_post()">打开所有</a>
            </span>
            
        </div>

HTML;

		//储存文章编辑地址 数组
		$array_post_edit_link = [];

		foreach ($post_list as $post)
		{

			$my_post = new My_Post_Slim($post);
			//获取文章编辑地址
			//如果是普通文章
			if ($post->post_type == 'post')
			{
				$edit_url = get_home_url() . '/edit?pid=' . $my_post->id;
			}
			//如果是其他类型  论坛主题, 回帖等
			else
			{
				$edit_url = $my_post->post_href;
			}
			//获取文章所属分类数组
			$cats     = get_the_category($my_post->id);
			$cat_list = ''; //输出所属所有分类名
			foreach ($cats as $cat)
			{
				$cat_list .= $cat->cat_name . ' , ';
			}

			$post_list_html .= <<<HTML

				<li class="list-group-item">
					<h4>
						<a href="{$edit_url}" target="_blank">{$my_post->post_title}</a>
					</h4>
					<div>
						作者: <a href="{$my_post->post_author->user_href}" target="_blank">{$my_post->post_author->display_name}</a> 分类:  {$cat_list}
					</div>
					<div>
						<span class="mx-2">投稿时间: {$my_post->post_date}</span>
						<span class="mx-2">更新时间: {$my_post->post_modified_date}</span>
					</div>
				</li>

HTML;
			//储存文章id
			$array_post_edit_link[] = $edit_url;
		}

		//转换为json格式
		$array_post_edit_link_json = json_encode($array_post_edit_link);

		$output = <<<HTML
        <div>
            <ul class="list-group">{$post_list_html}</ul>
            <script>
               function open_all_post(){
                   let array_post_edit_link = {$array_post_edit_link_json};
                   array_post_edit_link.forEach(function(edit_link){
                         setTimeout(function(){
                            window.open(edit_link,'_blank');
                        }, 500);
                   });

               }
            </script>
        </div>
HTML;
	}
	else
	{
		$output = '<div>目前没有待审文章</div>';
	}

	echo $output;
}

/**
 * 下载失效文章列表
 * @return void
 */
function fail_down_posts_dashboard_widget_function()
{

	$args = [
		'posts_per_page' => 10,
		'orderby'        => 'meta_value_num',
		'meta_key'       => 'fail_time',
	];

	$post_list = get_posts($args);

	//判断是否有失效文章
	if ($post_list)
	{

		$post_list_html = '';

		foreach ($post_list as $post)
		{

			$my_post = new My_Post_Slim($post);

			//获取文章所属分类数组
			$cats     = get_the_category($my_post->id);
			$cat_list = ''; //输出所属所有分类名
			foreach ($cats as $cat)
			{
				$cat_list .= $cat->cat_name . ' , ';
			}
			//失效次数
			$fail_time = get_post_fail_times($post->ID);

			$post_list_html .= <<<HTML

				<li class="list-group-item">
					<h4>
						<a href="{$my_post->post_href}" target="_blank">
							{$my_post->post_title} <span class="badge bg-danger">{$fail_time}</span>
						</a>
					</h4>
					<div>
						作者: <a href="{$my_post->post_author->user_href}" target="_blank">{$my_post->post_author->display_name}</a> 分类:  {$cat_list}
					</div>
					<div>
						<span class="mx-2">投稿时间: {$my_post->post_date}</span>
						<span class="mx-2">更新时间: {$my_post->post_modified_date}</span>
					</div>
				</li>

HTML;
		}

		$output = '<div>
								<h2 class="text-center">
									<a href="' . get_home_url() . '/fail_down_list" target="_blank">进入失效管理页面</a>
								</h2>
								<ul class="list-group">' . $post_list_html . '</ul>
							</div>';
	}
	else
	{

		$output = '<div>目前没有失效文章</div>';
	}

	echo $output;
}


/**
 * 上传图片自动重命名
 *
 * @param string $filename
 *
 * @return string 重新命名后的文件
 */
function make_filename_hash($filename)
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


	/*
	$image_type=[
	    'jpg',
        'jpeg',
        'png',
        'gif',
        'bmp',
    ];

	//检查文件格式是否属于图片
    // 通过文件类型 和 文件后缀名检查
	if ( in_array($ext, $image_type) ||
        strpos($filename, '.jpg') !== false ||
        strpos($filename, '.jpeg') !== false ||
        strpos($filename, '.png') !== false ||
        strpos($filename, '.gif') !== false ||
        strpos($filename, '.bmp') !== false
    ) {
	    //根据当前时间重命名文件
        $filename = date( "Y-m-d_H-i-s" ).'_' .mt_rand(0,999). '.' .$ext;
	}*/

	return $filename;
}

add_filter('sanitize_file_name', 'mikuclub\make_filename_hash', 10);


/**
 * 禁用响应式图片属性srcset和sizes
 *
 * @param int[] $sources
 *
 * @return bool
 */
function disable_srcset($sources)
{
	return false;
}

add_filter('wp_calculate_image_srcset', 'mikuclub\disable_srcset');

/**
 * 禁用缩放尺寸
 **/
add_filter('big_image_size_threshold', '__return_false');


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

add_filter('intermediate_image_sizes_advanced', 'mikuclub\disable_unused_image_sizes');



/**
 * 禁止用户上传GIF
 * 
 * @param array<string, mixed> $existing_mimes
 * @return array<string, mixed>
 */
function custom_upload_mimes($existing_mimes = array())
{

	unset($existing_mimes['gif']);

	return $existing_mimes;
}
add_filter('upload_mimes', 'mikuclub\custom_upload_mimes');


/**
 * 关闭投稿管理页面的REST API 缓存
 * @param bool $skip
 * @return bool
 */
function wprc_do_not_cache_user_post($skip)
{
	//如果存在作者id 和 文章状态参数 关闭缓存
	if (isset($_REQUEST['author']) && isset($_REQUEST['status']))
	{
		$skip = true;
	}
	return $skip;
}
add_filter('wp_rest_cache/skip_caching', 'mikuclub\wprc_do_not_cache_user_post', 10, 1);



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


/**
 * 获取网站发布的文章总数
 * @return int
 */
function site_posts_total_count()
{

	$cache_key = 'count_total_post';

	//从内存中获取
	$count = File_Cache::get_cache_meta($cache_key, '', Expired::EXP_7_DAYS);

	//如果缓存失效
	if (empty($count))
	{

		//重新计算
		$count = wp_count_posts()->publish;
		File_Cache::set_cache_meta($cache_key, '', $count);
	}

	return $count;
}

/**
 * 获取网站标签总数
 * @return int
 */
function site_tags_total_count()
{

	$cache_key = 'count_total_tag';

	//从内存中获取
	$count = File_Cache::get_cache_meta($cache_key, '', Expired::EXP_7_DAYS);

	//如果缓存失效
	if (empty($count))
	{

		//重新计算
		$count = wp_count_terms('post_tag');
		File_Cache::set_cache_meta($cache_key, '', $count);
	}

	return $count;
}

/**
 * 获取网站分类总数
 * @return int
 */
function site_categories_total_count()
{

	$cache_key = 'count_total_category';

	//从内存中获取
	$count = File_Cache::get_cache_meta($cache_key, '', Expired::EXP_7_DAYS);

	//如果缓存失效
	if (empty($count))
	{

		//重新计算
		$count = wp_count_terms('category');
		File_Cache::set_cache_meta($cache_key, '', $count);
	}

	return $count;
}

/**
 * 获取网站评论总数
 * @return int
 */
function site_comments_total_count()
{

	$cache_key = File_Cache::SITE_COMMENT_COUNT;

	//从内存中获取
	$count = File_Cache::get_cache_meta($cache_key, '', Expired::EXP_7_DAYS);

	//如果缓存失效
	if (empty($count))
	{

		//重新计算
		$count = wp_count_comments()->total_comments;
		File_Cache::set_cache_meta($cache_key, '', $count);
	}

	return $count;
}




/**
 * 设置cookie, 默认360天后过期
 *
 * @param string $key
 * @param mixed $value
 * @param int $expired 有效时间 默认数值  60 * 60 * 24 * 360 一年时间
 * @return void
 */
function set_my_cookie($key, $value, $expired = 31104000)
{

	//确保键值不是空的
	if ($value)
	{
		//设置cookie
		setcookie($key, json_encode($value), time() + $expired);
	}
}


/**
 * 删除数据库缓存
 *
 * @param string $meta_key 键名
 * @return void
 **/
function delete_transient_cache_meta($meta_key)
{

	delete_transient($meta_key);
}


/**
 * 检测变量是否存在 并且是 数字
 *
 * @param mixed $var
 *
 * @return bool
 */
function isset_numeric($var)
{

	if (isset($var) && is_numeric($var))
	{
		return true;
	}
	else
	{
		return false;
	}
}

/**
 * 检测变量是否存在 并且是 布尔值
 *
 * @param mixed $var
 *
 * @return bool
 */
function isset_boolean($var)
{

	$result = false;

	if (isset($var))
	{
		//如果不是 bool相关数值和字符串 则返回null
		$var = filter_var($var, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
		if (isset($var))
		{
			$result = true;
		}
	}

	return $result;
}

/**
 * 把变量值转换成布尔值
 *
 * @param mixed $var
 *
 * @return bool
 */
function parse_boolean($var)
{

	return filter_var($var, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
}

/**
 * 把数值 转换成hash随机数
 *
 * @param mixed $input
 * @return string
 */
function hash_xxh($input)
{

	//如果是数组和对象
	if (is_array($input) || is_object($input))
	{
		//转换成json字符串
		$value = json_encode($input);
	}
	else
	{
		$value = $input;
	}

	return hash('xxh3', $value);
}


/**
 * 从站点地图 wp-sitemap.xml 里移除 不需要的内容
 *
 * @param WP_Sitemaps_Provider $provider
 * @param string $name
 * @return void
 *//*
function remove_provider_from_sitemap($provider, $name)
{
	//移除用户列表
	if ('users' === $name)
	{
		return false;
	}

	return $provider;
}
add_filter('wp_sitemaps_add_provider', 'mikuclub\remove_provider_from_sitemap', 10, 2);*/


/**
 * 关闭 RSS Feeds.
 *
 * @return void
 */
function disable_feed() {
    wp_die('No feed available');
}
 
// Replace all feeds with the message above.
add_action( 'do_feed_rdf', 'mikuclub\disable_feed', 1 );
add_action( 'do_feed_rss', 'mikuclub\disable_feed', 1 );
add_action( 'do_feed_rss2', 'mikuclub\disable_feed', 1 );
add_action( 'do_feed_atom', 'mikuclub\disable_feed', 1 );
add_action( 'do_feed_rss2_comments', 'mikuclub\disable_feed', 1 );
add_action( 'do_feed_atom_comments', 'mikuclub\disable_feed', 1 );
// Remove links to feed from the header.
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'feed_links', 2 );