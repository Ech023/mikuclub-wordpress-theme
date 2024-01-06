<?php

namespace mikuclub;

/**
 * 网站邮件相关的钩子和动作
 */

//移除WordPress 邮件里的表情钩子
 remove_filter('wp_mail', 'wp_staticize_emoji_for_email');




 //关闭密码变更通知
add_filter('wp_password_change_notification_email', '__return_false');
//关闭密码修改用户邮件
add_filter('password_change_email', '__return_false');
//关闭新用户注册站长邮件
add_filter('wp_new_user_notification_email_admin', '__return_false');
//关闭新用户注册用户邮件
add_filter('wp_new_user_notification_email', '__return_false');

//修改默认邮件发信人名称
add_filter('wp_mail_from_name', 'mikuclub\set_email_from_name');

//自定义忘记密码邮件通知
add_filter('retrieve_password_message', 'mikuclub\set_email_reset_password_message', 10, 4);

//避免向无效的邮箱地址发送邮件
add_filter('wp_mail', 'mikuclub\block_to_send_fake_email');