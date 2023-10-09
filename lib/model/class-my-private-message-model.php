<?php

namespace mikuclub;

use mikuclub\constant\Config;

/**
 * 自定义私信模型
 */

/**
 * 基础私信类型
 */
class My_Private_Message_Model
{
	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var int
	 */
	public $sender_id;
	/**
	 * @var int
	 */
	public $recipient_id;
	/**
	 * @var string
	 */
	public $content;
	/**
	 * @var int
	 */
	public $respond;
	/**
	 * @var int
	 */
	public $status;
	/**
	 * @var string
	 */
	public $date;
	/**
	 * @var My_User_Model
	 */
	public $author;

	/**
	 * @param object|null $object
	 */
	function __construct($object = null)
	{
		if (is_object($object))
		{

			$this->id = $object->ID ? intval($object->ID) : 0;

			$this->sender_id = $object->sender_id ?  intval($object->sender_id) : 0;

			$this->recipient_id = $object->recipient_id ? intval($object->recipient_id) : 0;

			$this->content = $object->content ?? '';

			$this->respond = $object->respond ? intval($object->respond) : 0;

			$this->status =  $object->status ? intval($object->status) : 0;

			$this->date = $object->date ?? date(Config::DATE_FORMAT);

			$this->author = get_custom_user($this->sender_id);
		}
	}
}
