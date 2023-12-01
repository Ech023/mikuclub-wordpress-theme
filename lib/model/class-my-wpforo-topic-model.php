<?php

namespace mikuclub;

/**
 * wpforo论坛主题
 */
class My_Wpforo_Topic_Model
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $post_date;

    /**
     * @var string
     */
    public $post_title;

    /**
     * @var string
     */
    public $post_href;

    /**
     *
     * @var int
     */
    public $post_author_id;

    /**
     *
     * @var My_User_Model
     */
    public $post_author;

    /**
     * @var int
     */
    public $post_views;

    /**
     * 回帖数量
     * @var int
     */
    public $post_replay_number;

    /**
     * @param object $result_object
     */
    public function __construct($result_object)
    {

        $this->id           = intval($result_object->topicid);
        $this->post_date    = $result_object->modified;
        $this->post_title = $result_object->title;
        $this->post_href  = wpforo_topic($this->id, 'url');

        $this->post_author_id = intval($result_object->userid);
        $this->post_author = get_custom_user($this->post_author_id);

        $this->post_views =  intval($result_object->views);
        $this->post_replay_number =  intval($result_object->posts);
    }
}
