<?php

namespace mikuclub;

use WP_User;

/**
 * 标准用户对象
 */
class My_User_Model
{
    /**
     * @var int
     */
    public $id;
    /**
     * @var string
     */
    public $user_login;
    /**
     * @var string
     */
    public $display_name;
    /**
     * @var string
     */
    public $user_href;
    /**
     * @var string
     */
    public $user_image;
    /**
     * @var string
     */
    public $user_description;

    /**
     * @var string
     */
    public $user_level;

    /**
     * @var array<int, array<string, mixed>>
     */
    public $user_badges = [];

    /**
     * My_User constructor.
     * @param WP_User|null $user
     */
    function __construct($user = null)
    {
        if ($user instanceof WP_User)
        {
            $this->id               = $user->ID;
            $this->user_login       = $user->user_login;
            $this->display_name     = $user->display_name;
            $this->user_href        = get_author_posts_url($user->ID);
            $this->user_image       = get_my_user_avatar($user->ID);
            $this->user_description = get_user_meta($user->ID, 'description', true);

            $this->user_level  = get_user_level($user->ID);
            $this->user_badges = get_user_badges($user->ID);
        }
    }

    /**
     * 创建系统通知用账号
     *
     * @return My_User_Model
     */
    public static function create_system_user()
    {

        $model = new My_User_Model();
        $model->id = 0;
        $model->user_login = "系统通知";
        $model->display_name = "系统通知";
        $model->user_description = "此为系统自动消息, 请勿回复";
        $model->user_image  = get_home_url() . '/img/网站系统消息头像.jpg';
        return $model;
    }

    /**
     * 创建已注销的用户
     *
     * @return My_User_Model
     */
    public static function create_deleted_user()
    {

        $model = new My_User_Model();
        $model->id = 0;
        $model->user_login = "unknown";
        $model->display_name = "该用户已注销";
        $model->user_description = "该用户已注销";
        $model->user_image  = get_my_user_default_avatar();
        return $model;
    }
}
