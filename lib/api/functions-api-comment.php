<?php

namespace mikuclub;

use Exception;
use mikuclub\constant\Comment_Meta;
use mikuclub\constant\Config;
use mikuclub\User_Capability;
use WP_Error;
use WP_REST_Request;


/**
 * 获取用户未读的评论数量
 *
 * @param WP_REST_Request $data
 *
 * @return int
 */
function api_get_user_comment_reply_unread_count($data)
{
	return get_user_comment_reply_unread_count();
}


/**
 * API获取评论回复
 *
 * @param WP_REST_Request $data ['paged' => 页数, number' => 每页数据数量]
 *
 * @return My_Comment_Reply_Model[]|WP_Error
 */
function api_get_comment_reply_list($data)
{

	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$paged = Input_Validator::get_array_value($data, 'paged', Input_Validator::TYPE_INT, false) ?: 1;
		$number = Input_Validator::get_array_value($data, 'number', Input_Validator::TYPE_INT, false) ?: Config::NUMBER_COMMENT_REPLY_PER_PAGE;

		$result = get_comment_reply_list($paged, $number);

		return $result;
	});

	return $result;
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
	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		//获取 id 参数
		$comment_id = Input_Validator::get_array_value($data, 'id', Input_Validator::TYPE_INT, true);

		//当前登陆用户ID
		$user_id = get_current_user_id();

		//获取评论主体
		$comment =  get_comment($comment_id);
		if (empty($comment))
		{
			throw new Empty_Exception('评论');
		}

		//获取文章作者ID
		$post_id = intval($comment->comment_post_ID);
		$post_author_id = intval(get_post_field('post_author', $post_id));

		//评论人ID
		$author_id  = intval($comment->user_id);

		//检测时候是高级用户和当前文章的作者
		$is_premium_user_and_post_author = User_Capability::is_premium_user() && $user_id === $post_author_id;

		//检测权限, 只有管理员 和 评论作者自己 有权限删除评论
		if (
			//如果是管理员
			User_Capability::is_admin()
			||
			//如果是高级用户和文章的作者ID
			$is_premium_user_and_post_author
			||
			//如果是评论人本ID
			$user_id === $author_id
		)
		{
			//如果是文章作者就只把评论移到回收站, 其他情况 完全删除
			$result = wp_delete_comment($comment_id, $is_premium_user_and_post_author ? false : true);
			if ($result === false)
			{
				throw new Exception('删除失败');
			}

			//清空该文章的所有评论缓存
			delete_comment_file_cache($comment_id, $post_id);
		}
		else
		{
			throw new Exception('无权操作');
		}
	});

	return $result;
}

/**
 * API 获取文章的评论列表
 * @param WP_REST_Request $data
 *
 * @return My_Comment_Model[]|WP_Error
 */
function api_get_comment_list($data)
{
	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$post_id = Input_Validator::get_array_value($data, 'post_id', Input_Validator::TYPE_INT, true);
		$offset = Input_Validator::get_array_value($data, 'offset', Input_Validator::TYPE_INT, true);
		$number = Input_Validator::get_array_value($data, 'number', Input_Validator::TYPE_INT, false) ?: Config::NUMBER_COMMENT_PER_PAGE;

		$result = get_comment_list($post_id, $offset, $number);
		return $result;
	});

	return $result;
}


/**
 * 增加评论点赞次数
 *
 * @param WP_REST_Request $data
 *
 * @return int|WP_Error
 **/
function api_add_comment_like($data)
{

	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$comment_id = Input_Validator::get_array_value($data, 'comment_id', Input_Validator::TYPE_INT, true);

		//清空该文章的所有评论缓存
		delete_comment_file_cache($comment_id);

		$result = add_comment_like($comment_id);
		return $result;
	});

	return $result;
}

/**
 * 减少评论点赞次数
 *
 * @param WP_REST_Request $data
 *
 * @return int|WP_Error
 **/
function api_delete_comment_like($data)
{
	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$comment_id = Input_Validator::get_array_value($data, 'comment_id', Input_Validator::TYPE_INT, true);

		//清空该文章的所有评论缓存
		delete_comment_file_cache($comment_id);

		$result = delete_comment_like($comment_id);
		return $result;
	});

	return $result;
}


/**
 * 创建评论
 * @param WP_REST_Request $data
 *
 * @return bool|mixed|My_Comment_Model|WP_Error
 */
function api_insert_comment($data)
{
	$result = execute_with_try_catch_wp_error(function () use ($data)
	{
		$comment_post_ID = Input_Validator::get_array_value($data, 'comment_post_ID', Input_Validator::TYPE_INT, true);
		$comment_content = Input_Validator::get_array_value($data, 'comment_content', Input_Validator::TYPE_STRING, true);
		$comment_parent = Input_Validator::get_array_value($data, 'comment_parent', Input_Validator::TYPE_INT, false) ?? 0;

		$result = insert_comment($comment_content, $comment_post_ID, $comment_parent);
		return $result;
	});

	return $result;
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
			$output['comment_reply_ids'] = get_array_children_comment_id($comment_id);
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
		'callback'            => 'mikuclub\api_get_user_comment_reply_unread_count',
		'permission_callback' => 'is_user_logged_in',

	]);

	register_rest_route('utils/v2', '/comments', [
		[
			'methods'             => 'GET',
			'callback'            => 'mikuclub\api_get_comment_reply_list',
			'permission_callback' => 'is_user_logged_in',
		],
	]);

	register_rest_route('utils/v2', '/comment_list', [
		[
			'methods'             => 'GET',
			'callback'            => 'mikuclub\api_get_comment_list',
		],
		[
			'methods'             => 'POST',
			'callback'            => 'mikuclub\api_insert_comment',
			'permission_callback' => ['mikuclub\User_Capability', 'is_regular_user'],
		],
	]);

	register_rest_route('utils/v2', '/comments/(?P<id>\d+)', [
		[
			'methods'             => 'DELETE',
			'callback'            => 'mikuclub\api_delete_comment',
			'permission_callback' => 'is_user_logged_in',
		],
	]);

	//评论点赞 和 踩 接口
	register_rest_route('utils/v2', '/comment_likes', [
		[
			'methods'             => 'POST',
			'callback'            => 'mikuclub\api_add_comment_like',
		],
		[
			'methods'             => 'DELETE',
			'callback'            => 'mikuclub\api_delete_comment_like',
		],
	]);


	//在respond中添加自定义数据
	register_rest_field(
		'comment',
		'metadata',
		[
			'get_callback' => 'mikuclub\api_add_custom_comment_metadata',
		]
	);

	//注册自定义meta数据
	register_custom_comment_metadata();
}


