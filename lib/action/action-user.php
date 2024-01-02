<?php

namespace mikuclub;

use mikuclub\constant\Expired;
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

/**
 * 用户更新信息后触发
 * 
 * @param int   $user_id      The ID of the user that was just updated.
 * @param array $userdata     The array of user data that was updated.
 * @param array $userdata_raw The unedited array of user data that was updated.
 */
add_action(
    'wp_update_user',
    function ($user_id, $userdata, $userdata_raw)
    {
        //清空该用户的缓存
        File_Cache::delete_user_cache_meta_by_user_id($user_id);
    },
    10,
    3
);

/**
 * 更改默认登陆cookie的有效时间
 * @param int $expiration
 * @param int $user_id
 * @param bool $remember
 * @return int
 */
add_filter(
    'auth_cookie_expiration',
    function ($expiration, $user_id, $remember)
    {
        return Expired::EXP_6_MONTHS;
    },
    10,
    3
);





//替换默认头像img
// add_filter('get_avatar', 'mikuclub\replace_default_avatar', 99999, 5);

//替换默认头像获取函数
add_filter('get_avatar_data', 'mikuclub\replace_default_avatar_data', 99999, 2);


// add_filter('pre_get_avatar_data', 'open_social_pre_get_avatar_data', 99999, 2);
// function open_social_pre_get_avatar_data($args, $id_or_email)
// {
//     $avatar_option = apply_filters('pre_option_show_avatars', '', 100);
//     if (!empty($avatar_option)) return $args;
//     $args['default'] = get_option('avatar_default', 'mystery');
//     return $args;
// }
