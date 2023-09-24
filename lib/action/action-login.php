<?php

namespace mikuclub;


/**
 * 登陆页 相关的钩子和动作
 */

 //忘记密码页 自定义提示信息
 add_action('lostpassword_form', 'mikuclub\custom_lostpassword_message');
//登陆页面 自定义css和js
 add_action('login_enqueue_scripts', 'mikuclub\setup_login_page_css_and_script');