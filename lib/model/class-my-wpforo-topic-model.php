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
     * @param object $result_object
     */
    public function __construct($result_object)
    {

        $this->id           = intval($result_object->topicid);
        $this->post_date    = $result_object->modified;
        $this->post_title = $result_object->title;
        $this->post_href  = wpforo_topic($this->id, 'url');
    }
}
