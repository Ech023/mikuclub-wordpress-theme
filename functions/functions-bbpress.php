<?php


/* bbpress 专用函数-------------------------------------------------------------------------- */


/**
 * 论坛新回帖时,
 * 创建新meta数据, 实现 帖子作者 和 被回帖作者 的 未读消息提示的功能
 *
 * @param $reply_id
 * @param $topic_id
 * @param $forum_id
 * @param $anonymous_data
 * @param $reply_author
 * @param $false
 * @param $reply_to
 */
function action_on_bbp_new_reply( $reply_id, $topic_id, $forum_id, $anonymous_data, $reply_author, $false, $reply_to ) {

	//如果回复人不是楼主本人
	if ( get_post_field( 'post_author', $topic_id ) != $reply_author ) {
		//通知帖子作者有未读消息
		update_post_meta( $reply_id, BBPRESS_TOPIC_AUTHOR_READ, 0 );
	}

	//如果是正在回复另外一个回帖 通知被回复的作者有未读消息
	if ( $reply_to > 0 ) {
		update_post_meta( $reply_id, BBPRESS_REPLY_AUTHOR_READ, 0 );
	}
}

/**
 * 有新回复的时候触发
 */
add_action( 'bbp_new_reply', 'action_on_bbp_new_reply', 10, 7 );


/**
 *  获取用户未读的论坛主题回帖数量
 *
 * @return int
 */
function get_user_forum_topic_reply_unread_count() {

	$user_id = get_current_user_id();
	$count   = 0;

	if ( $user_id ) {

		global $wpdb;
		$sql = 'SELECT COUNT(*) FROM mm_posts P, mm_postmeta PM WHERE P.ID = PM.post_id AND PM.meta_key = "' . BBPRESS_TOPIC_AUTHOR_READ . '" AND P.post_status = "publish" AND P.post_type="reply" AND P.post_parent IN (SELECT P2.ID FROM mm_posts P2 WHERE P2.post_author = ' . $user_id . ' AND P2.post_status = "publish" AND P2.post_type="topic")  ';

		$count = $wpdb->get_var( $sql );

		//如果结果错误, 则返回0
		if ( is_null( $count ) ) {
			$count = 0;
		}


	}

	return $count;
}


/**
 * 获取用户未读的论坛回帖被回帖数量
 *
 * @return int
 */
function get_user_forum_reply_reply_unread_count() {

	$user_id = get_current_user_id();
	$count   = 0;

	if ( $user_id ) {

		global $wpdb;
		$sql = 'SELECT COUNT(*) FROM mm_posts P, mm_postmeta PM, mm_postmeta PM2 WHERE P.ID = PM.post_id AND P.ID = PM2.post_id AND PM.meta_key = "' . BBPRESS_REPLY_AUTHOR_READ . '" AND PM2.meta_key = "_bbp_reply_to" AND P.post_status = "publish" AND P.post_type="reply" AND PM2.meta_value IN (SELECT P2.ID FROM mm_posts P2 WHERE P2.post_author = ' . $user_id . ' AND P2.post_status = "publish" AND P2.post_type="reply")  ';

		$count = $wpdb->get_var( $sql );
		//如果结果错误, 则返回0
		if ( is_null( $count ) ) {
			$count = 0;
		}
	}

	return $count;

}

/**
 * 获取用户 收到的主题回帖和回复 总数
 *
 * @param int $user_id
 *
 * @return int
 */
function get_user_bbpress_reply_total_count( $user_id ) {

	global $wpdb;

	$sql = 'SELECT (SELECT COUNT(*) FROM mm_posts P, mm_postmeta PM WHERE P.ID = PM.post_id AND PM.meta_key = "' . BBPRESS_TOPIC_AUTHOR_READ . '" AND P.post_status = "publish" AND P.post_type="reply" AND P.post_parent IN (SELECT P2.ID FROM mm_posts P2 WHERE P2.post_author = ' . $user_id . ' AND P2.post_status = "publish" AND P2.post_type="topic"))   +  (SELECT COUNT(*) FROM mm_posts P, mm_postmeta PM, mm_postmeta PM2 WHERE P.ID = PM.post_id AND P.ID = PM2.post_id AND PM.meta_key = "' . BBPRESS_REPLY_AUTHOR_READ . '" AND PM2.meta_key = "_bbp_reply_to" AND P.post_status = "publish" AND P.post_type="reply" AND PM2.meta_value IN (SELECT P2.ID FROM mm_posts P2 WHERE P2.post_author = ' . $user_id . ' AND P2.post_status = "publish" AND P2.post_type="reply"))  ';

	$output = $wpdb->get_var( $sql );

	//如果结果为空, 则返回0
	if ( empty( $output ) ) {
		$output = 0;
	}


	return $output;
}


/**
 * 获取用户 收到的论坛回复列表
 *
 * @param int $paged
 * @param int $number_per_page
 *
 * @return My_BBpress_Reply[]
 */
function get_bbpress_replies( $paged = 1, $number_per_page = 20 ) {

	$user_id    = get_current_user_id();
	$reply_list = [];

	if ( $user_id ) {

		global $wpdb;

		//计算数据表列 的偏移值 来达到分页效果
		$offset = ( $paged - 1 ) * $number_per_page;

		$sql = '(SELECT P.ID ,P.post_author, P.post_date, P.post_content, P.post_parent FROM mm_posts P WHERE  P.post_status = "publish" AND P.post_type="reply" AND P.post_parent IN (SELECT P2.ID FROM mm_posts P2 WHERE P2.post_author = ' . $user_id . ' AND P2.post_status = "publish" AND P2.post_type="topic"))  UNION (SELECT P3.ID,  P3.post_author, P3.post_date, P3.post_content, P3.post_parent FROM mm_posts P3, mm_postmeta PM WHERE P3.ID = PM.post_id AND PM.meta_key = "_bbp_reply_to" AND P3.post_status = "publish" AND P3.post_type="reply" AND PM.meta_value IN (SELECT P4.ID FROM mm_posts P4 WHERE P4.post_author = ' . $user_id . ' AND P4.post_status = "publish" AND P4.post_type="reply"))  ORDER BY post_date DESC  LIMIT ' . $offset . ',' . $number_per_page;


		$results = $wpdb->get_results( $sql );

		foreach ( $results as $object ) {

			//转换成自定义回复类
			$reply_list[] = new My_BBpress_Reply( $object );
			//删除论坛回复未读标记
			set_bbpres_reply_as_read( $object->ID );
		}
	}

	return $reply_list;
}

/**
 * 删除论坛回复信息未读标记
 *
 * @param int $reply_id
 */
function set_bbpres_reply_as_read( $reply_id ) {


	delete_post_meta( $reply_id, BBPRESS_TOPIC_AUTHOR_READ );
	delete_post_meta( $reply_id, BBPRESS_REPLY_AUTHOR_READ );

	//$sql = 'DELETE M FROM mm_postmeta M JOIN ( SELECT PM.meta_id FROM mm_posts P, mm_postmeta PM WHERE P.ID = PM.post_id AND PM.meta_key = ' . BBPRESS_TOPIC_AUTHOR_READ . ' AND P.post_status = "publish" AND P.post_type="reply" AND P.post_parent IN ( SELECT P2.ID FROM mm_posts P2 WHERE P2.post_author = ' . $user_id . ' AND P2.post_status = "publish" AND P2.post_type="topic")) list ON M.meta_id = list.meta_id';
	//$wpdb->query( $sql );

	//	$sql = 'DELETE M FROM mm_postmeta M JOIN ( SELECT PM.meta_id FROM mm_posts P, mm_postmeta PM, mm_postmeta PM2 WHERE P.ID = PM.post_id AND P.ID = PM2.post_id AND PM.meta_key = ' . BBPRESS_REPLY_AUTHOR_READ . ' AND PM2.meta_key = "_bbp_reply_to" AND P.post_status = "publish" AND P.post_type="reply" AND PM2.meta_value IN (SELECT P2.ID FROM mm_posts P2 WHERE P2.post_author = ' . $user_id . ' AND P2.post_status = "publish" AND P2.post_type="reply")) list ON M.meta_id = list.meta_id';

	//$wpdb->query( $sql );
}

/**
 * 获取最新发布的主题帖列表
 *
 * @param int $posts_per_page
 *
 * @return WP_Post[] 主题帖列表
 */
function get_recent_bbs_topic( $posts_per_page = 8 ) {

	$args = [
		'post_type'      => 'topic',
		'post_status'    => POST_STATUS_PUBLISH,
		'posts_per_page' => $posts_per_page
	];

	return get_posts( $args );

}

/*
//输出主题帖所有回复楼层
function wpup_bbp_list_replies($args = array())
{

    // Reset the reply depth
    bbpress()->reply_query->reply_depth = 0;

    // In reply loop
    bbpress()->reply_query->in_the_loop = true;

    $r = bbp_parse_args($args, array(
        'walker' => null,
        'max_depth' => bbp_thread_replies_depth(),
        'style' => 'ul',
        'callback' => null,
        'end_callback' => null
            ), 'list_replies');

    // Get replies to loop through in $_replies
    $walker = new BBP_Walker_Reply;
    $walker->paged_walk(bbpress()->reply_query->posts, $r['max_depth'], $r['page'], $r['per_page'], $r);

    bbpress()->max_num_pages = $walker->max_pages;
    bbpress()->reply_query->in_the_loop = false;
}

//论坛主题回帖 分页功能
function wpup_custom_pagination($numreplies = '', $pagerange = '', $paged = '', $repliesperpage = '')
{

    /**
     * $pagerange
     * How many pages to display after the current page
     * Used in combination with 'shaw_all' => false
     *//*
    if (empty($pagerange))
    {
        $pagerange = 3;
    }

    /**
     * $numreplies
     * What is the total number of replies in the current topic
     * $numpages
     * Calculate total number of pages to display based on number of replies and replies per page
     *//*
    if ($numreplies != '')
    {
        $numpages = ceil($numreplies / $repliesperpage);
    }

    //assign value of 1 to $paged variable in case it's not passed on
    global $paged;
    if (empty($paged))
    {
        $paged = 1;
    }
*/
/**
 * We construct the pagination arguments to enter into our paginate_links
 * function.
 *//*
    //拼接正确的帖子地址
    $pagelink = get_pagenum_link(1);
    //如果是在第一页 就加斜杠
    if ($paged == 1)
    {
        $pagelink = $pagelink . '/%_%';
    }
    //如果不在第一页 就不加斜杠
    else
    {
        $pagelink = $pagelink . '%_%';
    }


    $pagination_args = array(
        'base' => $pagelink,
        'format' => 'page/%#%',
        'total' => $numpages,
        'current' => $paged,
        'show_all' => False,
        'end_size' => 1,
        'mid_size' => $pagerange,
        'prev_next' => True,
        'prev_text' => __('&lt;'),
        'next_text' => __('&gt;'),
        'type' => 'plain',
        'add_args' => false,
        'add_fragment' => ''
    );

    $paginate_links = paginate_links($pagination_args);

    if ($paginate_links)
    {
        echo "<nav class='custom-pagination'>";
        echo $paginate_links;
        echo "</nav>";
    }
}
*/



