<?php
namespace mikuclub;

/**
 * 获取用户收到的未读私信数量
 * @return int 数量
 */
function get_user_private_message_unread_count()
{

	$user_id   = get_current_user_id();
	$cache_key = 'user_message_unread_count_' . $user_id;

	$count = 0;

	//必须拥有当前用户id
	if ($user_id)
	{

		global $wpdb;
		$query = " SELECT COUNT(*) FROM mm_message WHERE recipient_id = {$user_id} AND status  = 0  ";
		$count = $wpdb->get_var($query);

		//如果错误 重设为0
		if (!$count)
		{
			$count = 0;
		}
	}

	return $count;
}

/**
 * 获取用户收到的收到的私信列表
 *
 * 会根据发件人ID进行分类, 在列表中只会存在该发件人最后发送的一条信息
 * 会为每条私信 加上作者信息
 *
 * @param int $paged 当前页码
 * @param int $number_per_page 每页显示数量
 *
 * @return My_Private_Message[] 私信列表
 */
function get_user_private_message_list_grouped($paged = 1, $number_per_page = 20)
{

	global $wpdb;
	$user_id = get_current_user_id();
	//计算数据表列 的偏移值 来达到分页效果
	$offset = ($paged - 1) * $number_per_page;

	$message_list = [];

	//必须拥有当前用户id
	if ($user_id)
	{

		//首先需要获取用户收到的所有私信
		//需要设置limit 100000000 , 不然group 会不正确
		$sql_to_get_user_messages = " SELECT *  FROM mm_message WHERE recipient_id = {$user_id} ORDER BY ID DESC limit 10000000000 ";
		//根据 发件人ID 进行 分组, 然后根据私信ID大小进行倒序 , 以此;来 获取每个发件人最后发送的私信数据
		$query = "SELECT tmp.* FROM ( {$sql_to_get_user_messages} ) tmp GROUP BY tmp.sender_id ORDER BY tmp.ID DESC LIMIT {$offset} ,  {$number_per_page}";

		$result = $wpdb->get_results($query, ARRAY_A);


		$is_updated = false;

		if ($result)
		{
			//转换成自定义私信类
			foreach ($result as $message)
			{
				$my_private_message = new My_Private_Message($message);
				//获取作者信息
				$my_private_message->author = get_custom_author($my_private_message->sender_id);
				$message_list[]             = $my_private_message;

				//如果存在未读私信
				if ($my_private_message->status == 0 && !$is_updated)
				{
					//更新所有私信为已读
					set_user_private_message_as_read($user_id);
					//避免重复运行
					$is_updated = true;
				}
			}
		}
	}

	return $message_list;
}


/**
 * 获取用户和另外一个发件人之间的私信列表
 *
 * @param int $sender_id 发件人ID 可以包括0 (系统消息)
 * @param int $paged 当前页码
 * @param int $number_per_page 每页显示数量
 *
 * @return My_Private_Message[] 私信列表
 */
function get_user_private_message_list_with_one_sender($sender_id, $paged = 1, $number_per_page = 20)
{


	global $wpdb;
	$user_id = get_current_user_id();
	//计算数据表列 的偏移值 来达到分页效果
	$offset = ($paged - 1) * $number_per_page;

	$message_list = [];

	$sender_author = get_custom_author($sender_id);

	//检测用户ID 和 检测 收件人 ID (可以是0)
	if ($user_id && $sender_id >= 0)
	{

		$where  = " (sender_id = {$sender_id}  AND recipient_id = {$user_id}  ) OR ( sender_id = {$user_id} AND recipient_id = {$sender_id} ) ";
		$query  = "SELECT *  FROM mm_message WHERE  {$where}  ORDER BY  ID  DESC LIMIT {$offset} , {$number_per_page}";
		$result = $wpdb->get_results($query, ARRAY_A);

		if ($result)
		{
			//转换成自定义私信类
			foreach ($result as $message)
			{
				$my_private_message = new My_Private_Message($message);
				//获取作者信息
				$my_private_message->author = $sender_author;
				$message_list[] = $my_private_message;
			}
		}
	}


	return $message_list;
}

/**
 * 设置私信为已读
 *
 * @param int $recipient_id 收件人ID
 */
function set_user_private_message_as_read($recipient_id)
{

	if ($recipient_id >= 0)
	{

		global $wpdb;
		//更新所有私信状态为已读
		$wpdb->update(
			'mm_message',
			['status' => 1],
			[
				'recipient_id' => $recipient_id,
			],
			[
				'%d'
			],
			[
				'%d',
			]
		);
	}
}


/**发送私信
 *
 * @param int $recipient_id 收件人id
 * @param string $message_content 私信内容,
 * @param int $respond 是否在回复另外一条私信
 * @param bool $is_system 否是系统消息,
 *
 * @return My_Private_Message|WP_Error
 */
function send_private_message($recipient_id, $message_content, $respond = 0, $is_system = false)
{

	global $wpdb;


	//如果是系统消息的情况, 设置sender_id 为0
	if ($is_system)
	{
		$sender_id = 0;
	}
	else
	{
		$sender_id = get_current_user_id();
		//如果发件人和收件人id一样报错
		if ($sender_id == $recipient_id)
		{
			return new WP_Error(400, __FUNCTION__ . ' : 发件人和收件人不能是同个人');
		}

		//检测发件人是否在收件人的黑名单里
		if(in_user_black_list($recipient_id, $sender_id)){
			return new WP_Error(400, __FUNCTION__ . ' : 你已被收件人拉黑', '无法发送 你已被收件人拉黑');
		}


	}


	$new_message = [
		'sender_id'    => $sender_id,
		'recipient_id' => $recipient_id,
		'content'      => $message_content,
		'respond'      => $respond,
		'status'       => 0,
		'date'         => date(Config::DATE_FORMAT),
	];

	$result_sql = $wpdb->insert('mm_message', $new_message, ['%d', '%d', '%s', '%d', '%d', '%s']);

	//如果插入错误
	if (!$result_sql)
	{
		return new WP_Error('500', __FUNCTION__ . ' : ' . $wpdb->last_error);
	}


	$my_message =  new My_Private_Message($new_message);
	//添加id 和 作者信息
	$my_message->id = $wpdb->insert_id;
	$my_message->author = get_custom_author($sender_id);

	return $my_message;
}


/**
 * 删除私信
 *
 * @param int $user_id
 * @param int $message_id
 * @param int $target_user_id
 *
 * @return bool 是否删除成功
 */
function delete_private_message($user_id,  $message_id, $target_user_id)
{

	global $wpdb;

	$result = false;

	//如果用户id存在
	if ($user_id)
	{

		//如果有私信ID
		if ($message_id)
		{
			//只删除对应的私信
			$result = $wpdb->delete(
				'mm_message',
				[
					'ID'        => $message_id,
					'sender_id' => $user_id, //第二个参数是为了 限制用户只能删除自己的私信
				],
				[
					'%d',
					'%d',
				]
			);
		}
		//如果有目标用户ID, 删除所有目标发给当前用户的私信 (不包含用户自己发出的私信)
		else if ($target_user_id)
		{
			//只删除对应的私信
			$result = $wpdb->delete(
				'mm_message',
				[
					'sender_id' => $target_user_id, 
					'recipient_id' =>  $user_id,
				],
				[
					'%d',
					'%d',
				]
			);
		}
	}

	return $result == true;
}
