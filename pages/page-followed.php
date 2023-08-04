<?php
/*
	template name: 我的关注
*/

//如果未登陆 重定向回首页
redirect_for_not_logged();

get_header();

//获取当前的id author 请求参数
$p_id_author = isset($_GET['id_author']) ? intval($_GET['id_author']) : 0;

$current_page_url =  get_permalink(get_the_ID());


//获取用户关注列表
$user_followed = get_user_followed();





//关注列表元素
$array_author_element = '';

//如果关注列表不是空
if ($user_followed)
{
	global $wp_query;

	$args = [
		'posts_per_page'      => get_option('posts_per_page'),
		'ignore_sticky_posts' => '1',
		'author'              => implode(',', $user_followed),
		'paged'               => get_query_var('paged', 1),
		//'no_cache'            => true, //缓存
		'page_type'           => 'page'
	];

	unset($wp_query->query['pagename']);
	unset($wp_query->query['page']);

	//如果存在 作者id请求参数
	if ($p_id_author)
	{
		//更改查询参数
		$args['author'] = $p_id_author;
	}

	$wp_query->query = array_merge($args, $wp_query->query);

	//改变主循环
	$wp_query->query($wp_query->query);

	//修复当前页面属性
	$wp_query->is_home     = $wp_query->is_archive = $wp_query->is_author = false;
	$wp_query->is_singular = $wp_query->is_page = true;

	$content = post_list_component();


	// 获取关注作者的实例数组
	$array_user = get_users(array(
		'include' => $user_followed,
	));

	//创建关注作者列表数组
	$array_author = array_merge([
		[
			'id' => 0,
			'display_name' => '全部关注',
			'user_image' => '',
		]
	], array_map(function ($user)
	{
		return [
			'id' => $user->ID,
			'display_name' => $user->display_name,
			'user_image' =>  get_my_user_avatar($user->ID),
		];
	}, $array_user));

	//转换成html元素
	$array_author_element = array_reduce($array_author, function ($result, $author) use ($p_id_author, $current_page_url)
	{
		$id_author = $author['id'];
		$display_name = $author['display_name'];
		$user_image_url =  $author['user_image'];
		$href = $current_page_url;
		$class_activated = '';


		//如果不出来作者ID
		if (empty($id_author))
		{

			//如果不存在 作者ID请求参数
			if (empty($p_id_author))
			{
				//设置专属类名
				$class_activated = 'text-miku';
			}

			//输出 自定义 图标
			$user_image = <<<HTML
			<div  style="width: 40px; height: 40px" class="ms-2 ps-2 pt-1">
				<i class="fa-solid fa-border-all fa-2x"></i>
			</div>
HTML;
		}
		//如果有作者ID
		else
		{
			//如果作者ID 和 请求参数 一样
			if ($id_author ===  $p_id_author)
			{
				//设置专属类名
				$class_activated = 'text-miku';
			}

			//设置专属链接
			$href .= '?' . http_build_query([
				'id_author' => $id_author,
			]);

			//输出img标签
			$user_image = <<<HTML
			<img class="rounded-circle " src="{$user_image_url}" style="width: 40px; height: 40px" alt="作者头像">
HTML;
		}



		$result .= <<<HTML

			<div class="col-auto">
				<a class="text-center {$class_activated}" href="{$href}" data-id_author="{$id_author}">
					<div>
						{$user_image}
					</div>
					<div class="text-break overflow-hidden mt-2" style="width: 80px; height: 48px;">
						{$display_name}
					</div>
				</a>
			</div>

HTML;
		return $result;
	}, '');

}
//如果没有关注过其他用户, 输出错误信息
else
{
	$content = <<<HTML
	<div class="m-5 mw-100 flex-fill">
		<h4 class="text-center">抱歉, 您还没有添加任何关注</h4>
			<br/><br/><br/><br/><br/>
	</div>
HTML;
}

$breadcrumbs_component = breadcrumbs_component();
$number_followed = count($user_followed);






$output = <<<HTML

<div class="content page-followed">

	<header class="page-header">
		<h4 class="my-4">
			{$breadcrumbs_component}
		</h4>

		<div class="row">
			<div class="col-12 col-md-1 mb-4 mb-md-0">
				<div class="text-center">
					<div>关注</div>
					<div class="fw-bold large">{$number_followed}</div>
				</div>
			</div>
			<div class="col-11">
				<div class="row g-4  pb-4 overflow-y-auto" style="max-height: 312px">
					{$array_author_element}
				</div>

			</div>

		</div>

	
	</header>

	<!--分隔符-->
	<hr />

	{$content}

</div>

HTML;

echo $output;

get_footer();
