<?php

namespace mikuclub;

use mikuclub\constant\User_Meta;

/**
 * 用户积分管理
 */

class User_Point
{

    const LV0 = 0;
    const LV1 = 1000;
    const LV2 = 10000;
    const LV3 = 30000;
    const LV4 = 100000;
    const LV5 = 300000;
    const LV6 = 1000000;

    const POINT_FOR_NEW_COMMENT = 100;
    const POINT_FOR_NEW_POST = 1000;

    /**
     * 获取用户积分字符串
     *
     * @param int $user_id
     * @return string
     */
    public static function get_point($user_id)
    {
        $result = '';

        if ($user_id)
        {
            $user_point = intval(get_user_meta($user_id, User_Meta::USER_POINT, true));
            $result = number_format($user_point, 0, '.', ',');
        }

        return $result;
    }

    /**
     * 获取用户积分等级
     * 
     * @param int $user_id
     * @return string
     */
    public static function get_point_level($user_id)
    {
        $result = '';

        if ($user_id)
        {
            $result = 'Lv';
            $user_point = intval(get_user_meta($user_id, User_Meta::USER_POINT, true));
            if ($user_point > static::LV6)
            {
                $result .= '6';
            }
            else if ($user_point > static::LV5)
            {
                $result .= '5';
            }
            else if ($user_point > static::LV4)
            {
                $result .= '4';
            }
            else if ($user_point > static::LV3)
            {
                $result .= '3';
            }
            else if ($user_point > static::LV2)
            {
                $result .= '2';
            }
            else if ($user_point > static::LV1)
            {
                $result .= '1';
            }
            else
            {
                $result .= '0';
            }
        }

        return $result;
    }

    /**
     * 增加用户的积分
     * 
     * @param int $user_id
     * @param int $value
     * @return void
     */
    public static function add_point($user_id, $value)
    {

        if ($user_id)
        {
            $user_point = intval(get_user_meta($user_id, User_Meta::USER_POINT, true));
            $user_point += $value;

            update_user_meta($user_id, User_Meta::USER_POINT, $user_point);
        }
    }
}
