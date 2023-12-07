<?php

namespace mikuclub;

use mikuclub\constant\Expired;
use WP_User;

/**
 *  邮件相关函数
 */


/**
 * 修改默认邮件发信人名称
 *
 * @param mixed $email
 * @return mixed
 */
function set_email_from_name($email)
{
    return get_option('blogname');
}



/**
 * 自定义忘记密码邮件通知
 *
 * @param string $message 默认重置邮件文本
 * @param string $key 重置密钥
 * @param string  $user_login The username for the user.
 * @param WP_User $user_data  WP_User object.
 *
 * @return bool
 */
function set_email_reset_password_message($message, $key,  $user_login, $user_data)
{

    $url = get_site_url() . '/wp-login.php?action=rp&key=' . $key . '&login=' . rawurlencode($user_login);

    $mailcontent = <<< HTML

        <div>
            <h2>重置密码?</h2>
            <br/>
            <p>
                如果你申请了重置 {$user_login} 的密码，请打开下面的链接来设置新密码。如果你没有提出该请求，请忽略这封邮件。
            </p>
            <br/>
            <h5><a href="{$url}" title="密码重置">重置密码</a></h5>
            <br/>
            <p>或者复制下面地址到浏览器地址栏来访问</p>
            <br/>
            <h5>{$url}</h5>
            <br/>
            <br/>
            <br/>
            <p>初音社 | 联系邮箱 hexie2109@gmail.com</p>
            <br/>
            <br/>	
        </div>

HTML;


    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

    wp_mail($user_data->user_email, '[初音社] 密码重置请求', $mailcontent, $headers);

    //返回false 屏蔽默认的密码重置邮件
    return false;
}



/**
 * 发送文章退稿通知邮件
 *
 * @param int $post_id
 * @param string $reject_cause 退稿原因
 * @return void
 */
function send_email_reject_post($post_id, $reject_cause)
{

    $user_id = intval(get_post_field('post_author', $post_id));
    $user = get_userdata($user_id);
    $user_email = $user->user_email;
    $user_display_name = $user->display_name;


    //如果用户的邮件地址有效
    if (stripos($user_email, "@fake") !== false)
    {

        //使用内存缓存来避免短时间内重复邮件同个作者, 6小时内只发送一次邮件
        File_Cache::get_cache_meta_with_callback(File_Cache::USER_REJECT_POST_EMAIL, File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $user_id, Expired::EXP_6_HOURS, function () use ($user_email, $user_display_name, $post_id, $reject_cause)
        {
            $post_title = get_post_field('post_title', $post_id);

            //邮件标题
            $email_object = '【初音社】您的投稿 ( ' . $post_title . ' ) 已被退回';

            //邮件主体
            $email_content = <<<HTML

                <h4>嗨 {$user_display_name} ,</h4>
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

            //邮件头部
            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

            //发送邮件
            wp_mail($user_email, $email_object, $email_content, $headers);

            return true;
        });
    }
}
