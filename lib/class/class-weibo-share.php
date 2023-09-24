<?php

namespace mikuclub;

use mikuclub\constant\Category;
use mikuclub\constant\Post_Meta;
use mikuclub\constant\Post_Status;
use mikuclub\constant\Post_Type;
use WP_Term;

/**
 * 通过微博同步网站内容
 */

class Weibo_Share
{
    const WEIBO_APP_KEY = '173298400';
    const WEIBO_USER_NAME = 'hexie2108@sina.com';
    const WEIBO_USER_PASSWORD = 'Wenjie2108@@';
    //微博接口地址
    const WEIBO_API_URL = 'https://api.weibo.com/proxy/article/publish.json';

    //分享成功的状态码
    const WEIBO_SUCCESS_CODE = 100000;

    //最大分享重试次数
    const WEIBO_SHARE_MAX_RETRY_TIME = 3;

    /**
     * 转发文章到微博
     * @return bool|string
     */
    public static function share_to_sina()
    {

        $result = 'not post found';

        $args = [
            'post_type' => Post_Type::POST,
            'post_status' => Post_Status::PUBLISH,
            'meta_query' => [
                [
                    'key' => Post_Meta::POST_SHARE_TO_WEIBO,
                    'value' => static::WEIBO_SHARE_MAX_RETRY_TIME,
                    'compare' => '<',
                    'type' => 'NUMERIC',
                ],
            ],
            'cat' => Category::NO_ADULT_CATEGORY, // 排除魔法区文章
            'posts_per_page' => 1,
            'ignore_sticky_posts' => 1,

        ];

        //查询文章
        $result = get_posts($args);

        //如果结果不是空
        if ($result)
        {

            $post = $result[0];
            //获取文章id
            $post_id = $post->ID;
            //获取文章标题
            $post_title = $post->post_title;
            //获取文章内容
            $post_content = static::get_post_content_for_weibo($post_id);

            //获取文章标签数组
            $array_tag = wp_get_post_tags($post_id);
            //转换成标签名称数组
            $array_tag_name = array_map(function (WP_Term $tag)
            {
                return $tag->name;
            }, $array_tag);

            $keywords = implode('# ', $array_tag_name);

            //去除标签后的标题
            $title = strip_tags($post_title);
            //限制长度
            $title = mb_substr($title, 0, 32, 'utf-8');

            //获取封面图地址
            $array_images = get_images_full_size($post_id);
            $top_image = count($array_images) > 0 ? $array_images[0] : '';


            $body = [
                'title' => $title, //头条的标题
                'content' => $post_content, //头条的正文
                'cover' => $top_image, //头条的封面
                'summary' => $title, //头条的导语
                'text' => $title . '   ' . $keywords . '    全文地址: ' . get_permalink($post_id), //简介的内容
                'source' => static::WEIBO_APP_KEY
            ];

            // $headers = ['Authorization' => 'Basic ' . base64_encode("$username:$userpassword")];

            //发送请求
            $response = wp_remote_post(
                static::WEIBO_API_URL,
                [
                    'body' => $body,
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode(static::WEIBO_USER_NAME . ':' . static::WEIBO_USER_PASSWORD)
                    ],
                ]
            );

            $responseBody = wp_remote_retrieve_body($response);

            //如果内容获取正确
            if ($responseBody)
            {
                $msg = json_decode($responseBody);
                //如果状态码 正确, 说明转发成功,
                if ($msg && isset($msg->code) && intval($msg->code) == static::WEIBO_SUCCESS_CODE)
                {
                    //删除 失败重试数数据
                    delete_post_meta($post_id, Post_Meta::POST_SHARE_TO_WEIBO);

                    $result = true;
                }
                //如果状态码异常
                else
                {
                    //更新失败重试数
                    $count = get_post_meta($post_id, Post_Meta::POST_SHARE_TO_WEIBO, true);
                    $count++;
                    update_post_meta($post_id, Post_Meta::POST_SHARE_TO_WEIBO, $count);

                    $result = $msg;
                }
            }
            else
            {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * 输出weibo文章内容
     *
     * @param int $post_id
     *
     * @return string
     */
    public static function get_post_content_for_weibo($post_id)
    {

        $post = get_post($post_id);  //获取文章主体
        $post_title = $post->post_title; //获取文章标题

        $post_link = get_permalink($post_id); // 获取文章链接

        //以数组x数组的方式返回元数据 [ 'meta_key' => [meta_value] ]
        $metadata = get_post_meta($post_id);
        //获取图片地址数组
        $images_src = get_images_large_size($post_id);
        $images_full_src = get_images_full_size($post_id);


        //获取来源url变量
        $source_url = trim($metadata['source'][0]);
        //获取来源说明
        $source_text = trim($metadata['source_name'][0]);

        //bilibili视频地址
        $bilibili_video = trim($metadata['bilibili'][0]);

        $post_content_part = $post->post_content; //描述部分
        $source_part = ''; //来源部分
        $preview_images_part = ''; //图片预览部分
        $video_part = ''; //视频部分
        $download_part = ''; //下载部分

        //来源部分------------------------------------------------------------------------------------------

        //有来源地址
        if ($source_url)
        {
            //没有来源说明 就使用来源地址当做说明
            if (empty($source_text))
            {
                $source_text = $source_url;
            }
            $source_part = '<a href="' . $source_url . '"  target="_blank" rel="external nofollow">' . $source_text . '</a>';
        }
        //只有来源说明的情况
        else if ($source_text)
        {
            $source_part = $source_text;
        }

        //如果来源信息不是空的 添加前置词
        if ($source_part)
        {
            $source_part = '©来源:  ' . $source_part;
        }

        //预览图片部分------------------------------------------------------------------------------------------
        for ($i = 1; $i < count($images_src); $i++) //从第二张图开始 循环输出剩下的图片
        {
            $preview_images_part .= '<div class="preview-image m-1 ">
														<a href="' . $images_full_src[$i] . '" data-lightbox="images">
															<img class="preview img-fluid"  src="' . $images_src[$i] . '" alt="' . $post_title . '"  />
														</a>
												</div>';
        }
        //如果有预览图存在, 添加前置标题
        if ($preview_images_part)
        {
            $preview_images_part = '
		<h4>预览</h4>
		<div class=" py-2 py-md-0">'
                . $preview_images_part
                . '</div> ';
        }

        if ($bilibili_video)
        {
            $video_part = '
		<h4>在线播放</h4>
		<div class=" py-2 py-md-0">
		  <h2>
            <a class="btn btn-miku w-100 w-md-50" href="https://www.bilibili.com/video/' . $bilibili_video . '" target="_blank" rel="external nofollow"> 点击观看</a>
            </h2>
        </div>';
        }

        $download_part = '
	<h4>下载地址</h4>
		<div class=" py-2 py-md-0">
		    <h2>
		    	<a href="' . $post_link . '" target="_blank">
		    	点击查看下载地址
				</a>
            </h2>
        </div>';


        return <<<HTML

		<div class="first-image-part my-4">
		    <a href="{$images_full_src[0]}" data-lightbox="images">
                <img class="preview img-fluid"  src="{$images_src[0]}" alt="{$post_title}"  />
            </a>
		</div>
		<br/>
		<div class="source-part my-4">
			{$source_part}
		</div>
		<br/>
		<div class="content-part my-4">
			{$post_content_part}
		</div>
        <br/>
		<div class="preview-images-part my-4" id="preview-images-part">
			{$preview_images_part}
		</div>
        <br/>
        <div class="video-part my-4"">
			{$video_part}
		</div>
        <br/>
		<div  class="download-part my-4">
            {$download_part}
		</div>
        <br/>
		<div>
			<small>本帖内容来自于服务器的自动推送, 如果涉及侵权或禁转, 麻烦请通知我, 邮箱地址 hexie2109@gmail.com</small>
		</div>
		
HTML;
    }
}
