<?php

namespace mikuclub;

/**
 * 文章相关的钩子和动作
 */

//通过REST API创建文章的时候触发, 为REST插入的新文章添加初始META数据
add_action('rest_insert_post', 'mikuclub\add_custom_post_meta_on_rest_post', 10, 2);

//通过WP user frontend 表单插入和更新文章时触发
add_action('wpuf_add_post_after_insert', 'mikuclub\post_submit_action');
add_action('wpuf_edit_post_after_update', 'mikuclub\post_submit_action');

//在保存文章的时候 过滤文章内容
add_filter( 'content_save_pre' , 'mikuclub\sanitize_post_content' , 10, 1);
