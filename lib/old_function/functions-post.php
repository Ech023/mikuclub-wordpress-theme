<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Category;
use mikuclub\constant\Expired;
use mikuclub\constant\Option_Meta;
use mikuclub\constant\Post_Feedback_Rank;
use mikuclub\constant\Post_Meta;
use mikuclub\constant\Post_Status;
use mikuclub\User_Capability;
use mikuclub\constant\Web_Domain;
use WP_Error;
use WP_Post;
use WP_REST_Request;








/**
 * 从旧版文章里提取出下载地址
 *
 * @param int $post_id
 *
 * @return array<string, string>
 */
function get_down_link_from_old_post($post_id)
{

    $result = [];

    //获取文章内容
    $post_content = get_post_field('post_content', $post_id);

    //尝试搜索文章内容
    $index = stripos($post_content, '<a class="dl" href="');
    //如果搜索到了下载1地址
    if ($index !== false)
    {

        //提取下载1的地址
        $download = substr($post_content, $index + (strlen('<a class="dl" href="')));
        $result[Post_Meta::POST_DOWN] = stristr($download, '"', true);

        //捕捉密码1位置
        $index_pw = stripos($post_content, '<span class="passw">');
        if ($index_pw !== false)
        {
            //提取密码1
            $password_text = substr($post_content, $index_pw + (strlen('<span class="passw">')));
            $result[Post_Meta::POST_PASSWORD] = stristr($password_text, '<', true);
        }

        //搜索第二个下载地址
        $index2 = stripos($download, '<a class="dl" href="');
        //如果搜索到了下载2地址
        if ($index2 !== false)
        {
            //提取下载2的地址
            $download2 = substr($download, $index2 + (strlen('<a class="dl" href="')));
            $result[Post_Meta::POST_DOWN2] = stristr($download2, "\"", true);

            //捕捉密码2位置
            $index_pw2 = stripos($password_text ?? '', '<span class="passw">');
            if ($index_pw2 != false)
            {
                //提取密码2
                $password_text2 = substr($password_text, $index_pw2 + (strlen('<span class="passw">')));
                $result[Post_Meta::POST_PASSWORD2] = stristr($password_text2, '<', true);
            }
        }
    }

    return $result;
}


/**
 *通过REST API创建文章的时候触发
 *为新文章添加初始数据
 *
 * @param WP_Post $post Inserted or updated post object.
 * @param WP_REST_Request $request Request object.
 * @return void
 **/
function action_on_api_insert_post($post, $request)
{

    //必须有元数据数组
    if (isset($request['meta']))
    {

        $meta = $request['meta'];

        $source_name = '';
        $previews = '';
        $down = '';
        $down2 = '';
        $password = '';
        $password2 = '';
        $unzip_password = '';
        $unzip_password2 = '';
        $bilibili = '';
        $baidu_fast_link = '';

        if (isset($meta['source_name']))
        {
            $source_name = $meta['source_name'];
        }
        if (isset($meta['previews']))
        {
            //如果图片数组只有一个图片id, 就直接保存为整数
            if (count($meta['previews']) == 1)
            {
                $previews = $meta['previews'][0];
            } //如果多余一个, 则继续保存为数组
            else if (count($meta['previews']) > 1)
            {
                $previews = $meta['previews'];
            }
        }
        if (isset($meta['down']))
        {
            $down = $meta['down'];
        }
        if (isset($meta['down2']))
        {
            $down2 = $meta['down2'];
        }
        if (isset($meta['password']))
        {
            $password = $meta['password'];
        }
        if (isset($meta['password2']))
        {
            $password2 = $meta['password2'];
        }
        if (isset($meta['unzip_password']))
        {
            $unzip_password = $meta['unzip_password'];
        }
        if (isset($meta['unzip_password2']))
        {
            $unzip_password2 = $meta['unzip_password2'];
        }
        if (isset($meta['bilibili']))
        {
            $bilibili = $meta['bilibili'];
        }
        if (isset($meta['baidu_fast_link']))
        {
            $baidu_fast_link = $meta['baidu_fast_link'];
        }


        //update_post_meta( $post->ID, 'content', $content );
        update_post_meta($post->ID, 'source_name', $source_name);
        update_post_meta($post->ID, 'previews', $previews);
        update_post_meta($post->ID, 'down', $down);
        update_post_meta($post->ID, 'down2', $down2);
        update_post_meta($post->ID, 'password', $password);
        update_post_meta($post->ID, 'password2', $password2);
        update_post_meta($post->ID, 'unzip_password', $unzip_password);
        update_post_meta($post->ID, 'unzip_password2', $unzip_password2);
        update_post_meta($post->ID, 'bilibili', $bilibili);
        update_post_meta($post->ID, 'baidu_fast_link', $baidu_fast_link);

        //添加空meta数据, 目前没在rest api投稿里用到

        //如果不存在数据meta, 创建空值
        if (empty(metadata_exists('post', $post->ID, 'source')))
        {
            update_post_meta($post->ID, 'source', '');
        }
        if (empty(metadata_exists('post', $post->ID, 'video')))
        {
            update_post_meta($post->ID, 'video', '');
        }
        /*if (empty(metadata_exists('post', $post->ID, 'baidu_fast_link')))
        {
            update_post_meta($post->ID, 'baidu_fast_link', '');
        }*/

        //更新相关图片附件的父文章id
        set_post_parent($previews, $post->ID);

        //调用后续文章提交动作 来设置 额外元数据 和发布
        post_submit_action($post->ID);
    }
}

add_action('rest_insert_post', 'mikuclub\action_on_api_insert_post', 10, 2);


/**
 * 用表单新建文章和更新文章后的动作
 *
 * @param int $post_id
 * @return void
 */
function post_submit_action($post_id)
{

    //更新所有大小版本的图片地址
    Post_Image::update_all_array_image_src($post_id);
    //更新缩微图ID
    Post_Image::set_thumbnail_id($post_id);
    //更新缩微图图片地址
    Post_Image::get_thumbnail_src($post_id);


    //如果是高级用户
    if (current_user_can('publish_posts'))
    {
        //发布文章
        post_publish_action($post_id);
    } //如果不是高级用户, 让文章变成待审核状态
    else
    {
        set_post_status($post_id, Post_Status::PENDING);
    }

    //清空bilibili视频缓存信息
    Bilibili_Video::delete_video_meta($post_id);
    File_Cache::delete_cache_meta(File_Cache::POST_CONTENT_PART_1 . '_' . $post_id, File_Cache::DIR_POST);
    File_Cache::delete_cache_meta(File_Cache::POST_CONTENT_PART_2 . '_' . $post_id, File_Cache::DIR_POST);

    File_Cache::delete_cache_meta(File_Cache::POST_META_DESCRIPTION, File_Cache::DIR_POST . DIRECTORY_SEPARATOR . $post_id);
}

add_action('wpuf_add_post_after_insert', 'mikuclub\post_submit_action');
add_action('wpuf_edit_post_after_update', 'mikuclub\post_submit_action');


/**
 * 文章发布动作
 *
 * @param int $post_id
 * @return void
 */
function post_publish_action($post_id)
{


    //下载地址
    $down = get_post_meta($post_id, 'down', true);
    $down2 = get_post_meta($post_id, 'down2', true);

    //更新主要分类
    $main_cat_id = set_post_main_cat_id($post_id);
    //更新具体子分类
    set_post_sub_cat_id($post_id);
    //更新分类ID数组
    set_post_category_ids($post_id);




    //如果下载, 设置fail_time =-1, 关闭下载失效反馈功能
    if (empty($down) && empty($down2))
    {
        update_post_fail_times($post_id, -1);
    }
    else
    {
        // 如果无下载 重置失效次数为0
        update_post_fail_times($post_id, 0);
    }


    //如果是第一次发布
    if (empty(get_post_meta($post_id, 'first_published', true)))
    {

        $post = get_post($post_id);

        //添加 point奖励
        mycred_add(
            'publishing_content',
            $post->post_author,
            1000,
            '发布投稿 %link_with_title%',
            $post_id,
            ['ref_type' => 'post'],
            'mycred_default'
        );

        //如果不是微博禁发分类

        if (!in_array($main_cat_id, Category::get_array_not_weibo()))
        {
            //加入等待新浪微博同步标示
            update_post_meta($post_id, Post_Meta::POST_SHARE_TO_WEIBO, 0);
        }

        //设置发布过的标识
        update_post_meta($post_id, 'first_published', 1);
    }

    //发布文章
    wp_publish_post($post_id);
}








/**
 * 更改文章状态
 *
 * @param int $post_id
 * @param string $post_status
 * @return void
 */
function set_post_status($post_id, $post_status)
{

    global $wpdb;

    //判断文章是否已经公开过
    $first_published = get_post_meta($post_id, 'first_published', true);

    //获取文章所属分类ID数组
    $array_id_category = get_post_category_ids($post_id);
    //如果是动漫区或者视频区或者从未公开过
    if (in_array(Category::ANIME, $array_id_category) || in_array(Category::VIDEO, $array_id_category) || empty($first_published))
    {
        //更新创建时间+状态
        $time = current_time('mysql');


        $wpdb->update(
            $wpdb->posts,
            [
                'post_status' => $post_status,
                'post_date' => $time,
                'post_date_gmt' => get_gmt_from_date($time),
            ],
            [
                'ID' => $post_id,
            ],
            [
                '%s',
                '%s',
                '%s',
            ],
            [
                '%d',
            ]
        );
    }
    //如果不是动漫区
    else
    {
        //只更新状态
        $wpdb->update(
            $wpdb->posts,
            [
                'post_status' => $post_status,
            ],
            [
                'ID' => $post_id,
            ],
            [
                '%s',
            ],
            [
                '%d',
            ]
        );
    }
}

/**
 * 更改附件关联的父文章
 *
 * @param int|int[] $post_id
 * @param int $post_parent_id
 * @return void
 */
function set_post_parent($post_id, $post_parent_id)
{

    global $wpdb;

    //如果是id数组 并且是纯数字数组
    if (is_array($post_id) && is_numeric($post_id[0]))
    {
        $query = 'UPDATE  ' . $wpdb->posts . '  SET post_parent =  ' . $post_parent_id . '  WHERE ID IN (  ' . implode(',', $post_id) . '  )';
        $wpdb->query($query);
    }
    //如果只是单个id, 并且是纯数字
    else if (is_numeric($post_id))
    {

        $wpdb->update(
            $wpdb->posts,
            [
                'post_parent' => $post_parent_id,
            ],
            [
                'ID' => $post_id,
            ],
            [
                '%d',
            ],
            [
                '%d',
            ]
        );
    }
}


/**
 * 更新文章的创建时间
 *
 * @param int $post_id
 *
 * @return int|WP_Error
 */
function update_post_date($post_id)
{

    //只有高级用户有权限
    if (!User_Capability::is_premium_user())
    {
        return new WP_Error(401, __FUNCTION__ . ' : 无权进行该项操作');
    }

    $time = current_time('mysql');
    //更新文章的创建时间
    $result = wp_update_post(
        [
            'ID' => $post_id,
            'post_date' => $time,
            'post_date_gmt' => get_gmt_from_date($time)
        ]
    );

    return $result;
}

/**
 * 添加文章置顶
 *
 * @param int $post_id
 *
 * @return bool
 * */
function add_sticky_posts($post_id)
{


    //获取置顶文章id数组
    $sticky_posts = get_option(Option_Meta::STICKY_POSTS);

    //如果id未曾置顶
    if (!in_array($post_id, $sticky_posts))
    {
        //添加id到数组头部
        array_unshift($sticky_posts, $post_id);
    }

    //更新数组
    return update_option(Option_Meta::STICKY_POSTS, $sticky_posts);
}

/**
 * 移除文章置顶
 *
 * @param int $post_id
 *
 * @return bool
 * */
function delete_sticky_posts($post_id)
{


    //获取置顶文章id数组
    $sticky_posts = get_option(Option_Meta::STICKY_POSTS);

    //搜索元素在数组中的位置
    $index = array_search($post_id, $sticky_posts);
    //如果成功找到
    if ($index !== false)
    {
        //根据位置删除该元素
        unset($sticky_posts[$index]);
    }

    //更新数组
    return update_option(Option_Meta::STICKY_POSTS, $sticky_posts);
}


/**
 * 检测是否是置顶文章
 *
 * @param int $post_id
 * @return bool
 */
function is_sticky_post($post_id)
{

    $is_sticky = false;

    //获取置顶文章id数组
    $sticky_posts = get_option(Option_Meta::STICKY_POSTS);
    if (is_array($sticky_posts) && in_array($post_id, $sticky_posts))
    {
        $is_sticky = true;
    }

    return $is_sticky;
}


/**
 * 驳回投稿
 *
 * @param int $post_id
 * @param string $reject_cause 驳回原因 默认为下载地址失效
 * @return void
 */
function reject_post($post_id, $reject_cause = '下载地址失效')
{

    $user_id = intval(get_post_field('post_author', $post_id));
    $post_title = get_post_field('post_title', $post_id);

    //发送退稿邮件
    send_reject_post_email($post_id, $reject_cause);

    //创建私信通知
    $message_content = '您的投稿 《 ' . $post_title . ' 》( ' . get_permalink($post_id) . ' ) 已被退回, 退回原因: ' . $reject_cause . ' .  您可以通过投稿管理页面重新编辑该稿件.';
    //发送系统私信
    send_private_message($user_id, $message_content, 0, true);

    //更新文章标题+更新状态为草稿
    $time = current_time('mysql');
    wp_update_post(
        [
            'ID' => $post_id,
            'post_title' => $reject_cause . ' ' . $post_title,
            'post_status' => 'draft',
            'post_date' => $time,
            'post_date_gmt' => get_gmt_from_date($time)
        ]
    );
}

/**
 * 转为草稿
 *
 * @param int $post_id
 * @return null|WP_Error
 */
function draft_post($post_id)
{
    $result = null;

    $author_id = get_post_field('post_author', $post_id);
    //如果不是管理员 并且 不是用户自己的投稿
    if (!User_Capability::is_admin() && get_current_user_id() != $author_id)
    {
        $result = new WP_Error(401, __FUNCTION__ . ' : 无权进行该项操作');
    }

    wp_update_post(
        [
            'ID' => $post_id,
            'post_status' => 'draft',
        ]
    );

    return $result;
}


/**
 * 发送稿件驳回通知邮件
 *
 * @param int $post_id
 * @param string $reject_cause
 * @return void
 */
function send_reject_post_email($post_id, $reject_cause)
{

    $user_id = intval(get_post_field('post_author', $post_id));
    $user = get_userdata($user_id);

    $post_title = get_post_field('post_title', $post_id);



    //检查是否有缓存 和 有效收件地址
    //使用内存缓存来避免短时间内重复邮件同个作者, 1天内只发送一次邮件
    if (empty(File_Cache::get_cache_meta(File_Cache::USER_REJECT_POST_EMAIL, File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $user_id, Expired::EXP_1_DAY)) && stripos($user->user_email, "@fake") !== false)
    {


        //邮件标题
        $email_object = '【初音社】您的投稿 ( ' . $post_title . ' ) 已被退回';

        //邮件主体
        $email_content = <<<HTML

		<h4>嗨 {$user->display_name} ,</h4>
		<br/>
		<hr/>
		<p>您在 初音社的投稿 (<b> {$post_title} </b> ) 已被退回</p>
		<p>退回原因: {$reject_cause}</p>
		<br/>
		<p>请根据原因进行对应修改</p>
		<br/>
		<p>如果不想再继续修改该稿件的话 无视本邮件即可</p>
		<p>如果有疑问, 可以加QQ群 649609553 进行询问</p>
		<br/>
		<hr/>
		<p>初音社</p>

HTML;

        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

        //发送邮件
        wp_mail($user->user_email, $email_object, $email_content, $headers);

        //设置缓存
        File_Cache::set_cache_meta(File_Cache::USER_REJECT_POST_EMAIL, File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $user_id, 1);
    }
}


/**
 * 输出文章HTML内容
 *
 * @param int|null $post_id
 *
 * @return string
 */
function print_post_content($post_id = null)
{
    $cache_key_post_content_part_1 = File_Cache::POST_CONTENT_PART_1 . '_' . $post_id;
    $cache_key_post_content_part_2 = File_Cache::POST_CONTENT_PART_2 . '_' . $post_id;

    $post_content_part_1 = File_Cache::get_cache_meta($cache_key_post_content_part_1, File_Cache::DIR_POST, Expired::EXP_1_DAY);
    if (empty($post_content_part_1))
    {

        $post = get_post($post_id);  //获取文章主体
        $post_title = $post->post_title; //获取文章标题

        //以数组x数组的方式返回元数据 [ 'meta_key' => [meta_value] ]
        $metadata = get_post_meta($post_id);

        //获取图片地址数组
        $images_src = Post_Image::get_array_image_large_src($post_id);
        $images_full_src = Post_Image::get_array_image_full_src($post_id);

        //获取来源url变量
        $source_url = array_key_exists('source', $metadata) ? trim($metadata['source'][0]) : '';
        //获取来源说明
        $source_text = array_key_exists('source_name', $metadata) ? trim($metadata['source_name'][0]) : '';


        //下载地址
        $down = array_key_exists('down', $metadata) ? trim($metadata['down'][0]) : '';
        $down2 = array_key_exists('down2', $metadata) ? trim($metadata['down2'][0]) : '';
        //密码
        $password1 = array_key_exists('password', $metadata) ? trim($metadata['password'][0]) : '';
        $password2 = array_key_exists('password2', $metadata) ? trim($metadata['password2'][0]) : '';
        $password_unzip1 = array_key_exists('unzip_password', $metadata) ? trim($metadata['unzip_password'][0]) : '';
        $password_unzip2 = array_key_exists('unzip_password2', $metadata) ? trim($metadata['unzip_password2'][0]) : '';



        //秒传链接
        $baidu_fast_link = array_key_exists('baidu_fast_link', $metadata) ? trim($metadata['baidu_fast_link'][0]) : '';

        $post_content = $post->post_content; //描述部分
        $source_part = ''; //来源部分

        $password_part = ''; //密码部分

        $download_part = ''; //下载部分


        //第一张图片部分------------------------------------------------------------------------------------------
        $first_image_part = '
		<a href="' . $images_full_src[0] . '" data-lightbox="images">
			<img class="preview img-fluid" src="' . $images_src[0] . '" alt="' . $post_title . '" />
		</a>
		';


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


        //如果访问密码1存在
        $password1_part = ''; //密码1部分
        $password1_id = 'password1';

        if ($password1 !== '')
        {
            $password1_part .= print_password_form($password1_id, '提取密码', $password1);
        }
        //如果解压密码1存在
        if ($password_unzip1 !== '')
        {
            $password1_part .= print_password_form('password_unzip1', '解压密码', $password_unzip1);
        }
        //如果有密码
        if ($password1_part)
        {
            $password1_part = '<div class="col-12 col-sm-6">' . $password1_part . '</div>';
        }

        //如果访问密码2存在
        $password2_part = ''; //密码2部分
        $password2_id = 'password2';

        if ($password2 !== '')
        {
            $password2_part .= print_password_form($password2_id, '提取密码2', $password2);
        }
        //如果解压密码2存在
        if ($password_unzip2 !== '')
        {
            $password2_part .= print_password_form('password_unzip2', '解压密码2', $password_unzip2);
        }
        //如果有密码
        if ($password2_part)
        {
            $password2_part = '<div class="col-12 col-sm-6">' . $password2_part . '</div>';
        }

        if ($password1_part || $password2_part)
        {
            $password_part = '
			<h4>密码</h4>
			<div class="row my-3">'
                . $password1_part
                . $password2_part
                . '</div>
		
			';
        }


        //下载部分------------------------------------------------------------

        $download_links = '';
        if ($down)
        {
            $download_links = print_download_button('下载1', $down, $password1_id, $password1);
        }
        if ($down2)
        {
            $download_links .= print_download_button('下载2', $down2, $password2_id, $password2);
        }
        //添加下载链接
        if ($download_links)
        {
            $download_part = '
			<h4>下载</h4>
			<div class="row my-3">'
                . $download_links
                . '</div>
			<div class="small">
				点击下载会自动复制提取密码到剪切板
			</div>
			';
        }
        //添加秒传链接
        if ($baidu_fast_link)
        {

            //转换为base64编码 给一键秒传使用
            $baidu_fast_link_base_64 = base64_encode($baidu_fast_link);

            $download_part .= '<h4 class="mt-3">秒传链接 (暂时复活, 请更新脚本到3.1.6版本)</h4>

			<div class="my-3 " style="/*white-space: pre-line;word-break: break-all;*/">
                <textarea class="baidu-fast-link form-control small bg-white" readonly rows="3" style="font-size: 0.75rem;">'
                . $baidu_fast_link
                . '</textarea>
			</div>
			<div class="my-3">
			    <a class="btn  btn-info me-1 me-sm-2 mb-2 mb-sm-0 px-4" target="_blank" rel="external nofollow" href="https://pan.baidu.com/disk/home?adapt=pc&miku#bdlink=' . $baidu_fast_link_base_64 . '">一键秒传</a>
			    <a class="baidupan-home-link btn  btn-primary me-1 me-sm-2 mb-2 mb-sm-0 px-4" target="_blank" rel="external nofollow" href="https://pan.baidu.com/disk/home?adapt=pc">打开百度盘</a>
				<a class="btn  btn-secondary mb-2 mb-sm-0" target="_blank" href="' . get_site_url() . '/185303">秒传链接使用教程</a>
			</div>
			<div class="small">
				安装最新脚本后可使用一键秒传, 点击打开百度盘会自动复制秒传链接到剪切板
			</div>
			';
        }

        $post_content_part_1 = <<<HTML

            <div class="first-image-part my-4" id="first-image-part">
                {$first_image_part}
            </div>
            <div class="source-part my-4">
                {$source_part}
            </div>
            <div class="content-part my-4" >
                {$post_content}
            </div>
            
            <div class="password-part my-4" id="password-part">
                {$password_part}
            </div>
            <div class="download-part my-4">
                {$download_part}
            </div>

HTML;

        File_Cache::set_cache_meta($cache_key_post_content_part_1, File_Cache::DIR_POST, $post_content_part_1);
    }

    /*if ($download_part)
    {
        $download_part .= '<hr/>';
    }*/

    $post_functional_part = post_functional_box();

    $post_content_part_2 = File_Cache::get_cache_meta($cache_key_post_content_part_2, File_Cache::DIR_POST, Expired::EXP_1_DAY);
    if (empty($post_content_part_2))
    {

        $pc_adsense = '';
        //PC端 文章页 - 正文中间
        if (get_theme_option(Admin_Meta::POST_CONTENT_ADSENSE_PC_ENABLE))
        {
            $pc_adsense = '<div class="pop-banner text-center my-4 d-none d-md-block">' . get_theme_option(Admin_Meta::POST_CONTENT_ADSENSE_PC) . '</div>';
        }
        $mobile_adsense = '';
        //手机端 文章页 - 正文中间
        if (get_theme_option(Admin_Meta::POST_CONTENT_ADSENSE_PHONE_ENABLE))
        {
            $mobile_adsense = '<div class="pop-banner text-center my-3 d-md-none">' . get_theme_option(Admin_Meta::POST_CONTENT_ADSENSE_PHONE) . '</div>';
        }


        $video_part = ''; //在线播放部分

        //bilibili地址
        $bilibili = array_key_exists('bilibili', $metadata) ? trim($metadata['bilibili'][0]) : '';
        //在线播放
        $video = array_key_exists('video', $metadata) ? trim($metadata['video'][0]) : '';


        //如果是BILIBILI视频-----------------------------------------------------------------------------
        if ($bilibili)
        {

            $video_part = '

                <div class="row my-3">
                    <div class="col-12 col-sm-6">
                        <button class="btn btn-miku w-100 w-md-50 play-button" value="' . $bilibili . '" data-video-type="bilibili" data-post-id="' . $post_id . '">
                            <span class="button-text">点击播放</span>
                            <span class="button-loading spinner-border spinner-border-sm" style="display: none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                    <div class="col-12 col-sm-6 mt-3 my-sm-0">
                        <a class="btn btn-miku w-100 w-md-50" href="https://www.bilibili.com/video/' . $bilibili . '" target="_blank" rel="external nofollow">
                            前往B站观看
                        </a>
                    </div>
                </div>
            ';
        }
        //如果是其他在线地址 并且 包含识别符号
        else if ($video && stripos($video, '[') !== false)
        {

            //把[]符号 改回 <>
            $video = str_ireplace('[', '<', $video);
            $video = str_ireplace(']', '>', $video);

            //如果是mp3直链
            if (strripos($video, '.mp3') !== false)
            {
                $type = 'music';
                $value = '<audio src="' . $video . '" controls="controls" autoplay="autoplay"></audio>';
            }
            else
            {
                $type = 'video';
                $value = $video;
            }

            //编码内容
            $value = urlencode($value);

            $video_part = '

                <div class="row my-3">
                    <div class="col-12 col-sm-6">
                        <button class="btn btn-miku w-100 w-md-50 play-button" value="' . $value . '" data-video-type="' . $type . '">
                            <span class="button-text">点击播放</span>
                            <span class="button-loading spinner-border spinner-border-sm" style="display: none" role="status" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
            ';

            //如果是youtube地址 增加翻墙提示
            if (stripos($video, 'youtube') !== false)
            {
                $video_part .= '

                    <div class="small">
                        需要科学上网才能正常播放
                    </div>
                ';
            }
        }


        //如果有B站地址/在线播放地址
        if ($video_part)
        {
            //添加在线播放标题
            $video_part = '<h4>在线播放</h4>' . $video_part;
        }

        //预览图片部分------------------------------------------------------------------------------------------
        $preview_images_part = ''; //图片预览部分

        //获取图片地址数组
        $images_src = Post_Image::get_array_image_large_src($post_id);
        $images_full_src = Post_Image::get_array_image_full_src($post_id);

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

        $post_content_part_2 = <<<HTML
            {$pc_adsense}
            {$mobile_adsense}
            <div class="video-part my-4" id="video-part">
                {$video_part}
            </div>
            <div class="preview-images-part my-4" id="preview-images-part">
                {$preview_images_part}
            </div>
HTML;

        File_Cache::set_cache_meta($cache_key_post_content_part_2, File_Cache::DIR_POST, $post_content_part_2);
    }

    return <<<HTML

    
    {$post_content_part_1}
    
    <div class="functional-part my-4 ">
        {$post_functional_part}
    </div>
    
    {$post_content_part_2}
    

HTML;

    /*
    return <<<HTML

		<div class="first-image-part my-5" id="first-image-part">
			{$first_image_part}
		</div>
		<div class="source-part my-5">
			{$source_part}
		</div>
		<div class="content-part my-54" >
			{$post_content}
		</div>
		
		<div class="password-part my-5" id="password-part">
			{$password_part}
		</div>
		<div class="download-part my-5">
			{$download_part}
		</div>
		<div class="functional-part my-5 ">
			{$post_functional_part}
		</div>
		
		{$pc_adsense}
		{$mobile_adsense}
		<div class="video-part my-5" id="video-part">
			{$video_part}
		</div>
		<div class="preview-images-part my-5" id="preview-images-part">
			{$preview_images_part}
		</div>
 
HTML;*/
}


/**
 * 输出密码表单
 *
 * @param string $class_name
 * @param string $name
 * @param string $value
 *
 * @return string
 */
function print_password_form($class_name, $name, $value)
{
    return '
	<div class="input-group w-100 w-md-50 my-2">
				<div class="input-group-prepend">
					<span class="input-group-text bg-white">' . $name . '</span>
				</div>
				<input class="form-control bg-white ' . $class_name . '"  type="text" value="' . $value . '" readonly />
			</div>';
}

/**
 * 输出下载按钮
 *
 * @param string $name
 * @param string $href
 * @param string $password_id
 * @param string $password
 *
 * @return string
 */
function print_download_button($name, $href, $password_id, $password)
{

    //如果是百度云盘地址
    //增加后缀说明
    if (stripos($href, 'pan.baidu.com') !== false)
    {
        $name .= ' (百度网盘)';

        //如果是标准百度分享地址 并且存在访问密码 , 并且 不存在 ? 参数
        if (stripos($href, 'pan.baidu.com/s/1') !== false && $password && stripos($href, '?') === false)
        {

            //移除#符号和后面的内容, 添加访问密码到下载链接里
            $href = explode('#', $href)[0] . '?' . http_build_query([
                'pwd' => $password,
            ]);
        }
    }
    else if (stripos($href, 'aliyundrive') !== false)
    {
        $name .= ' (阿里云盘)';
    }
    else if (stripos($href, 'lanzou') !== false)
    {
        $name .= ' (蓝奏云)';
    }
    else if (stripos($href, 'weiyun') !== false)
    {
        $name .= ' (腾讯微云)';
    }
    else if (stripos($href, '115.com') !== false)
    {
        $name .= ' (115盘)';
    }
    else if (stripos($href, 'xunlei') !== false)
    {
        $name .= ' (迅雷云盘)';
    }
    else if (stripos($href, 't00y.com') !== false)
    {
        $name .= ' (城通盘)';
    }
    else if (stripos($href, 'quqi') !== false)
    {
        $name .= ' (曲奇云盘)';
    }
    else if (stripos($href, '189') !== false)
    {
        $name .= ' (天翼云)';
    }
    else if (stripos($href, '139') !== false)
    {
        $name .= ' (和彩云)';
    }
    else if (stripos($href, 'quark') !== false)
    {
        $name .= ' (夸克网盘)';

        //如果存在访问密码 , 并且 不存在 ? 参数
        if ($password && stripos($href, '?') === false)
        {

            //移除#符号和后面的内容, 添加访问密码到下载链接里
            $href = explode('#', $href)[0] . '?' . http_build_query([
                'passcode' => $password,
            ]);
        }
    }
    else if (stripos($href, 'magnet') !== false)
    {
        $name .= ' (磁力链接)';
    }
    else if (stripos($href, 'sharepoint') !== false)
    {
        $name .= ' (OneDrive 要梯子)';
    }
    else if (stripos($href, 'mega') !== false)
    {
        $name .= ' (MEGA盘 要梯子)';
    }

    return '
	
	<div class="col-12 col-sm-6 my-2 my-sm-0">
		<a class="btn btn-miku w-100 w-md-50 download" title="' . $name . '" href="' . $href . '" target="_blank" data-password-id="' . $password_id . '">
			' . $name . '
		</a>
	</div>
	
	';
}


/**
 * 文章页功能区
 * 点赞+收藏+错误反馈+分享
 * @return string
 */
function post_functional_box()
{

    //获取文章id
    $post_id = get_the_ID();
    $post_like = get_post_like($post_id);
    $post_unlike = get_post_unlike($post_id);
    $Post_Feedback_Rank = Post_Feedback_Rank::get_rank($post_like, $post_unlike);

    //输出点赞按钮
    $like_button = <<<HTML

        <div class="btn-group w-100">
            <button class="btn btn-sm btn-secondary set-post-like  " data-post-id="{$post_id}">
                <i class="fa-solid fa-thumbs-up  my-2 my-md-0"></i> 
                <span class="text">好评</span> <br class="d-sm-none" /> ( <span class="count">{$post_like}</span> )
            </button>
            <div class="btn btn-sm btn-secondary disabled text-bg-secondary fw-bold w-25 Post_Feedback_Rank">
                {$Post_Feedback_Rank}
            </div>
            <button class="btn btn-sm btn-secondary set-post-unlike  " data-post-id="{$post_id}">
                <i class="fa-solid fa-thumbs-down  my-2 my-md-0"></i> 
                <span class="text">差评</span> <br class="d-sm-none" /> ( <span class="count">{$post_unlike}</span> )
            </button>
        </div>
       

HTML;

    //输出收藏按钮
    $favorite_button = '';
    $favorite_button_disabled = '';
    //如果用户未登陆
    if (is_user_logged_in() === false)
    {
        //禁用收藏按钮
        $favorite_button_disabled = 'disabled';
    }

    $favorite_button = '
		          <button class="btn btn-sm btn-secondary set-post-favorite w-100" data-post-id="' . $post_id . '" ' . $favorite_button_disabled . '>
		          <i class="fa-solid fa-heart my-2 my-md-0" aria-hidden="true"></i> 
		          <span class="text">收藏</span> <br class="d-sm-none" /> ( <span class="count">' . get_post_favorites($post_id) . '</span> )
		          </button>';



    //分享按钮
    $sharing_box = '';
    if (function_exists('open_social_share_html'))
    {
        $sharing_box = '
		 <div class="dropdown  post-share  ">
		 	 <button class="btn btn-sm btn-secondary dropdown-toggle w-100 set-post-share" type="button" data-bs-toggle="dropdown" data-post-id="' . $post_id . '">
			    <i class="fa-solid fa-share-alt my-2 my-md-0"></i> <span class="text">分享</span>  <br class="d-sm-none" /> ( <span class="count">' . get_post_shares($post_id) . ' </span> )
			  </button>'
            . open_social_share_html()
            . '</div>';
    }


    //获取失效次数统计
    $fail_times = get_post_fail_times($post_id);
    //如果小于 0,  重置为0
    if ($fail_times < 0)
    {
        $fail_times = 0;
    }

    $fail_down_button = '
		<button class="btn btn-sm btn-secondary w-100 set-post-fail-times" data-post-id="' . $post_id . '">
			<i class="fa-solid fa-bug  my-2 my-md-0" aria-hidden="true"></i> 
			 <span class="text">反馈失效</span> <br class="d-sm-none" /> ( <span class="count">' . $fail_times . '</span>  )
		</button> ';

    /*$down_suggestion_button = '
    <button type="button" class="btn btn-outline-dark m-3 m-md-1" data-container="body" data-html="true" data-toggle="popover" data-placement="top" data-trigger="focus" title="下载的文件打不开/损坏/密码错误?" data-content="' . print_download_help() . '">
          <i class="fa-solid fa-life-ring"></i> 文件打不开/密码错误?
    </button>
    ';*/

    $down_suggestion_button = '
	<button type="button" class="btn btn-sm btn-secondary w-100" data-bs-toggle="collapse" data-bs-target="#unzip-help">
	  	<i class="fa-solid fa-life-ring d-none d-md-inline-block my-2 my-md-0"></i> 文件解压教程
	</button>
	';

    $report_button = '
	<button type="button" class="btn btn-sm btn-secondary w-100 open-post-report"  data-post-id="' . $post_id . '">
        <i class="fa-solid fa-paper-plane d-none d-md-inline-block my-2 my-md-0"></i>
	  	<span>
	  	    稿件投诉
	  	</span>
	</button>
	';

    $unzip_help_text = print_unzip_help();


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
                    
                        {$sharing_box}
                
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
 * 输出下载提示信息
 * 
 * @return string
 */
function print_download_help()
{
    return '
<div>
	<b>99%的情况 解压错误 或 密码错误 都是自己本地的设备和应用不兼容导致, 请在评论前先按照步骤试一遍, 另外请不要随便口吐芬芳和问候UP的家人, 没人会专门放一个损坏的资源来逗人玩.</b>
	<p>1. 如果是改后缀的压缩文件, 尝试把后缀名改回 RAR/ZIP/7z, 然后用解压软件打开.</p>
	<p>2. 如果是 后缀7z.001/part01.rar 这种压缩分卷的资源, 要全部都下载完成后 再只解压第一个文件即可, 软件会从其他分卷中提取内容.</p>
	<p>3. 更换设备和解压应用, 软件解压错误的请更换其他软件, 手机端解压错误的 请更换成电脑  (电脑端: Winrar / 7-zip, 手机: ES管理器 / ZArchiver).</p>
	<p>4. 文件损坏导致无法解压, 请重新下载一遍</p>
</div>';
}

/**
 * 输出解压说明
 * 
 * @return string
 */
function print_unzip_help()
{
    return '
	<div class="collapse mt-2" id="unzip-help">
		<div  class="card card-body">
			<h4>文件解压教程</h4>
			<p class="my-1">首先准备好解压工具, 电脑端安装 <b>WINRAR</b>, 手机端安装 <b>Zarchiver</b> 或者 <b>ES文件管理器</b>,  就基本不会解压错误，<span class="text-danger">不要用那些乱报错的阴间解压软件!!! 如果还去用, 报错了就不要在评论里抱怨!!!</span></p>
			<h5 class="my-2">然后有2种类型的压缩包: </h5>
			<p class="my-2 fw-bold">1. 单一压缩文件的（可以单独下载和解压)  </p>
			<p class="my-1">- 如果后缀名正常: 直接打开文件 > 输入密码 >解压文件 > 一气呵成 . </p>
			<p class="my-1">- 如果需要修改后缀名: 不需要管文件原本后缀是什么，只要是压缩文件，后缀直接改成 .rar， 然后用上面提到的解压工具打开，工具会自动识别正确的类型， 然后解压即可, (有的人的系统默认不能更改后缀名，自己百度下如何显示后缀名). </p>
			<p class="my-2 fw-bold">2. 多个压缩分卷的 (需要全部下载完毕后 才能正确解压)  </p>
			<p class="my-1">
				- 如果后缀名正常: 只需要解压第一个分卷即可, 工具在解压过程中会自动调用其他分卷, 不需要每个分卷都解压一遍 (所以需要提前全部下载好), 不同压缩格式的第一个分卷命名是有区别的 (RAR格式的第一个分卷是叫 xxx.part1.rar , 7z格式的第一个压缩分卷是叫 xxx.001 , ZIP格式的第一个压缩分卷 就是默认的 XXX.zip ) .
			</p>
			<p class="my-1">
			- 如果是需要改后缀的情况 (比较少见): RAR的分卷命名格式是  xxx.part1.rar,  xxx.part2.rar,  xxx.part3.rar,  7z的命名格式是 xxx.001, xxx.002, xxx.003, ZIP的排序格式 xxx.zip, xxx.zip.001, xxx.zip.002
			</p>
		</div>
	</div>	
';
}

/**
 * 为未登录用户显示 404成人内容
 * @return string
 */
function adult_404_content_for_no_logging_user()
{

    $login_url = wp_login_url();


    $output = <<<HTML

		 <div class="w-50 mx-auto my-5 text-center" style="min-height: 500px">

	            <div class="m-3">
	                <i class="fa-solid fa-exclamation-triangle fa-5x text-warning"></i>
	            </div>
	            <h3 class="m-3 mb-5">页面不存在</h3>
	            <div class="m-3">该页面可能因为如下原因无法访问</div>
	            <ul class="list-group list-group-flush">
	                <li class="list-group-item">投稿已被删除</li>
	                <li class="list-group-item">投稿内容正在重新审核</li>
	                    <!--li class="list-group-item">也许是您忘了登陆  <a class="btn btn-miku m-2" href="{$login_url}">点击登陆</a></li-->
	            </ul>
	           

        </div>
        

        
HTML;

    return $output;
}

/**
 * 如果文章的作者被当前用户拉黑, 输出遮罩class类名 来遮挡当前文章
 *
 * @param int $post_author_id
 * @return string
 */
function set_black_user_post_container_mask_class($post_author_id)
{

    static $user_black_list = null;

    //只在初始化的时候获取一次
    if ($user_black_list === null)
    {
        $user_id = get_current_user_id();
        $user_black_list = get_user_black_list($user_id);
    }

    $class_name = '';

    //如果在黑名单内
    if (in_array($post_author_id, $user_black_list))
    {
        //输出文章遮罩的html class类名
        $class_name = 'black-user-post-mask';
    }

    return $class_name;
}
