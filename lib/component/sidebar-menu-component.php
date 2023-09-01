<?php

/**
 * 侧边栏浮动菜单组件
 */
function sidebar_menu_component()
{

	//基本菜单选项
	$menu_items = [
		[
			'target_id'   => 'header',
			'name' => '<i class="fas fa-arrow-up fa-lg "></i>',
			'title' => '回到顶部',
		],
	];



	//如果是内容页
	if (is_single())
	{

		$menu_items_for_single = [
			[
				'target_id' => 'first-image-part',
				'name'   => '描述',
				'title' => '',
			],
			[
				'target_id' => 'password-part',
				'name'   => '下载',
				'title' => '',
			],
			[
				'target_id' => 'video-part',
				'name'   => '在线播放',
				'title' => '',
			],
			[
				'target_id' => 'preview-images-part',
				'name'   => '预览图',
				'title' => '',
			],
			[
				'target_id' => 'comments-part',
				'name'   => '评论',
				'title' => '',
			],
		];

		$menu_items = array_merge($menu_items, $menu_items_for_single);
	}



	$menu_items_html = '';
	foreach ($menu_items as $menu_item)
	{
		$menu_items_html .= '<a class="list-group-item list-group-item-action py-2 py-md-3 px-2 px-md-3" href="#' . $menu_item['target_id'] . '" data-bs-target-id="' . $menu_item['target_id'] . '" title="' . $menu_item['title'] . '">' . $menu_item['name'] . '</a>';
	};

	//如果不是内容页 并且没登陆 显示 登陆链接
	if (!is_single() && !is_user_logged_in())
	{
		$menu_items_html .= '<a class="list-group-item list-group-item-action py-2 py-md-3 px-2 px-md-3" href="' . wp_login_url() . '" target="_blank" title="登陆"><i class="fas fa-sign-in-alt fa-lg d-block d-md-none"></i><span class="d-none d-md-block">登陆<span></a>';
	}

	//暗夜模式按钮
	$menu_items_html .= <<<HTML
	
		<a id="dark_mode_button" class="list-group-item list-group-item-action py-3 py-md-3 px-2 px-md-3" title="手动设置的有效期为7天" href="javascript:void(0)" title="">
			<span class="moon" style="display: none;">
				<i class="fas fa-moon fa-lg d-block d-md-none" ></i>
				<span class="d-none d-md-block" style="display: none;">夜间模式</span>
			</span>
			<span class="sun" style="display: none;">
				<i class="fas fa-sun fa-lg d-block d-md-none" ></i>
				<span class="d-none d-md-block" style="display: none;">日间模式</span>
			</span>
			<span class="cloud-sun" style="display: none;">
				<i class="fas fa-cloud-sun fa-lg d-block d-md-none" ></i>
				<span class="d-none d-md-block" style="display: none;">自动日夜</span>
			</span>
		</a>

HTML;

	//如果不是内容页
	if (!is_single())
	{
		//底部按钮
		$menu_items_html .= <<<HTML
	
	<a class="list-group-item list-group-item-action py-2 py-md-3 px-2 px-md-3" href="#footer" data-bs-target-id="footer" title="前往底部">
		<i class="fas fa-arrow-down fa-lg "></i>
	</a>

HTML;
	}



	$output = <<<HTML

			<!--悬浮菜单-->
			<div id="fixed-sidebar-menu" class="list-group position-fixed end-0 small medium-md text-center opacity-75" style="display: none">
			    {$menu_items_html}
			</div>


HTML;

	return $output;
}
