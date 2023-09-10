<?php

namespace mikuclub;

/**
 * 自定义私信模型
 */


if (!class_exists('My_Private_Message'))
{

	/**
	 * 基础私信类型
	 */
	class My_Private_Message
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
		 * @var My_System_User|My_User|null
		 */
		public $author;

		/**
		 * @param array<string, mixed> $message
		 */
		function __construct($message)
		{

			$this->id = $message['ID'] ?? 0;

			$this->sender_id = $message['sender_id'] ?? 0;

			$this->recipient_id = $message['recipient_id'] ?? 0;

			$this->content = $message['content'] ?? '';

			$this->respond = $message['respond'] ?? 0;

			$this->status = $message['status'] ?? 0;

			$this->date = $message['date'] ?? date(Config::DATE_FORMAT);
		}
	}
}
