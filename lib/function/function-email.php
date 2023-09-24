<?php

namespace mikuclub;

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
            <h4><a href="{$url}" title="密码重置">重置密码</a></h4>
            <br/>
            <p>或者复制下面地址到浏览器地址栏来访问</p>
            <br/>
            <h4>{$url}</h4>
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
