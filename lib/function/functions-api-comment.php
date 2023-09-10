<?php

namespace mikuclub;

use stdClass;
use WP_Error;
use WP_REST_Request;

/**
 * 获取用户未读的评论数量
 *
 * @param WP_REST_Request $data
 *
 * @return int
 */
function api_get_user_unread_comment_reply_count($data)
{
	return get_user_unread_comment_reply_count();
}


/**
 * API获取评论回复
 *
 * @param WP_REST_Request $data ['paged' => 页数, number' => 每页数据数量]
 *
 * @return My_Comment_Reply[]|WP_Error
 */
function api_get_comment_replies($data)
{

	//默认参数
	$paged  = 1;
	$number = 20;

	if (isset_numeric($data['paged']))
	{
		$paged = $data['paged'];
	}
	else if (isset($data['paged']) && !is_numeric($data['paged']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : paged 参数错误');
	}

	if (isset_numeric($data['number']))
	{
		$number = $data['number'];
	}
	else if (isset($data['number']) && !is_numeric($data['number']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : number 参数错误');
	}


	return get_comment_replies($paged, $number);
}


/**
 * 删除评论
 *
 * @param WP_REST_Request $data
 *
 * @return bool|WP_Error
 */
function api_delete_comment($data)
{

	if (!isset_numeric($data['id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : id参数错误');
	}

	//当前登陆用户ID
	$user_id = get_current_user_id();

	//获取评论主体
	$comment_id = $data['id'];
	$comment =  get_comment($comment_id);

	//获取文章作者ID
	$post_id = intval($comment->comment_post_ID);
	$post_author_id = intval(get_post_field('post_author', $post_id));

	//评论人ID
	$author_id  = $comment->user_id;

	//如果是高级用户和当前文章的作者
	$is_premium_user_and_post_author = current_user_can_publish_posts() && $user_id === $post_author_id;

	//检测权限, 只有管理员 和 评论作者自己 有权限删除评论
	if (
		//如果是管理员
		current_user_is_admin()
		||
		//如果是高级用户和文章的作者ID
		$is_premium_user_and_post_author
		||
		//如果是评论人本ID
		$user_id == $author_id
	)
	{
		//如果是文章作者就只把评论移到回收站, 其他情况 完全删除
		$result = wp_delete_comment($comment_id, $is_premium_user_and_post_author ? false : true);
		if (!$result)
		{
			$result = new WP_Error(500, __FUNCTION__ . ' : 删除失败');
		}
	}
	else
	{
		$result = new WP_Error(500, __FUNCTION__ . ' : 权限不足');
	}

	return $result;
}

/**
 * API 获取文章的评论列表
 * @param WP_REST_Request $data
 *
 * @return My_Comment[]|WP_Error
 */
function api_get_comment_list($data)
{

	if (!isset_numeric($data['post_id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : post_id 参数错误');
	}
	if (!isset_numeric($data['offset']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : offset 参数错误');
	}
	if (isset($data['number']) && !is_numeric($data['number']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : number 参数类型错误');
	}

	if (isset($data['number']))
	{
		$comment_list = get_comment_list($data['post_id'], $data['offset'], $data['number']);
	}
	else
	{
		$comment_list = get_comment_list($data['post_id'], $data['offset']);
	}

	return $comment_list;
}


/**
 * 增加评论点赞次数
 *
 * @param WP_REST_Request $data
 *
 * @return int|WP_Error
 **/
function api_add_comment_likes($data)
{

	//如果缺少必要参数
	if (!isset_numeric($data['comment_id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : comment_id 参数错误');
	}
	return add_comment_likes($data['comment_id']);
}

/**
 * 减少评论点赞次数
 *
 * @param WP_REST_Request $data
 *
 * @return int|WP_Error
 **/
function api_delete_comment_likes($data)
{

	//如果缺少必要参数
	if (!isset_numeric($data['comment_id']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : comment_id 参数错误');
	}
	return delete_comment_likes($data['comment_id']);
}




/**
 * 创建评论
 * @param WP_REST_Request $data
 *
 * @return bool|mixed|My_Comment|WP_Error
 */
function api_insert_custom_comment($data)
{

	if (!isset($data['comment_post_ID']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : comment_post_ID 参数错误');
	}
	if (!isset($data['comment_content']) &&  trim($data['comment_content']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : comment_content 缺少参数');
	}
	if (isset($data['comment_parent']) && !is_numeric($data['comment_parent']))
	{
		return new WP_Error(400, __FUNCTION__ . ' : comment_parent 参数错误');
	}

	$comment_parent = 0;
	if (isset($data['comment_parent']))
	{
		$comment_parent =  $data['comment_parent'];
	}

	$comment_content = trim($data['comment_content']);

	return insert_custom_comment($comment_content, $data['comment_post_ID'], $comment_parent);
}

/**
 * API在回复中 添加自定义 数据
 *
 * @param WP_REST_Request $data
 *
 * @return array<string, mixed>
 */
function api_add_custom_comment_metadata($data)
{

	$output = [];
	//只有在自己是顶级评论的时候 才需要计数子回复数量
	if ($data['parent'] == 0)
	{

		$comment_id = $data['id'];

		//如果有子评论
		if (get_comment_reply_count($comment_id) > 0)
		{
			//获取子评论 id数组
			$output['comment_reply_ids'] = get_comment_reply_ids($comment_id);
		}
	}

	//获取用户头像
	$output['user_image'] = get_my_user_avatar($data['author']);


	//如果为空 创建 一个 空对象返回,  不然返回空数组 app端会崩溃
	// if (empty($output))
	// {
	// 	$output = new stdClass();
	// }

	return $output;
}




/**
 * 在API中给comment添加自定义meta元数据支持
 * 
 * @return void
 **/
function register_custom_comment_metadata()
{

	$integer_meta_args = [
		'type'         => 'integer',
		'description'  => 'custom integer field',
		'single'       => true,
		'show_in_rest' => true,
	];

	register_meta('comment', Comment_Meta::COMMENT_PARENT_USER_ID, $integer_meta_args);
	register_meta('comment', Comment_Meta::COMMENT_PARENT_USER_READ, $integer_meta_args);
}



/**
 * 注册自定义 api 接口
 * 
 * @return void
 */
function register_custom_comment_api()
{

	//添加自定义接口
	register_rest_route('utils/v2', '/comments_count', [
		'methods'             => 'GET',
		'callback'            => 'api_get_user_unread_comment_reply_count',
		'permission_callback' => 'is_user_logged_in',

	]);

	register_rest_route('utils/v2', '/comments', [
		[
			'methods'             => 'GET',
			'callback'            => 'api_get_comment_replies',
			'permission_callback' => 'is_user_logged_in',
		],
	]);

	register_rest_route('utils/v2', '/comment_list', [
		[
			'methods'             => 'GET',
			'callback'            => 'api_get_comment_list',
		],
		[
			'methods'             => 'POST',
			'callback'            => 'api_insert_custom_comment',
			'permission_callback' => 'current_user_is_regular',
		],
	]);

	register_rest_route('utils/v2', '/comments/(?P<id>\d+)', [
		[
			'methods'             => 'DELETE',
			'callback'            => 'api_delete_comment',
			'permission_callback' => 'is_user_logged_in',
		],
	]);

	//评论点赞 和 踩 接口
	register_rest_route('utils/v2', '/comment_likes', [
		[
			'methods'             => 'POST',
			'callback'            => 'api_add_comment_likes',
		],
		[
			'methods'             => 'DELETE',
			'callback'            => 'api_delete_comment_likes',
		],
	]);


	//在respond中添加自定义数据
	register_rest_field(
		'comment',
		'metadata',
		[
			'get_callback' => 'api_add_custom_comment_metadata',
		]
	);

	//注册自定义meta数据
	register_custom_comment_metadata();
}

add_action('rest_api_init', 'mikuclub\register_custom_comment_api');
