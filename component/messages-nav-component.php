<?php

/**
 * @deprecated  已废弃
 * 消息页面顶部切换菜单
 */
function message_nav_component($nav_items) {


	$nav_items_html = '';
	foreach ( $nav_items as $nav_item ) {


		$nav_items_html .= '
		<li class="nav-item">
			<a class="nav-link ' . $nav_item['active']. '" href="' . add_query_arg( 'type', $nav_item['type'], get_page_link() ) . '">'
		                   . $nav_item['name'] . ' <span class="badge bg-miku">' . $nav_item['count'] . '</span>
			</a>
		</li>';
	}


	return '
	<nav>
		<ul class="nav nav-tabs justify-content-center  nav-fill">
			' . $nav_items_html . '
		</ul>
	</nav>';


}