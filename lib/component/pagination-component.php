<?php
namespace mikuclub;
/**
 * 分页导航 组件
 */
function pagination_component()
{


	global $wp_query;
	$wp_query->post  = null;
	$wp_query->posts = null;


	//获取总页数
	$max_page = $wp_query->max_num_pages;


	$pagination_output = '';

	//只有总页数超过1页时才会显示分页导航条
	if ($max_page > 1)
	{

		//获取当前页数 , 如果未设置, 默认为1
		$current_paged = get_query_var('paged', 1);

		//默认长度
		$length = 5;
		//计算开始页码, 不会低于1
		$start_page = (($current_paged - $length) > 1) ? ($current_paged - $length) : 1;
		//计算结束页码, 不会超过总页数
		$end_page = (($current_paged + $length) < $max_page) ? ($current_paged + $length) : $max_page;


		//分页头部html
		$the_list_start = '';
		//如果初始页码大于2, 输出省略号
		if ($start_page > 2)
		{
			$the_list_start = '<li class="page-item disabled flex-fill mb-2"><span class="page-link border-0">.....</span></li>';
		}
		//分页尾部html
		$the_list_end = '';
		//如果结束页码小于 总页数-1, 输出省略号
		if ($end_page < $max_page - 1)
		{
			$the_list_end = '
				<li class="page-item disabled flex-fill mb-2">
					<span class="page-link border-0">.....</span>
				</li>
				';
		}

		//增加跳转窗口
		$the_list_end .= '<li class="page-item flex-fill align-self-center ps-2 mb-2">
				
					<div class="input-group change-page">
						  <input type="number" class="form-control form-control-lg change-page-value" placeholder="总共' . $max_page . '页" name="paged"  min="1" max="' . $max_page . '" autocomplete="off">
						  <!--数值123456为占位符, 方便js抓取替换-->
						  <input type="hidden" class="change-page-href" value="' . get_pagenum_link(123456, true) . '" />
						 
						    <button class="btn btn-outline-miku btn-lg change-page-button ">跳转</button>
						
						</div>
				</li>';


		//分页内容html
		$the_list_content = '';
		//从 当前页数-分页长度的 页码开始,  到 当前页数+分页长度的页码结束为止
		for ($i = $start_page; $i <= $end_page; $i++)
		{

			//如果是当前页码
			if ($current_paged == $i)
			{

				$the_list_content .= '<li class="page-item flex-fill disabled mb-2">
						<span class="page-link  border-0 text-miku fw-bolder">' . $i . '</span>
					</li>';
			}
			else
			{
				$the_list_content .= '
					<li class="page-item flex-fill mb-2">
						<a class="page-link border-0 rounded" href="' . get_pagenum_link($i, true) . '">' . $i . '</a>
					</li>';
			}
		}

		//如果有开启动态加载下一页
		if (dopt('d_ajaxpager_b'))
		{

			$the_next_page = next_page_button('下一页');

			//根据查询变量 创建对应的input隐藏表单
			$the_next_page .= insertQueryInputForms($wp_query->query);
		} //如果没开启, 则使用传送 跳转切换下一页
		else
		{

			$the_next_page = '
			<a class=" btn btn-lg btn-miku w-100 my-3" href="' . next_posts(0, false) . '">
				下一页
			</a>';
		}

		//最终输出
		$pagination_output = <<< HTML

	<nav class="pagination-nav" aria-label="post-list navigation">
	
		{$the_next_page}
		
		<ul class="pagination pagination-lg justify-content-center text-center flex-wrap">
			{$the_list_start}
             {$the_list_content}
             {$the_list_end}
		</ul>
		</nav>

HTML;
	}

	return $pagination_output;
}


/**
 * 根据查询变量 创建对应的 隐藏表单
 *
 * @param $query_vars array
 *
 * @return string HTML input表单
 */
function insertQueryInputForms($query_vars)
{

	$output = '';

	$query_vars['page_type'] = get_current_page_type();

	//如果未设置初始分页
	if (!array_key_exists('paged', $query_vars) || !$query_vars['paged'])
	{
		$query_vars['paged'] = 1;
	}



	foreach ($query_vars as $key => $val)
	{

		//不需要
		//如果键名 是用户输入的搜索词 需要转义
		/*if ( $key == 's' ) {
			$val = urlencode( $val );
		}*/

		$output .= '<input type="hidden" name="' . $key . '" value="' . $val . '" />';
	}

	return $output;
}
