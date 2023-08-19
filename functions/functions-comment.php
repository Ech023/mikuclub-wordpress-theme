<?php


/**
 * 获取评论子回复数量
 *
 * @param int $comment_id
 *
 * @return int 回复数
 */
function get_comment_reply_count($comment_id)
{


    $count = get_comment_meta($comment_id, COMMENT_REPLIES_COUNT, true);
    //如果键值不存在
    if ($count === '')
    {
        //重新计算
        $count = update_comment_reply_count($comment_id);
    }

    return $count;
}

/**
 * 重新获取评论回复数
 *
 * @param int $comment_id
 *
 * @return int 回复数
 */
function update_comment_reply_count($comment_id)
{

    $args = [
        'status' => 'approve',
        'count' => true,
        'parent' => $comment_id
    ];
    //查询评论数
    $count = get_comments($args);
    update_comment_meta($comment_id, COMMENT_REPLIES_COUNT, $count);

    return $count;
}

/**
 * 获取 评论回复 ID 数组
 *
 * @param int $comment_id
 *
 * @return string[] id数组
 */
function get_comment_reply_ids($comment_id)
{


    $args = [
        'parent' => $comment_id,
        'hierarchical' => 'flat', //包括间接回复 , 添加到列表结尾
        'fields' => 'ids',
        'status' => 'approve',
    ];

    return get_comments($args);
}


/**
 * 获取回复我的评论
 *
 * @param int $paged
 * @param int $number_per_page
 *
 * @return My_Comment_Reply[]
 */
function get_comment_replies($paged = 1, $number_per_page = 20)
{

    $comment_replies = [];

    $user_id = get_current_user_id();
    if ($user_id)
    {

        $args = [
            'paged' => $paged,
            'meta_key' => COMMENT_PARENT_USER_ID,
            'meta_value' => get_current_user_id(),
            'status' => 'approve',
            'number' => $number_per_page,
        ];

        $results = get_comments($args);

        //遍历结果
        foreach ($results as $comment)
        {
            //把结果转换成自定义评论回复类
            $comment_replies[] = new My_Comment_Reply($comment);
            //把所有请求过的评论更新为已读
            set_comment_as_read($comment->comment_ID);
        }
    }

    return $comment_replies;
}


/**
 * 更新评论状态为已读
 *
 * @param $comment_id
 */
function set_comment_as_read($comment_id)
{
    update_comment_meta($comment_id, COMMENT_PARENT_USER_READ, 1, 0);
}


/**
 * 新建评论时触发
 * 添加自定义评论元数据
 * 如果评论需要@作者, 添加meta通知
 * 获取评论的回复的父评论id, 父评论作者id和最顶层的父评论id
 *
 * @param $comment_id
 * @param WP_Comment $commentdata
 */
function action_on_insert_comment($comment_id, $commentdata)
{

    $post_id = $commentdata->comment_post_ID;


    //增加用户评论数统计
    add_user_comment_count($commentdata->user_id);
    //更新文章评论数统计
    update_post_comments($post_id);


    //如果勾选了通知作者 并且是一级评论
    if (isset($_POST['notify_author']) && $commentdata->comment_parent == 0)
    {

        //添加原文章作者id数据, 设置为作者未读评论
        $post_author_id = get_post_field('post_author', $post_id);
        update_comment_meta($comment_id, COMMENT_PARENT_USER_ID, $post_author_id);
        update_comment_meta($comment_id, COMMENT_PARENT_USER_READ, 0);
    }
    //如果是二级评论 (正在回复另外一个评论)
    else if ($commentdata->comment_parent > 0)
    {

        $parent_comment = get_comment($commentdata->comment_parent);
        $parent_user_id = $parent_comment->user_id;
        $parent_comment_id = $parent_comment->comment_ID;

        //添加父评论ID
        update_comment_meta($comment_id, 'parent_comment_id', $parent_comment_id);
        //添加父评论作者ID
        update_comment_meta($comment_id, COMMENT_PARENT_USER_ID, $parent_user_id);
        //通知父评论作者 未读回复
        update_comment_meta($comment_id, COMMENT_PARENT_USER_READ, 0);
        //更新评论回复数
        update_comment_reply_count($parent_comment_id);


        //如果父评论还有他自己的父评论
        while ($parent_comment->comment_parent > 0)
        {
            $parent_comment = get_comment($parent_comment->comment_parent);
        }
        //获取顶级父评论的id
        $top_parent_comment_id = $parent_comment->comment_ID;
        //添加顶级父评论的ID
        update_comment_meta($comment_id, 'top_parent_comment_id', $top_parent_comment_id);
    }
}

//默认+rest api添加评论 都会触发
add_action('wp_insert_comment', 'action_on_insert_comment', 10, 2);


/**
 * 通过REST API添加新评论前进行用户资格检测
 *
 * @param array           $prepared_comment The prepared comment data for `wp_insert_comment`.
 * @param WP_REST_Request $request          The current request.
 * @return array|WP_Error 将要插入的评论 或者 WP_Error 错误
 */
function action_on_rest_preprocess_comment($prepared_comment, $request)
{

    $comment_post_id =  $prepared_comment['comment_post_ID'] ?? null;
    //如果相关联的文章id存在
    if ($comment_post_id)
    {
        $user_id = get_current_user_id();
        $post_author_id = get_post_field('post_author', $comment_post_id);

        //如果当前用户是封禁用户
        if (current_user_is_regular() === false)
        {
            $prepared_comment = new WP_Error(400, __FUNCTION__ . ' : 该账号已被封禁',  '无法发送 该账号已被封禁');
        }
        //如果评论人已经被文章作者拉黑
        else if (in_user_black_list($post_author_id, $user_id))
        {
            $prepared_comment = new WP_Error(400, __FUNCTION__ . ' : 你已被投稿作者拉黑',  '无法发送 你已被投稿作者拉黑');
        }
    }

    return $prepared_comment;
}
add_filter('rest_preprocess_comment', 'action_on_rest_preprocess_comment', 10, 2);


/**
 * @param string $comment_content
 * @param int $comment_post_id
 * @param int $comment_parent
 *
 * @return My_Comment | mixed
 */
function insert_custom_comment($comment_content, $comment_post_id, $comment_parent = 0)
{

    $user = wp_get_current_user();

    $result = false;

    if ($user)
    {

        $args = [
            'comment_author' => $user->display_name,
            'comment_author_email' => $user->user_email,
            'comment_author_url' => '',
            'comment_content' => $comment_content,
            'comment_parent' => $comment_parent,
            'comment_post_ID' => $comment_post_id,
            'user_id' => $user->ID,

        ];

        //获取文章作者ID
        $post_author_id = get_post_field('post_author', $comment_post_id);
        //如果评论人已经被文章作者拉黑
        if (in_user_black_list($post_author_id, $user->ID))
        {
            return new WP_Error(400, __FUNCTION__ . ' : 你已被投稿作者拉黑',  '无法发送 你已被投稿作者拉黑');
        }

        $result = wp_new_comment($args, true);
        //$result = wp_handle_comment_submission($args);

        if ($result && !is_wp_error($result))
        {
            $result = new My_Comment(get_comment($result));
        }
    }

    return $result;
}


/**
 * 获取文章的评论列表
 *
 * @param int $post_id
 * @param int $offset
 * @param int $number
 *
 * @return My_Comment[]
 */
function get_comment_list($post_id, $offset, $number = 30)
{

    $comment_list = [];

    //如果是第一页评论
    //添加 高点赞的评论
    if (empty($offset))
    {

        //需要显示的点赞评论数
        $number_liked_comment = 3;

        $args_liked_comment = [
            'post_id' => $post_id,
            'status' => 'approve',
            'type' => 'comment',
            'number' => $number_liked_comment,
            'hierarchical' => 'threaded',

            //根据点赞数进行排序
            'meta_query' => [
                'relation' => 'OR',
                'comment_likes' =>
                [
                    'key' => COMMENT_LIKES,
                    'value'   => 1,
                    'compare' => '>=',
                    'type' => 'NUMERIC',
                ],
                'comment_not_likes' =>
                [
                    'key' => COMMENT_LIKES,
                    'compare' => 'NOT EXISTS',
                    'type' => 'NUMERIC',
                    'value' => ''
                ],
            ],
            //根据点赞数排序, 没有点赞数 则用id排序
            'orderby' => [
                'comment_not_likes' => 'DESC', 'comment_ID' => 'DESC'
            ],

            //根据点赞数进行排序
            /* 'meta_query' => [
                 'relation' => 'OR',
                 'comment_likes' =>
                     [
                         'key' => COMMENT_LIKES,
                         'value'   => '0',
                         'compare' => '>=',
                         'type' => 'NUMERIC',
                     ],
             ],
             'orderby' => [
                 'comment_likes' => 'DESC'
             ],*/
        ];

        $results_liked_comment = get_comments($args_liked_comment);

        if ($results_liked_comment)
        {
            foreach ($results_liked_comment as $comment)
            {
                $comment_list[] = new My_Comment($comment);
            }
        }
    }

    $args_normal_comment = [
        'post_id' => $post_id,
        'offset' => $offset,
        'status' => 'approve',
        'type' => 'comment',
        'number' => $number,
        'hierarchical' => 'threaded',
        'orderby' => [
            'comment_ID' => 'DESC'
        ],
    ];
    $results_normal_comment = get_comments($args_normal_comment);

    if ($results_normal_comment)
    {
        foreach ($results_normal_comment as $comment)
        {
            $comment_list[] = new My_Comment($comment);
        }
    }


    return $comment_list;
}


/**
 * 获取评论点赞次数
 *
 * @param int $comment_id
 *
 * @return int 点赞次数
 */
function get_comment_likes($comment_id)
{
    $count = get_comment_meta($comment_id, COMMENT_LIKES, true);;
    if ($count === '')
    {
        $count = 0;
    }

    return $count;
}

/**
 * 增加评论点赞数
 *
 * @param int $comment_id
 * @return int
 */
function add_comment_likes($comment_id)
{
    $count = get_comment_likes($comment_id);
    $count++;
    update_comment_meta($comment_id, COMMENT_LIKES, $count);

    return $count;
}

/**
 * 减少评论点赞数
 *
 * @param int $comment_id
 * @return int
 */
function delete_comment_likes($comment_id)
{
    $count = get_comment_likes($comment_id);
    //如果评论点赞数大于0
    if ($count > 0)
    {
        $count--;
    }
    else
    {
        $count = 0;
    }

    update_comment_meta($comment_id, COMMENT_LIKES, $count);

    return $count;
}
