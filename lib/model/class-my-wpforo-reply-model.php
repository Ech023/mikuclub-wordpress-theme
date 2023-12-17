<?php

namespace mikuclub;

/**
 * wpforo论坛回复
 */
class My_Wpforo_Reply_Model
{


    /**
     * @var int
     */
    public $postid;

    /**
     * @var int
     */
    public $parentid;

    /**
     * @var int
     */
    public $forumid;

    /**
     * @var int
     */
    public $topicid;

    /**
     * @var int
     */
    public $userid;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $body;

    /**
     * @var string
     */
    public $created;

    /**
     * @var string
     */
    public $modified;


    /**
     * @var string
     */
    public $post_href;


    /**
     *
     * @var My_User_Model
     */
    public $author;


    /**
     * @param object $result_object
     */
    public function __construct($result_object)
    {

        $this->postid = intval($result_object->postid);
        $this->parentid = intval($result_object->parentid);
        $this->forumid = intval($result_object->forumid);
        $this->topicid = intval($result_object->topicid);
        $this->userid = intval($result_object->userid);

        $this->title    = $result_object->title;
        $this->body    = $result_object->body;
        $this->created    = $result_object->created;
        $this->modified    = $result_object->modified;

        if (function_exists('wpforo_post'))
        {
            $this->post_href  = wpforo_post($this->postid, 'url');
        }
        $this->author = get_custom_user($this->userid);
    }
}
