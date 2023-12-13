<?php

namespace mikuclub;

/**
 * 论坛相关的钩子和动作
 */


//输出旧版帖子附件图片
add_filter('wpforo_content_after', 'mikuclub\wpforo_default_attachment_image_embed', 11);

//自定义 wpforo 文本编辑器按钮
add_filter('wpforo_editor_settings', 'mikuclub\wpforo_custom_editors', 2, 2);

//修正 wpforo 论坛消息通知的链接域名
add_filter('wpforo_notifications_list', 'mikuclub\fix_wpforo_notification_list_link', 10, 1);

//在插入新主题帖子后 更新对应的附件图片元数据
add_action(
    'wpforo_after_add_topic',
    /**
     * @param array<string,mixed> $args
     * [
     * 	'body' => 内容,
     * 	'topicid' => 主题ID,
     * 	'forumid' => 论坛板块ID,
     * 	'first_postid' => 帖子ID,
     * ]
     * @param array<string,mixed> $forum
     * @return void
     */
    function ($args, $forum)
    {
        update_topic_attach_meta($args);
    },
    10,
    2
);

//在更新主题帖子后 更新对应的附件图片元数据
add_action(
    'wpforo_after_edit_topic',
    /**
     * @param array<string,mixed> $a
     * [
     * 	'body' => 内容,
     * 	'topicid' => 主题ID,
     * 	'forumid' => 论坛板块ID,
     * 	'first_postid' => 帖子ID,
     * ]
     * @param array<string,mixed> $args
     * @param array<string,mixed> $forum
     * @return void
     */
    function ($a, $args, $forum)
    {
        update_topic_attach_meta($a);
    },
    10,
    3
);
