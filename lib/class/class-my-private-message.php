<?php

namespace mikuclub;

/**
 * 自定义私信模型
 */


if ( ! class_exists( 'My_Private_Message' )



) {

	/**
	 * 基础私信类型
	 */
	 class My_Private_Message {

		public $id = '';
		public $sender_id = '';
		public $recipient_id = '';
		public $content = '';
		public $respond = 0;
		public $status = 0;
		public $date;

		public $author = '';


		function __construct( $message ) {

			if ( isset($message['ID'] )) {
				$this->id = $message['ID'];
			}
			if ( isset($message['sender_id'] )) {
				$this->sender_id = $message['sender_id'];
			}
			if ( isset($message['recipient_id'] )) {
				$this->recipient_id = $message['recipient_id'];
			}
			if ( isset($message['content'] )) {
				$this->content = $message['content'];
			}
			if ( isset($message['respond'] )) {
				$this->respond = $message['respond'];
			}
			if ( isset($message['status'] )) {
				$this->status = $message['status'];
			}
			if ( isset($message['date'] )) {
				$this->date = $message['date'];
			} //未设置时间的话, 获取当前时间
			else {
				$this->date = date( Config::DATE_FORMAT );
			}
		}
	}



}