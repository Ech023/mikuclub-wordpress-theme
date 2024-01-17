<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Download_Link_Type;
use mikuclub\constant\Expired;
use mikuclub\constant\Post_Feedback_Rank;
use mikuclub\constant\Post_Meta;

/**
 * 输出文章页HTML内容
 *
 * @param int $post_id
 * @return string
 */
function print_post_content($post_id)
{
	$output = '';
	//如果不存在post id
	if (empty($post_id))
	{
		return $output;
	}

	//文章内容第一部分
	$post_content_part_1 = File_Cache::get_cache_meta_with_callback(File_Cache::POST_CONTENT_PART_1, File_Cache::DIR_POST . DIRECTORY_SEPARATOR . $post_id, Expired::EXP_7_DAYS, function () use ($post_id)
	{
		//获取文章主体
		// $post = get_post($post_id);

		$first_image_part = print_post_content_first_image($post_id);
		$post_description_part = print_post_content_description($post_id);

		$download_part = print_post_content_download($post_id);
		//$download_fast_link_part = print_post_content_download_fast_link($post_id);
		$video_part = print_post_content_video($post_id);

		$result = <<<HTML

			<div class="row">
				<div class="col-12 col-xl-6 order-0 ">
					<div class="first-image-part my-2 " id="first-image-part">
						{$first_image_part}
					</div>
				</div>
				<div class="col-12 col-xl-6 order-2 order-xl-1">
					
					<div class="download-part my-2" id="download-part">
						{$download_part}
					</div>
					<div class="video-part my-2" id="video-part">
						{$video_part}
					</div>
				</div>
				<div class="col-12 order-1  order-xl-2">
					<div class="content-part my-2 text-break" style="min-height: 100px">
						{$post_description_part}
					</div>
				</div>
			</div>

HTML;

		return $result;
	});

	//文章内容第二部分
	$post_content_part_2 = File_Cache::get_cache_meta_with_callback(File_Cache::POST_CONTENT_PART_2, File_Cache::DIR_POST . DIRECTORY_SEPARATOR . $post_id, Expired::EXP_7_DAYS, function () use ($post_id)
	{

		$preview_images_part = print_post_content_previews_image($post_id);

		$result = <<<HTML
			
			<div class="preview-images-part my-2" id="preview-images-part">
				{$preview_images_part}
			</div>
HTML;

		return $result;
	});

	$post_functional_button_part = print_post_content_functional_button($post_id);

	$pc_adsense = '';
	//PC端 文章页 - 正文中间
	if (get_theme_option(Admin_Meta::POST_CONTENT_ADSENSE_PC_ENABLE))
	{
		$pc_adsense = '<div class="pop-banner text-center my-2 d-none d-md-block">' . get_theme_option(Admin_Meta::POST_CONTENT_ADSENSE_PC) . '</div>';
	}
	$mobile_adsense = '';
	//手机端 文章页 - 正文中间
	if (get_theme_option(Admin_Meta::POST_CONTENT_ADSENSE_PHONE_ENABLE))
	{
		$mobile_adsense = '<div class="pop-banner text-center my-2 d-md-none">' . get_theme_option(Admin_Meta::POST_CONTENT_ADSENSE_PHONE) . '</div>';
	}


	$output = <<<HTML

		{$post_content_part_1}
		<div class="functional-part my-2">
			{$post_functional_button_part}
		</div>
		{$pc_adsense}
		{$mobile_adsense}
		{$post_content_part_2}

HTML;

	return $output;
}


/**
 * 输出文章HTML内容里的头部图片
 *
 * @param int $post_id
 * @return string
 */
function print_post_content_first_image($post_id)
{
	$output = '';

	//获取图片地址数组
	$images_full_src = Post_Image::get_array_image_full_src($post_id);
	// $images_src = Post_Image::get_array_image_large_src($post_id);

	$first_image_full_src = $images_full_src[0] ?? '';
	// $first_image_src = $images_src[0] ?? '';

	//2个图片的地址必须存在
	if ($first_image_full_src /*&& $first_image_src*/)
	{
		$output = <<<HTML
			<a href="{$first_image_full_src}" data-lightbox="images">
				<img class="img-fluid preview" src="{$first_image_full_src}" alt="封面图" />
			</a>
HTML;
	}

	return $output;
}

/**
 * 输出文章HTML内容里的主要描述
 *
 * @param int $post_id
 * @return string
 */
function print_post_content_description($post_id)
{

	$source_part = '';

	//获取来源地址
	$source_url = get_post_meta($post_id, Post_Meta::POST_SOURCE, true) ?: '';
	//获取来源说明
	$source_text = get_post_meta($post_id, Post_Meta::POST_SOURCE_NAME, true) ?: '';
	//文章主要描述
	$post_content = get_post_field('post_content', $post_id);


	//有来源地址
	if ($source_url)
	{
		//如果缺少来源说明就直接用来源地址替代
		$source_text = $source_text ?: $source_url;

		$source_part = <<<HTML
			<a href="{$source_url}" target="_blank" rel="external nofollow">
				$source_text
			</a>
HTML;
	}
	//只有来源说明的情况
	else if ($source_text)
	{
		$source_part = '<span>' . $source_text . '</span>';
	}

	//如果来源地址或者来源信息不是空的, 添加前置词
	if ($source_part)
	{
		$source_part = <<<HTML
			<p>
				<span>©来源:</span>
				{$source_part}
			</p>
HTML;
	}

	$output = <<<HTML
		$source_part
		$post_content
HTML;

	return $output;
}



/**
 * 输出文章HTML内容里的下载地址部分
 *
 * @param int $post_id
 * @return string
 */
function print_post_content_download($post_id)
{

	$output = '';

	$down = get_post_meta($post_id, Post_Meta::POST_DOWN, true) ?: '';
	$down2 = get_post_meta($post_id, Post_Meta::POST_DOWN2, true) ?: '';
	$down3 = get_post_meta($post_id, Post_Meta::POST_DOWN3, true) ?: '';
	$password = get_post_meta($post_id, Post_Meta::POST_PASSWORD, true) ?? '';
	$password2 = get_post_meta($post_id, Post_Meta::POST_PASSWORD2, true) ?? '';
	$password3 = get_post_meta($post_id, Post_Meta::POST_PASSWORD3, true) ?? '';
	$unzip_password = get_post_meta($post_id, Post_Meta::POST_UNZIP_PASSWORD, true) ?? '';
	$unzip_password2 = get_post_meta($post_id, Post_Meta::POST_UNZIP_PASSWORD2, true) ?? '';
	$unzip_password3 = get_post_meta($post_id, Post_Meta::POST_UNZIP_PASSWORD3, true) ?? '';
	$unzip_sub_password = get_post_meta($post_id, Post_Meta::POST_UNZIP_SUB_PASSWORD, true) ?? '';
	$unzip_sub_password2 = get_post_meta($post_id, Post_Meta::POST_UNZIP_SUB_PASSWORD2, true) ?? '';
	$unzip_sub_password3 = get_post_meta($post_id, Post_Meta::POST_UNZIP_SUB_PASSWORD3, true) ?? '';

	//如果有下载地址
	$download_box_1 = print_download_box('下载1', $down, $password, $unzip_password, $unzip_sub_password);
	$download_box_2 = print_download_box('下载2', $down2, $password2, $unzip_password2, $unzip_sub_password2);
	$download_box_3  = print_download_box('下载3', $down3, $password3, $unzip_password3, $unzip_sub_password3);



	if ($download_box_1 || $download_box_2 ||  $download_box_3)
	{
		$output = <<<HTML
			{$download_box_1}
			
			{$download_box_2}
			
			{$download_box_3}
			<div class="text-dark-1 fs-75 fs-sm-875  ">
				点击下载会自动复制提取密码到剪切板
			</div>
			<div class="row border-bottom my-2">
			</div>
HTML;
	}

	return $output;
}






/**
 * 输出下载按钮
 *
 * @param string $title 标题
 * @param string $down_link 下载链接
 * @param string $access_password
 * @param string $unzip_password
 * @param string $unzip_sub_password
 * @return string 如果缺少下载地址 返回空字符串
 */
function print_download_box($title, $down_link, $access_password, $unzip_password, $unzip_sub_password)
{
	$output = '';

	if ($down_link)
	{
		$down_type = Download_Link_Type::get_type_by_link($down_link);

		//如果有独立的访问密码 
		if ($access_password)
		{

			//如果是百度网盘和夸克的分享 + 不包含 ? 问号
			if (($down_type === Download_Link_Type::BAIDU_PAN || $down_type === Download_Link_Type::QUARK) && stripos($down_link, '?') === false)
			{
				$params = '';

				//如果是百度盘 还需要额外检查格式
				if ($down_type === Download_Link_Type::BAIDU_PAN)
				{
					//如果是标准没有带访问密码的百度分享地址 + 链接里没有 ? 问号
					if (stripos($down_link, 'pan.baidu.com/s/1') !== false)
					{
						$params = [
							'pwd' => $access_password,
						];
					}
				}
				else if ($down_type === Download_Link_Type::QUARK)
				{
					$params = [
						'passcode' => $access_password,
					];
				}

				//如果有正确生成参数
				if ($params)
				{
					//移除#符号和后面的内容, 添加访问密码到下载链接里
					$down_link = explode('#', $down_link)[0] . '?' . http_build_query($params);
				}
			}
		}


		$button_text = Download_Link_Type::get_description($down_type) ?: '下载';

		$access_password_component = $access_password !== '' ? <<<HTML

			<div class="col-auto">
				<div class="input-group">
					<div class="input-group-text bg-light-2">访问码</div>
					<input class="access_password form-control form-control-sm bg-light-2 disabled " type="text" value="{$access_password}" readonly />
				</div>
			</div>
HTML : '';

		$unzip_password_component = $unzip_password !== '' ? <<<HTML

			<div class="col-auto">
				<div class="input-group">
					<div class="input-group-text bg-light-2">解压码</div>
					<input class="unzip_password form-control form-control-sm bg-light-2 disabled" type="text" value="{$unzip_password}" readonly />
				</div>
			</div>
HTML : '';

		$unzip_sub_password_component = $unzip_sub_password !== '' ? <<<HTML

			<div class="col-auto">
				<div class="input-group">
					<div class="input-group-text bg-light-2">解压码2</div>
					<input class="unzip_sub_password form-control form-control-sm bg-light-2 disabled" type="text" value="{$unzip_sub_password}" readonly />
				</div>
			</div>

HTML : '';



		$output = <<<HTML

			<div class="download_container row align-items-center my-2 pb-2 border-bottom">
				<div class="col-auto">
					<div>
						<i class="fa-solid fa-download me-2"></i>
						{$title}
					</div>
				</div>
				<div class="col">
					<div class="download_container row align-items-center g-2">
						{$access_password_component}
						{$unzip_password_component}
						{$unzip_sub_password_component}
						<div class="m-0 mt-3"></div>
						<div class="col-6">
							<a class="download btn btn-sm btn-miku w-100" href="{$down_link}" target="_blank">
							{$button_text}
							</a>
						</div>
						<div class="m-0 mb-3"></div>
					</div>
				</div>
			</div>

HTML;
	}


	return $output;
}

/**
 * @deprecated 2023-12-01 秒传接口已经被封杀
 * 
 * 输出文章HTML内容里的秒传链接部分
 *
 * @param int $post_id
 * @return string
 */
function print_post_content_download_fast_link($post_id)
{
	$output = '';

	$baidu_fast_link = get_post_meta($post_id, Post_Meta::POST_BAIDU_FAST_LINK, true) ?: '';

	if ($baidu_fast_link)
	{
		//百度网盘主页
		$baidu_drive_home = 'https://pan.baidu.com/disk/home?adapt=pc';
		//百度秒传使用教程
		$baidu_fast_link_help = get_site_url() . '/185303';

		//生成一键秒传URL
		$baidu_fast_link_base_64 = base64_encode($baidu_fast_link);
		$baidu_fast_link_href = $baidu_drive_home . '&miku#bdlink=' . $baidu_fast_link_base_64;

		$output = <<<HTML

			<h5 class="mt-3">秒传链接</h5>
			<div class="my-3">
                <textarea class="baidu-fast-link form-control small bg-light-1" style="font-size: 0.75rem;" rows="3" readonly>{$baidu_fast_link}</textarea>
			</div>
			<div class="my-3">
				<a class="btn btn-info me-1 me-sm-2 mb-2 mb-sm-0 px-4" target="_blank" rel="external nofollow" href="{$baidu_fast_link_href}">一键秒传</a>
				<a class="baidupan-home-link btn btn-primary me-1 me-sm-2 mb-2 mb-sm-0 px-4" target="_blank" rel="external nofollow" href="{$baidu_drive_home}">打开百度盘</a>
				<a class="btn  btn-light-2 mb-2 mb-sm-0" target="_blank" href="{$baidu_fast_link_help}">秒传链接使用教程</a>
			</div>
			<div class="small">
				确保秒传脚本为3.1.6+版本, 否则无法使用, 新版脚本需要授权码, 请根据教程里的说明来获取
			</div>
HTML;
	}

	return $output;
}

/**
 * 输出文章HTML内容里的视频部分
 *
 * @param int $post_id
 * @return string
 */
function print_post_content_video($post_id)
{
	$output = '';

	$bilibili_video_id = get_post_meta($post_id, Post_Meta::POST_BILIBILI, true) ?: '';
	$bilibili_play_button = '';

	$youtube_video_id = get_post_meta($post_id, Post_Meta::POST_YOUTUBE, true) ?: '';
	$youtube_play_button = '';

	$video = get_post_meta($post_id, Post_Meta::POST_VIDEO, true) ?: '';
	$video_play_button = '';

	if ($bilibili_video_id)
	{

		$bilibili_play_button = <<<HTML

			<div class="col-auto">
				<button class="btn btn-sm btn-light-2 px-4 open_video_modal" value="{$bilibili_video_id}" data-video-type="bilibili" data-post-id="{$post_id}">
					播放B站视频
				</button>
			</div>
			<div class="col-auto">
				<a class="btn btn-sm btn-light-2 px-4" href="https://www.bilibili.com/video/{$bilibili_video_id}" target="_blank" rel="external nofollow">
					前往B站观看
				</a>
			</div>
	
HTML;
	}

	if ($youtube_video_id)
	{

		$youtube_play_button = <<<HTML

			<div class="col-auto">
				<button class="btn btn-sm btn-light-2 px-4 open_video_modal" value="{$youtube_video_id}" data-video-type="youtube" data-post-id="{$post_id}">
					播放YOUTUBE视频
				</button>
			</div>
			<div class="col-auto">
				<a class="btn btn-sm btn-light-2 px-4" href="https://www.youtube.com/watch?v={$youtube_video_id}" target="_blank" rel="external nofollow">
					前往YOUTUBE观看
				</a>
			</div>
	
HTML;
	}

	//如果有其他在线地址 并且 包含识别符号
	if ($video && stripos($video, '[') !== false)
	{
		//把[]符号 改回 <>
		$video = str_ireplace(['[', ']'], ['<', '>'], $video);

		$type = 'video';
		$value = $video;

		//如果是mp3直链
		if (strripos($video, '.mp3') !== false)
		{
			$type = 'music';
			$value = <<<HTML
				<audio src="{$video}" controls="controls" autoplay="autoplay"></audio>
HTML;
		}
		//将字符串进行 URL 编码
		$value = urlencode($value);

		$video_play_button = <<<HTML
			<div class="col-auto">
				<button class="btn btn-sm btn-light-2 px-4 open_video_modal" value="{$value}" data-video-type="{$type}">
					播放在线视频
				</button>
			</div>
			
HTML;

		// 		//如果是youtube地址 增加翻墙提示
		// 		if (stripos($video, 'youtube') !== false)
		// 		{
		// 			$video_play_button .= <<<HTML
		// 				<div class="m-0"></div>
		// 				<div class="col-12 fs-75 fs-sm-875">
		// 					需要科学上网才能正常播放
		// 				</div>
		// HTML;
		// 		}
	}

	if ($bilibili_play_button || $youtube_play_button || $video_play_button)
	{
		$output = <<<HTML
			<div class="video_container row align-items-center my-2">
				<div class="col-auto">
					<div>
						<i class="fa-solid fa-video me-2"></i>
						在线播放
					</div>
				</div>
				<div class="col">
					<div class="download_container row align-items-center g-2">
						{$bilibili_play_button}
						{$youtube_play_button}
						{$video_play_button}
					</div>
				</div>
			</div>
HTML;
	}

	return $output;
}

/**
 * 输出文章HTML内容里的预览图部分
 *
 * @param int $post_id
 * @return string
 */
function print_post_content_previews_image($post_id)
{
	$output = '';

	//获取图片地址数组
	// $images_src = Post_Image::get_array_image_large_src($post_id);
	$images_full_src = Post_Image::get_array_image_full_src($post_id);

	//忽略第一张图片
	// array_shift($images_src);
	array_shift($images_full_src);

	//循环输出图片
	for ($i = 0; $i < count($images_full_src); $i++)
	{
		$image_href = $images_full_src[$i] ?? '';
		// $image_src = $images_src[$i] ?? '';

		$output .= <<<HTML
			<div class="col-12 col-xxl-6">
				<a href="{$image_href}" data-lightbox="images">
					<img class="img-fluid preview" src="{$image_href}" alt="预览图 {$i}"  />
				</a>
			</div>
HTML;
	}

	//如果有预览图存在, 添加前置标题
	if ($output)
	{
		$output = <<<HTML
			<h5 class="mt-2">预览</h5>
			<div class="row g-2">
				{$output}
			</div>
HTML;
	}

	return $output;
}



/**
 * 文输出文章HTML内容里的功能按钮
 * 点赞+收藏+错误反馈+分享
 * @param int $post_id
 * @return string
 */
function print_post_content_functional_button($post_id)
{

	//获取文章id
	$post_id = get_the_ID();
	$post_like = get_post_like($post_id);
	$post_unlike = get_post_unlike($post_id);
	$post_feedback_rank = Post_Feedback_Rank::get_rank($post_like, $post_unlike);
	$post_favorites = get_post_favorites($post_id);
	$post_shares = get_post_shares($post_id);
	$post_fail_times = get_post_fail_times($post_id);

	//输出点赞按钮
	$like_button = <<<HTML

        <div class="btn-group w-100">
            <button class="btn btn-sm btn-light-2 set-post-like  " data-post-id="{$post_id}">
                <i class="fa-solid fa-thumbs-up  my-2 my-md-0"></i> 
                <span class="text">好评</span>
				<br class="d-sm-none" />
				( <span class="count">{$post_like}</span> )
            </button>
            <div class="btn btn-sm btn-light-2 disabled fw-bold w-25 post_feedback_rank">
                {$post_feedback_rank}
            </div>
            <button class="btn btn-sm btn-light-2 set-post-unlike  " data-post-id="{$post_id}">
                <i class="fa-solid fa-thumbs-down  my-2 my-md-0"></i>
                <span class="text">差评</span>
				<br class="d-sm-none" />
				( <span class="count">{$post_unlike}</span> )
            </button>
        </div>
       

HTML;

	//输出收藏按钮
	$favorite_button_disabled = '';
	//如果用户未登陆
	if (is_user_logged_in() === false)
	{
		//禁用收藏按钮
		$favorite_button_disabled = 'disabled';
	}

	$favorite_button = <<<HTML

		<button class="btn btn-sm btn-light-2 set-post-favorite w-100" data-post-id="{$post_id}" {$favorite_button_disabled}>
			<i class="fa-solid fa-heart my-2 my-md-0" aria-hidden="true"></i> 
			<span class="text">收藏</span>
			<br class="d-sm-none" />
			( <span class="count">{$post_favorites}</span> )
		</button>
HTML;



	//分享按钮
	$sharing_button = '';

	$open_social_share_html = '';
	if (function_exists('\mikuclub_open_social\open_social_share_html'))
	{
		$open_social_share_html = \mikuclub_open_social\open_social_share_html();
	}
	
	

	if ($open_social_share_html)
	{

		$sharing_button = <<<HTML
	 		<div class="dropdown post-share">
				<button class="btn btn-sm btn-light-2 dropdown-toggle w-100 set-post-share" type="button" data-bs-toggle="dropdown" data-post-id="{$post_id}">
					<i class="fa-solid fa-share-alt my-2 my-md-0"></i>
					<span class="text">分享</span>
					<br class="d-sm-none" />
					( <span class="count">{$post_shares}</span> )
				</button>
				{$open_social_share_html}
			</div>

HTML;
	}


	//获取失效次数统计
	$fail_down_button = <<<HTML
		<button class="btn btn-sm btn-light-2 w-100 set-post-fail-times" data-post-id="{$post_id}">
			<i class="fa-solid fa-bug  my-2 my-md-0" aria-hidden="true"></i>
			<span class="text">反馈失效</span>
			<br class="d-sm-none" />
			( <span class="count">{$post_fail_times}</span> )
		</button>
HTML;


	$down_suggestion_button = <<<HTML
		<button type="button" class="btn btn-sm btn-light-2 w-100" data-bs-toggle="collapse" data-bs-target="#unzip-help">
			<i class="fa-solid fa-life-ring d-none d-md-inline-block my-2 my-md-0"></i> 文件解压教程
		</button>
HTML;

	$report_button = <<<HTML
		<button type="button" class="btn btn-sm btn-light-2 w-100 open_post_report_modal" data-post-id="{$post_id}">
			<i class="fa-solid fa-paper-plane d-none d-md-inline-block my-2 my-md-0"></i>
			<span>稿件投诉</span>
		</button>
HTML;


	$unzip_help_text = <<<HTML

		<div class="collapse mt-2" id="unzip-help">
			<div  class="card card-body">
				<h5>文件解压教程</h5>
				<p class="my-1">
					首先准备好解压工具, 电脑端安装 <b>WINRAR</b>, 手机端安装 <b>Zarchiver</b> 或者 <b>ES文件管理器</b>
				</p>
				<h5 class="my-2">然后有2种类型的压缩包: </h5>
				<p class="my-2 fw-bold">
					1. 单一压缩文件的（可以单独下载和解压)  
				</p>
				<p class="my-1">
					- 如果后缀名正常: 直接打开文件 > 输入密码 >解压文件 > 解压成功, 有的情况会有双层压缩, 再继续解压即可
				</p>
				<p class="my-1">
					- 如果需要修改后缀名: 不需要管文件原本后缀是什么，只要是压缩文件，后缀直接改成 .rar， 然后用解压工具打开，工具会自动识别正确的类型， 然后解压即可, (有的系统默认不能更改后缀名，这种情况, 要先百度下如何显示文件后缀名). 
				</p>
				<p class="my-2 fw-bold">
					2. 多个压缩分卷文件的 (需要全部下载完毕后 才能正确解压)  
				</p>
				<p class="my-1">
					- 如果后缀名正常: 只需要解压第一个分卷即可, 工具在解压过程中会自动调用其他分卷, 不需要每个分卷都解压一遍 (所以需要提前全部下载好), 不同压缩格式的第一个分卷命名是有区别的 (RAR格式的第一个分卷是叫 xxx.part1.rar , 7z格式的第一个压缩分卷是叫 xxx.001 , ZIP格式的第一个压缩分卷 就是默认的 XXX.zip ) .
				</p>
				<p class="my-1">
					- 如果是需要改后缀的情况 (比较少见): 需要把文件按顺序重新命名好才能正常解压, RAR的分卷命名格式是  xxx.part1.rar,  xxx.part2.rar,  xxx.part3.rar,  7z的命名格式是 xxx.001, xxx.002, xxx.003, ZIP的排序格式 xxx.zip, xxx.zip.001, xxx.zip.002
				</p>
			</div>
		</div>
	
	
HTML;


	return <<<HTML
        <div class="border-top border-bottom">
            <div class="row py-3 g-3 ">
                <div class="col-12 col-xxl-4">
                        {$like_button}
                </div>
                <div class="col">
                        {$favorite_button}
                </div>
                <div class="col">
                        {$sharing_button}
                </div>
                <div class="col">
                        {$fail_down_button} 
                </div>
                <div class="m-0 d-lg-none"></div>
                <div class="col">
                    {$report_button}
                </div>
                <div class="col">
                        {$down_suggestion_button}
                </div>
              
            </div>
        </div>
        {$unzip_help_text}
HTML;
}


/**
 * 如果未登陆用户访问魔法区, 显示404内容
 * @return string
 */
function print_adult_404_content_for_no_logging_user()
{

	// $login_url = wp_login_url();

	$output = <<<HTML

		<div class="w-50 mx-auto my-5 text-center" style="min-height: 500px">

			<div class="m-3">
				<i class="fa-solid fa-exclamation-triangle fa-5x text-warning"></i>
			</div>
			<h3 class="m-3 mb-5">页面不存在</h3>
			<div class="m-3">该页面可能因为如下原因无法访问</div>
			<ul class="list-group list-group-flush">
				<li class="list-group-item">投稿已被删除</li>
				<li class="list-group-item">投稿正被重新审核</li>
				<li class="list-group-item">用户权限不足</li>
			</ul>

		</div>
        
HTML;

	return $output;
}



/**
 * 如果文章的作者被当前用户拉黑, 输出遮罩class类名 'black-user-post-mask' 来遮挡当前文章
 *
 * @param int $post_author_id
 * @return string
 */
function add_mask_class_to_black_user_post_container($post_author_id)
{
	static $class_name = null;

	//只在第一次初始化的时候才计算
	if ($class_name === null)
	{
		$user_id = get_current_user_id();
		$user_black_list = get_user_black_list($user_id);
		//如果在黑名单内
		if (in_array($post_author_id, $user_black_list))
		{
			//输出文章遮罩的html class类名
			$class_name = 'black-user-post-mask';
		}
		else
		{
			$class_name = '';
		}
	}

	return $class_name;
}
