<?php

namespace mikuclub;

use WP_Comment;

/**
 * 评论相关的钩子和动作
 */


//在插入评论之前 进行权限检测
add_filter('rest_preprocess_comment', 'mikuclub\check_pre_insert_comment', 10, 2);

//在插入新评论后 添加自定义元数据
add_action('wp_insert_comment', 'mikuclub\add_custom_comment_meta', 10, 2);

//获取评论作者的真实IP
add_filter('pre_comment_user_ip', 'mikuclub\get_comment_author_real_ip');

add_action('rest_delete_comment', 'mikuclub\action_on_rest_delete_comment',10, 3);
