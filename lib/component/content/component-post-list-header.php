<?php

namespace mikuclub;

use mikuclub\constant\Download_Link_Type;
use mikuclub\constant\Post_Meta;
use mikuclub\Post_Query;

/**
 * 文章列表自定义排序组件
 * @return string
 */
function print_post_list_header_component()
{

	$array_orderby = [
		[
			'group' => 'post_date',
			'title' => '近期发布',
			'icon' => 'fa-regular fa-clock',
			'sub_orderby' => [
				[
					'title' => '最新更新',
					'value' => 'modified',
					'parameters' => [
						Post_Query::CUSTOM_ORDERBY => 'modified',
						// Post_Query::CUSTOM_ORDER_DATA_RANGE => '',
					],
				],
				[
					'title' => '最新发布',
					'value' => 'date',
					'parameters' => [
						Post_Query::CUSTOM_ORDERBY => 'date',
						// Post_Query::CUSTOM_ORDER_DATA_RANGE => '',
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
			$button_class = 'btn-secondary active';
		}
		else
		{
			$button_class = 'bg-gray-half';
		}


		$orderby_items .= <<<HTML
			<div class="col-auto">
				<button class="btn btn-sm px-md-4 orderby_group {$button_class}" data-orderby-group="{$orderby['group']}">
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
				$button_class = 'btn-secondary active';
			}
			else
			{
				$button_class = 'bg-gray-half';
			}

			//如果所属排序组未显示, 隐藏子排序
			if ($orderby['group'] !== $active_custom_orderby)
			{
				$style = 'display: none;';
			}

			$json_parameters = htmlspecialchars(json_encode($sub_orderby['parameters']));

			$sub_orderby_items .= <<<HTML
			<div class="col-auto sub_orderby_container {$orderby['group']}" style="{$style}">
				<button class="btn btn-sm px-md-4 sub_orderby {$button_class}"  data-parameters='{$json_parameters}'>
					{$sub_orderby['title']}
				</button>
			</div>

HTML;
		}
	}


	$download_type_items = print_download_type_by_items_component();

	$output = <<<HTML

		<div class="my-2">
			<div class="row post_list_orderby align-items-center mb-2 gy-2 gx-2">
				{$orderby_items}
			</div>
			<div class="row post_list_sub_orderby align-items-center mb-2 gy-2 gx-2">
				{$sub_orderby_items}
			</div>
			<div class="row post_list_download_type align-items-center gy-2 gx-2">
				{$download_type_items}
			</div>
		</div>

HTML;



	return $output;
}

/**
 * 输出和文章下载方式有关的过滤按钮
 * 
 * @return string
 */
function print_download_type_by_items_component()
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
			$button_class = 'btn-secondary active';
		}
		else
		{
			$button_class = 'bg-gray-half';
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

	return $output;
}
