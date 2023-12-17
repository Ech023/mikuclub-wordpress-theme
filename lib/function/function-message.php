<?php

namespace mikuclub;

use mikuclub\constant\Comment_Meta;
use mikuclub\constant\Config;
use mikuclub\constant\Expired;
use WP_Error;

/**
 * 私信和回复相关的函数
 */


/**
 * 获取用户收到的未读私信数量
 * @return int 数量
 * @global $wpdb
 */
function get_user_private_message_unread_count()
{
    global $wpdb;

    $result = 0;

    $user_id = get_current_user_id();

    if ($user_id)
    {
        $result = Session_Cache::get(Session_Cache::USER_PRIVATE_MESSAGE_UNREAD_COUNT);
        //如果缓存不存在
        if ($result === null)
        {

            $query = <<<SQL
				SELECT 
                    COUNT(*) 
                FROM 
                    mm_message 
                WHERE 
                    recipient_id = {$user_id} 
                AND 
                    status = 0
SQL;

            $result = intval($wpdb->get_var($query));

            //设置缓存
            Session_Cache::set(Session_Cache::USER_PRIVATE_MESSAGE_UNREAD_COUNT, $result);
        }
    }

    return intval($result);
}


/**
 * 获取用户未读的评论回复数量
 *
 * @return int
 */
function get_user_comment_reply_unread_count()
{

    $result = 0;

    $user_id = get_current_user_id();

    if ($user_id)
    {
        $result = Session_Cache::get(Session_Cache::USER_COMMENT_REPLY_UNREAD_COUNT);
        //如果缓存不存在
        if ($result === null)
        {

            $args = [
                'status'     => 'approve',
                'count'      => true,
                'meta_query' => [
                    'meta_query' => [
                        'relation' => 'AND',
                        [
                            'key'     => Comment_Meta::COMMENT_PARENT_USER_ID,
                            'value'   => $user_id,
                            'compare' => '=',
                            'type'    => 'NUMERIC',
                        ],
                        [
                            'key'     => Comment_Meta::COMMENT_PARENT_USER_READ,
                            'value'   => 0,
                            'compare' => '=',
                            'type'    => 'NUMERIC',
                        ],
                    ],
                ],
            ];

            $result = get_comments($args);
            //设置缓存
            Session_Cache::set(Session_Cache::USER_COMMENT_REPLY_UNREAD_COUNT, $result);
        }
    }

    return intval($result);
}

/**
 * 获取用户未读消息总数
 * @return int;
 */
function get_user_total_unread_count()
{
    return
        get_user_private_message_unread_count() +
        get_user_comment_reply_unread_count() +
        get_wpforo_notification_unread_count();
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
 * @return My_Private_Message_Model[] 私信列表
 * 
 * @global $wpdb
 */
function get_user_private_message_list_grouped($paged = 1, $number_per_page = Config::NUMBER_PRIVATE_MESSAGE_LIST_PER_PAGE)
{

    $result = [];

    $user_id = get_current_user_id();

    //必须拥有当前用户id
    if ($user_id)
    {

        $result = File_Cache::get_cache_meta_with_callback(File_Cache::USER_PRIVATE_MESSAGE_LIST, File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $user_id, Expired::EXP_1_HOUR, function () use ($paged, $number_per_page, $user_id)
        {
            global $wpdb;

            //计算数据表列 的偏移值 来达到分页效果
            $offset = ($paged - 1) * $number_per_page;

            //         //首先需要获取用户收到的所有私信
            //         $query1 = <<<SQL
            //             SELECT 
            //                 * 
            //             FROM 
            //                 mm_message 
            //             WHERE 
            //                 recipient_id = {$user_id} 
            //             ORDER BY 
            //                 ID DESC
            // SQL;
            //         //根据 发件人ID 进行 分组, 然后根据私信ID大小进行倒序 , 以此;来 获取每个发件人最后发送的私信数据
            //         $query2 = <<<SQL
            //             SELECT 
            //                 tmp.* 
            //             FROM 
            //                 ( {$query1} ) tmp 
            //             GROUP BY 
            //                 tmp.sender_id 
            //             ORDER BY 
            //                 tmp.ID DESC 
            //             LIMIT 
            //                 {$offset} , {$number_per_page}
            // SQL;

            $query = <<<SQL
            SELECT 
                mm_message.ID,
                mm_message.sender_id,
                mm_message.recipient_id,
                mm_message.content,
                mm_message.respond,
                mm_message.status,
                mm_message.date
            FROM 
                mm_message
            WHERE
                mm_message.ID IN
                (
                    SELECT
                        max(ID) as ID
                    FROM
                        mm_message
                    WHERE
                        recipient_id = {$user_id} 
                    GROUP BY
                        sender_id
                )
                OR
                mm_message.ID IN
                (
                    /* 获取所有 我发出消息, 但是还未收到回复的 私信方*/
                    SELECT
                        max(ID) as ID
                    FROM
                        mm_message
                    WHERE
                        sender_id = {$user_id}
                    AND
                        recipient_id NOT IN (
                            SELECT
                                sender_id
                            FROM
                                mm_message
                            WHERE
                                recipient_id = {$user_id} 
                            GROUP BY
                                sender_id
                        )
                    GROUP BY
                        recipient_id
                )
            ORDER BY 
                ID DESC 
            LIMIT 
                {$offset} , {$number_per_page}

SQL;

            $query_results = $wpdb->get_results($query);

            //转换成 My_Private_Message_Model
            $result = array_map(function ($object) use ($user_id)
            {
                if (intval($object->sender_id) === $user_id)
                {
                    //如果发件人是用户自己, 说明是未收到回复的私信, 
                    //需要互换收件人和发件人 避免错误
                    $object->sender_id = $object->recipient_id;
                    $object->recipient_id = $user_id;
                    //如果对方还未读消息, 设置一个特殊状态码来注明
                    if (intval($object->status) === 0)
                    {
                        $object->status = 2;
                    }
                    else
                    {
                        $object->status = 3;
                    }
                }

                $model = new My_Private_Message_Model($object);

                return $model;
            }, $query_results);

            // var_dump($result);

            return $result;
        });
    }

    return $result;
}



/**
 * 获取用户和另外一个发件人之间的私信列表
 *
 * @param int $sender_id 发件人ID 可以包括0 (系统消息)
 * @param int $paged 当前页码
 * @param int $number_per_page 每页显示数量
 *
 * @return My_Private_Message_Model[] 私信列表
 * 
 * @global $wpdb
 */
function get_user_private_message_list_with_one_sender($sender_id, $paged = 1, $number_per_page = Config::NUMBER_PRIVATE_MESSAGE_LIST_WITH_ONE_SENDER_PER_PAGE)
{

    global $wpdb;

    $user_id = get_current_user_id();


    $result = [];


    //用户ID 和 收件人 ID 存在 (收件人可以是0)
    if ($user_id && $sender_id >= 0)
    {

        //计算数据表列 的偏移值 来达到分页效果
        $offset = ($paged - 1) * $number_per_page;

        $query = <<<SQL
            SELECT 
                *  
            FROM 
                mm_message 
            WHERE  
                (
                    sender_id = {$sender_id}  
                AND 
                    recipient_id = {$user_id}  
                ) 
            OR
                ( 
                    sender_id = {$user_id} 
                AND 
                    recipient_id = {$sender_id} 
                )
            ORDER BY 
                ID DESC
            LIMIT 
                {$offset} , {$number_per_page}
SQL;

        $query_results = $wpdb->get_results($query);

        //转换成 My_Private_Message_Model
        $result = array_map(function ($object)
        {
            return new My_Private_Message_Model($object);
        }, $query_results);
    }

    return $result;
}




/**
 * 发送私信
 *
 * @param int $recipient_id 收件人id
 * @param string $message_content 私信内容,
 * @param int $respond 是否在回复另外一条私信
 * @param bool $is_system 否是系统消息,
 *
 * @return My_Private_Message_Model|WP_Error
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
        if (in_user_black_list($recipient_id, $sender_id))
        {
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

    //清空私信缓存
    File_Cache::delete_cache_meta(File_Cache::USER_PRIVATE_MESSAGE_LIST, File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $sender_id);
    File_Cache::delete_cache_meta(File_Cache::USER_PRIVATE_MESSAGE_LIST, File_Cache::DIR_USER . DIRECTORY_SEPARATOR . $recipient_id);


    $model =  new My_Private_Message_Model();
    $model->id = $wpdb->insert_id;
    $model->sender_id = $sender_id;
    $model->recipient_id = $recipient_id;
    $model->content = $message_content;
    $model->respond = $respond;
    $model->status =  0;
    $model->date = date(Config::DATE_FORMAT);
    $model->author = get_custom_user($sender_id);

    return $model;
}



/**
 * 更新私信为已读
 *
 * @param int $user_id 收件人ID
 * @return void
 * 
 * @global $wpdb
 */
function set_user_private_message_as_read($user_id)
{

    global $wpdb;

    if ($user_id)
    {
        //更新所有私信状态为已读
        $wpdb->update(
            'mm_message',
            ['status' => 1],
            [
                'recipient_id' => $user_id,
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




/**
 * 删除单个私信或者 根据发件人ID删除
 *
 * @param int $user_id
 * @param int|null $message_id
 * @param int|null $target_user_id
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
