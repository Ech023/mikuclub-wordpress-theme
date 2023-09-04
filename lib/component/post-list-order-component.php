<?php

namespace mikuclub;

/**
 * 文章列表自定义排序组件
 * @return string
 */
function post_list_order_component()
{

	//排序表单name名字
	$orderby_name          = Post_Query::CUSTOM_ORDERBY;
	$order_data_range_name = Post_Query::CUSTOM_ORDER_DATA_RANGE;

	$orderby          = sanitize_text_field(get_query_var($orderby_name));
	$order_data_range = sanitize_text_field(get_query_var($order_data_range_name));

	$orderby_options_html = '';
	//排序依据列表
	$orderby_options_list = [
		[
			'name'  => '最新发布',
			'value' => '',
		],
		[
			'name'  => '查看次数',
			'value' => Post_Meta::POST_VIEWS,
		],
		[
			'name'  => '点赞次数',
			'value' => Post_Meta::POST_LIKE,
		],
		[
			'name'  => '评论次数',
			'value' => Post_Meta::POST_COMMENT_COUNT,
		],
	];


	//日期范围列表
	$order_data_range_options_html = '';
	$order_data_range_options_list = [
		[
			'name'  => '默认',
			'value' => '',
		],
		[
			'name'  => '1个月内',
			'value' => 1,
		],
		[
			'name'  => '6个月内',
			'value' => 6,
		],
		[
			'name'  => '1年内',
			'value' => 12,
		],
	];

	//生成选项列表
	foreach ($orderby_options_list as $option)
	{
		$orderby_options_html .= '<option ' . ($option['value'] == $orderby ? "selected" : "") . ' value="' . $option['value'] . '">' . $option['name'] . '</option>';
	}
	foreach ($order_data_range_options_list as $option)
	{
		$order_data_range_options_html .= '<option ' . ($option['value'] == $order_data_range ? "selected" : "") . ' value="' . $option['value'] . '">' . $option['name'] . '</option>';
	}


	return <<<HTML

			<div class="row  my-4 justify-content-end post-list-order align-items-center">

				<div class="col-auto me-auto d-none d-sm-block">
					<h4>
						投稿列表
						
					</h4>
				</div>

			
			
				<div class=" col col-lg-3">

					<div class="input-group">
						<label class="input-group-text bg-white d-none d-sm-flex" for="{$orderby_name}">排序依据</label>
						<select class="form-select" id="{$orderby_name}" name="{$orderby_name}">
							{$orderby_options_html}
						</select>
					</div>
				</div>
				
				<div class="col col-lg-3">
					<div class="input-group">
				
						<label class="input-group-text bg-white d-none d-sm-flex" for="{$order_data_range}">日期范围</label>
						<select class="form-select" id="{$order_data_range}" name="{$order_data_range_name}">
							{$order_data_range_options_html}
						</select>
					</div>
				</div>
				
			</div>



HTML;
}
