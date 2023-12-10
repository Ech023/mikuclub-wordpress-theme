<?php

namespace mikuclub;

use mikuclub\constant\Download_Link_Type;
use mikuclub\constant\Expired;
use mikuclub\constant\Post_Meta;
use mikuclub\constant\Post_Status;
use mikuclub\Post_Query;

/**
 * 文章列表自定义排序组件
 * @param bool $show_fail_time_order 是否显示最多失效反馈
 * @return string
 */
function print_post_list_header_order($show_fail_time_order = false)
{

	$array_orderby = [
		[
			'group' => 'post_date',
			'title' => '近期发布',
			'icon' => 'fa-solid fa-clock',
			'sub_orderby' => [
				[
					'title' => '最新更新',
					'value' => 'modified',
					'parameters' => [
						Post_Query::CUSTOM_ORDERBY => 'modified',
						Post_Query::CUSTOM_ORDER_DATA_RANGE => '',
					],
				],
				[
					'title' => '最新发布',
					'value' => 'date',
					'parameters' => [
						Post_Query::CUSTOM_ORDERBY => 'date',
						Post_Query::CUSTOM_ORDER_DATA_RANGE => '',
					],
				],
			],

		],
	];

	$array_custom_orderby_post_meta = [
		[
			'group' => Post_Meta::POST_LIKE,
			'icon' => 'fa-solid fa-thumbs-up',
			'title' => '最多好评',
		],
		[
			'group' => Post_Meta::POST_VIEWS,
			'icon' => 'fa-solid fa-eye',
			'title' => '最多点击',
		],
		[
			'group' => Post_Meta::POST_COMMENT_COUNT,
			'icon' => 'fa-solid fa-comment',
			'title' => '最多评论',
		],
	];

	$array_custom_order_data_range = [
		[
			'title' =>  '最近1个月内',
			'value' => 30,
		],
		[
			'title' =>  '最近2个月内',
			'value' => 60,
		],
		[
			'title' =>  '最近3个月内',
			'value' => 90,
		],
		[
			'title' =>  '最近6个月内',
			'value' => 180,
		],
		[
			'title' =>  '最近12个月内',
			'value' => 365,
		],
	];






	$array_orderby = array_merge($array_orderby, array_map(function ($orderby_post_meta) use ($array_custom_order_data_range)
	{
		//添加 sub_orderby 属性
		$orderby_post_meta['sub_orderby'] =  array_map(function ($order_data_range) use ($orderby_post_meta)
		{
			//添加 parameters 属性
			$order_data_range['parameters'] = [
				Post_Query::CUSTOM_ORDERBY => $orderby_post_meta['group'],
				Post_Query::CUSTOM_ORDER_DATA_RANGE => $order_data_range['value'],
			];
			return $order_data_range;
		}, $array_custom_order_data_range);

		return $orderby_post_meta;
	}, $array_custom_orderby_post_meta));

	//添加下载失效排序
	if ($show_fail_time_order)
	{
		$array_orderby[] = [
			'group' => Post_Meta::POST_FAIL_TIME,
			'title' => '最多下载失效反馈',
			'icon' => 'fa-solid fa-bug',
			'sub_orderby' => [],
			'parameters' => [
				Post_Query::CUSTOM_ORDERBY => Post_Meta::POST_FAIL_TIME,
				Post_Query::CUSTOM_ORDER_DATA_RANGE => '',
			],
		];
	}



	//如果当前存在 自定义排序参数请求
	$active_custom_orderby = get_query_var(Post_Query::CUSTOM_ORDERBY, 'modified');
	$active_custom_order_data_range  = intval(get_query_var(Post_Query::CUSTOM_ORDER_DATA_RANGE, 30));


	//兼容默认时间排序
	if ($active_custom_orderby === 'modified')
	{
		$active_custom_orderby = 'post_date';
		$active_custom_order_data_range = 'modified';
	}
	else if ($active_custom_orderby === 'date')
	{
		$active_custom_orderby = 'post_date';
		$active_custom_order_data_range = 'date';
	}



	$orderby_items = '';
	$sub_orderby_items = '';
	//便利每个排序按钮分类
	foreach ($array_orderby as $orderby)
	{
		$button_class = '';
		if ($orderby['group'] === $active_custom_orderby)
		{
			$button_class = 'btn-dark-1 active';
		}
		else
		{
			$button_class = 'btn-light-2';
		}

		$json_parameters_group = '';
		if(isset($orderby['parameters'])){
			$json_parameters_group = htmlspecialchars(json_encode($orderby['parameters']));
		}


		$orderby_items .= <<<HTML
			<div class="col-auto">
				<button class="btn btn-sm px-md-4 orderby_group {$button_class}" data-orderby-group="{$orderby['group']}" data-parameters='{$json_parameters_group}'>
					<i class="{$orderby['icon']} me-2"></i>
					{$orderby['title']}
				</button>
			</div>

HTML;
		//便利每个子排序按钮
		foreach ($orderby['sub_orderby'] as $sub_orderby)
		{
			$button_class = '';
			$style = '';

			//兼容 最新修改 和 最新发布
			if ($sub_orderby['value'] === $active_custom_order_data_range)
			{
				$button_class = 'btn-dark-1 active';
			}
			else
			{
				$button_class = 'btn-light-2';
			}

			//如果所属排序组未显示, 隐藏子排序
			if ($orderby['group'] !== $active_custom_orderby)
			{
				$style = 'display: none;';
			}

			$json_parameters = htmlspecialchars(json_encode($sub_orderby['parameters']));

			$sub_orderby_items .= <<<HTML
			<div class="col-auto sub_orderby_container {$orderby['group']}" style="{$style}">
				<button class="btn btn-sm px-md-4 sub_orderby {$button_class}" data-parameters='{$json_parameters}'>
					{$sub_orderby['title']}
				</button>
			</div>

HTML;
		}
	}


	$output = <<<HTML

		<div>
			<div class="row post_list_orderby align-items-center mb-2 g-2">
				{$orderby_items}
			</div>
			<div class="row post_list_sub_orderby align-items-center mb-2 g-2">
				{$sub_orderby_items}
			</div>
		</div>

HTML;



	return $output;
}

/**
 * 输出和文章下载方式有关的列表过滤按钮
 * 
 * @return string
 */
function print_post_list_header_download_type()
{
	$active_custom_down_type = get_query_var(Post_Query::CUSTOM_DOWN_TYPE, '');

	$array_download_type = [
		[
			'title' => '全部方式',
			'value' => '',
			'parameters' => [
				Post_Query::CUSTOM_DOWN_TYPE => ''
			],
		],
		[
			'title' => '百度盘',
			'value' => Download_Link_Type::BAIDU_PAN,
			'parameters' => [
				Post_Query::CUSTOM_DOWN_TYPE => Download_Link_Type::BAIDU_PAN,
			],
		],
		[
			'title' => '夸克盘',
			'value' => Download_Link_Type::QUARK,
			'parameters' => [
				Post_Query::CUSTOM_DOWN_TYPE => Download_Link_Type::QUARK,
			],
		],
		[
			'title' => 'UC盘',
			'value' => Download_Link_Type::UC_DRIVE,
			'parameters' => [
				Post_Query::CUSTOM_DOWN_TYPE => Download_Link_Type::UC_DRIVE,
			],
		],
		[
			'title' => '阿里云盘',
			'value' => Download_Link_Type::ALIYUN_DRIVE,
			'parameters' => [
				Post_Query::CUSTOM_DOWN_TYPE => Download_Link_Type::ALIYUN_DRIVE,
			],
		],
		[
			'title' => '磁力链接',
			'value' => Download_Link_Type::MAGNET,
			'parameters' => [
				Post_Query::CUSTOM_DOWN_TYPE => Download_Link_Type::MAGNET,
			],
		],
	];

	$output = array_reduce($array_download_type, function ($carry, $item) use ($active_custom_down_type)
	{

		$button_class = '';
		if ($item['value'] === $active_custom_down_type)
		{
			$button_class = 'btn-dark-1 active';
		}
		else
		{
			$button_class = 'btn-light-2';
		}

		$json_parameters = htmlspecialchars(json_encode($item['parameters']));

		$carry .= <<<HTML
			<div class="col-auto">
				<button class="btn btn-sm px-md-4 download_type {$button_class}"  data-parameters='{$json_parameters}'>
					{$item['title']}
				</button>
			</div>

HTML;

		return $carry;
	}, '');

	$output = <<<HTML
		<div class="row post_list_download_type align-items-center mb-2 g-2">
			{$output}
		</div>
HTML;

	return $output;
}

/**
 * 输出和文章下载方式有关的列表过滤按钮
 * 
 * @param string|string[] $default_status 默认状态
 * @return string
 */
function print_post_list_header_post_status($default_status)
{

	$active_post_status = get_query_var(Post_Query::POST_STATUS, '');
	//如果没有指定参数 就自动设置默认状态
	if (empty($active_post_status))
	{
		$active_post_status = $default_status;
	}

	$array_post_status = [
		Post_Status::get_to_array(), //伪状态
		Post_Status::PUBLISH,
		Post_Status::PENDING,
		Post_Status::DRAFT,
	];

	$output = array_reduce($array_post_status, function ($result, $status) use ($active_post_status)
	{

		$button_class = '';
		if (($status === $active_post_status) || (is_array($status) && is_array($active_post_status)))
		{
			$button_class = 'btn-dark-1 active';
		}
		else
		{
			$button_class = 'btn-light-2';
		}

		//如果是特定状态
		if (is_array($status))
		{
			$status_description = '全部状态';
		}
		//否则是指全部状态
		else
		{
			$status_description = Post_Status::get_description($status);
		}

		$parameters = [
			Post_Query::POST_STATUS => $status,
		];

		$json_parameters = htmlspecialchars(json_encode($parameters));

		$result .= <<<HTML
			<div class="col-auto">
				<button class="btn btn-sm px-md-4 post_status {$button_class}"  data-parameters='{$json_parameters}'>
					{$status_description}
				</button>
			</div>

HTML;

		return $result;
	}, '');

	$output = <<<HTML
		<div>
			<div class="row post_list_post_status align-items-center mb-2 g-2">
				{$output}
			</div>
		</div>
HTML;

	return $output;
}

/**
 * 输出和分类有关的列表过滤按钮
 * 
 * @return string
 */
function print_post_list_header_category()
{
	$active_sub_cat_id = get_query_var(Post_Query::CUSTOM_CAT, 0);
	$active_main_cat_id = 0;

	//如果存在
	if ($active_sub_cat_id)
	{
		//确保获取到主分类ID
		$active_main_cat_id = get_parent_category_id($active_sub_cat_id) ?: $active_sub_cat_id;
		//如果参数为主分类ID, 就把 子分类ID重置为0
		if ($active_main_cat_id === $active_sub_cat_id)
		{
			$active_sub_cat_id = 0;
		}
	}


	//获取缓存
	$output = File_Cache::get_cache_meta(File_Cache::POST_LIST_HEADER_CATEGORY, File_Cache::DIR_COMPONENTS, Expired::EXP_30_MINUTE);

	//如果没有缓存 或者 没有分类请求参数 
	if (empty($output) || $active_sub_cat_id > 0 || $active_main_cat_id > 0)
	{

		//获取分类id列表
		$array_category = get_array_main_category();

		//创建伪分类
		$total_category = new My_Category_Model();
		$total_category->term_id = 0;
		$total_category->name = '全部分区';

		array_unshift($array_category, $total_category);

		$category_items = '';
		$sub_category_items = '';

		//便利每个排序按钮分类
		foreach ($array_category as $category)
		{
			$array_sub_category = get_array_sub_category($category->term_id);
			$data_custom_cat = null;
			$data_category_group = null;
			//如果没有子分区
			if (count($array_sub_category) === 0)
			{
				//直接设置分类请求参数
				$data_custom_cat = $category->term_id;
			}
			//否则设置分类分组
			else
			{
				$data_category_group = 'category_group_' . $category->term_id;

				//创建伪子分类
				$total_sub_category = new My_Category_Model();
				$total_sub_category->term_id = $category->term_id;
				$total_sub_category->name = '全部子分区';

				array_unshift($array_sub_category, $total_sub_category);
			}

			$button_class = '';
			if ($category->term_id === $active_main_cat_id)
			{
				$button_class = 'btn-dark-1 active';
			}
			else
			{
				$button_class = 'btn-light-2';
			}

			$category_items .= <<<HTML
			<div class="col-auto">
				<button class="btn btn-sm px-md-4 category_group {$button_class}" data-custom_cat="{$data_custom_cat}" data-category_group="{$data_category_group}">
					{$category->name}
				</button>
			</div>
HTML;


			//便利每个子排序按钮
			foreach ($array_sub_category as $sub_category)
			{
				$data_custom_cat = $sub_category->term_id;

				$button_class = '';
				$style = '';
				if ($sub_category->term_id === $active_sub_cat_id)
				{
					$button_class = 'btn-dark-1 active';
				}
				else
				{
					$button_class = 'btn-light-2';
				}

				//如果所属排序组未显示, 隐藏子排序
				if ($category->term_id !== $active_main_cat_id)
				{
					$style = 'display: none;';
				}

				$sub_category_items .= <<<HTML
			<div class="col-auto sub_category_container {$data_category_group}" style="{$style}">
				<button class="btn btn-sm px-md-4 sub_category {$button_class}" data-custom_cat="{$data_custom_cat}">
					{$sub_category->name}
				</button>
			</div>
HTML;
			}
		}

		$output = <<<HTML

		<div class="my-2">
			<div class="row post_list_category align-items-center mb-2 g-2">
				{$category_items}
			</div>
			<div class="row post_list_sub_category align-items-center mb-2 g-2">
				{$sub_category_items}
			</div>
		</div>

HTML;
	}


	return $output;
}

/**
 * 输出搜索框
 *	
 * @param string $placeholder
 * @return string
 */
function post_list_header_search_form($placeholder = '')
{

	$search_value = sanitize_text_field(get_query_var('s'));

	$output = <<<HTML

		<div class="my-2">
			<form class="site-search-form input-group input-group-sm">
				<input type="text" class="form-control" name="search" autocomplete="off" placeholder="{$placeholder}" value="{$search_value}"/>
				<button type="submit" class="btn btn-sm btn-miku px-4">
					<i class="fa-solid fa-search me-2"></i> 搜索
				</button>
			</form>
		</div>

HTML;

	return $output;
}
