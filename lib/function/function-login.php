<?php

namespace mikuclub;

use mikuclub\constant\Config;

/**
 *  登陆页相关函数
 */


/**
 * 忘记密码页 自定义提示信息
 * 
 * @return void
 */
function custom_lostpassword_message()
{
	echo <<<HTML
    <p class="my-2 text-muted small">
		如果显示邮件发送成功, 却没在邮箱里看到的话, 请检查下垃圾箱.
	</p>
	<p class="my-2 text-muted small">
		如果提交后提示错误的话, 请过5分钟再试试, 多次无效的情况, 可以手动发邮件到 hexie2109@gmail.com 主题填下: 找回密码 内容注明 用户名或邮箱.
	</p>
HTML;
}



/**
 * 登陆页面 自定义css和js
 *
 * @return void
 */
function setup_login_page_css_and_script()
{

	//bootstrap css
	wp_enqueue_style('twitter-bootstrap-css', 'https://cdn.staticfile.org/twitter-bootstrap/4.5.0/css/bootstrap.min.css', [], '4.50');

	wp_enqueue_style('custom-system-css', get_template_directory_uri() . '/css/style-system.css', [], Config::CSS_JS_VERSION);
	wp_enqueue_style('custom-login-css', get_template_directory_uri() . '/css/style-login.css', [], Config::CSS_JS_VERSION);


	wp_enqueue_script('custom-login-js', get_template_directory_uri() . '/js/login.js', [], Config::CSS_JS_VERSION, true);
}






