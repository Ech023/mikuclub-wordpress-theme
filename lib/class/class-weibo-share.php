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
     * 添加文章元数据来注明 那些文章需要同步到微博
     *
     * @param int $post_id
     * @return void
     */
    public static function add_post_to_weibo_sync($post_id)
    {
        if ($post_id)
        {
            //获取主分类
            $main_cat_id = get_post_main_cat_id($post_id);

            //如果不是微博禁发分类
            if (!in_array($main_cat_id, Category::get_array_not_weibo()))
            {
                //加入等待新浪微博同步标示
                update_post_meta($post_id, Post_Meta::POST_SHARE_TO_WEIBO, 0);
            }
        }
    }

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
            $array_images = Post_Image::get_array_image_full_src($post_id);
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

        $post_link = get_permalink($post_id); // 获取文章链接

        $first_image_part = print_post_content_first_image($post_id);
        $post_description_part = print_post_content_description($post_id);
        $preview_images_part = print_post_content_previews_image($post_id);

        //视频部分
        $bilibili_video_id = get_post_meta($post_id, Post_Meta::POST_BILIBILI, true) ?: '';
        $video_part = '';
        if ($bilibili_video_id)
        {
            $video_part = <<<HTML
                <h4>在线播放</h4>
                <div class=" py-2 py-md-0">
                    <h2>
                        <a class="btn btn-miku w-100 w-md-50" href="https://www.bilibili.com/video/{$bilibili_video_id}" target="_blank" rel="external nofollow">点击观看</a>
                    </h2>
                </div>
HTML;
        }

        //下载部分
        $download_part = <<<HTML
            <h4>下载地址</h4>
            <div class=" py-2 py-md-0">
                <h2>
                    <a href="{$post_link}" target="_blank">点击查看下载地址</a>
                </h2>
            </div>
HTML;

        return <<<HTML

            <div class="first-image-part my-4" id="first-image-part">
                    {$first_image_part}
            </div>
            <br/>
            <div class="content-part my-4" >
				{$post_description_part}
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
                <small>本帖内容来自于初音社网站的自动推送, 如果有资源的相关问题, 请打开链接向在网站投稿的UP用户反馈, 如果帖子涉及侵权或禁转, 麻烦请通知我, 邮箱地址 hexie2109@gmail.com</small>
            </div>
		
HTML;
    }
}
