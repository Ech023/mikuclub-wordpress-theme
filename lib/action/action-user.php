<?php

namespace mikuclub;

use mikuclub\constant\User_Meta;
use WP_Post;
use WP_REST_Request;

/**
 * 用户相关的钩子和动作
 */



/**
 * 通过API上传头像的时候触发 更新头像数据 
 *
 * @param WP_Post $attachment Inserted or updated attachment object.
 * @param WP_REST_Request $request Request object.
 * @return void
 */
add_action(
    'rest_after_insert_attachment',
    function ($attachment, $request)
    {
        //如果是上传更换新头像 的 动作
        if ($request->has_param(User_Meta::ACTION_UPDATE_AVATAR_BY_API))
        {
            $user_id = intval($attachment->post_author);
            $attachment_id = $attachment->ID;
            update_user_custom_avatar($user_id, $attachment_id);
        }
    },
    10,
    2
);
