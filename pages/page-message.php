<?php
/*
	template name: 我的私信
	description:  
*/

//如果未登陆 重定向回首页

use mikuclub\constant\Message_Type;
use mikuclub\Session_Cache;

use function mikuclub\breadcrumbs_component;
use function mikuclub\next_page_button;
use function mikuclub\redirect_for_not_logged;

redirect_for_not_logged();

get_header();

//尝试从url参数中获取当前消息类型
$current_type = filter_input( INPUT_GET, 'type' );


$nav_items = [
	[
        'type_key' => 'type',
		'type'      => Message_Type::PRIVATE_MESSAGE,
		'name'      => '我的私信',
		'count'     => $_SESSION[ Session_Cache::PRIVATE_MESSAGE_COUNT ],
		'count_key' => Session_Cache::PRIVATE_MESSAGE_COUNT,
        'page_link' => get_page_link(),
	],
	[
		'type_key' => 'type',
		'type'      => Message_Type::COMMENT_REPLY,
		'name'      => '评论回复',
		'count'     => $_SESSION[ Session_Cache::USER_COMMENT_REPLY_UNREAD_COUNT ],
		'count_key' => Session_Cache::USER_COMMENT_REPLY_UNREAD_COUNT,
		'page_link' => get_page_link(),
	],
	[
		'type_key' => 'show_notification',
		'type'      => 1,
		'name'      => '论坛回复',
		'count'     => $_SESSION[ Session_Cache::USER_FORUM_NOTIFICATION_UNREAD_COUNT ],
		'count_key' => Session_Cache::USER_FORUM_NOTIFICATION_UNREAD_COUNT,
		'page_link' => get_home_url().'/forums',
	]

];
//检测每个菜单选项 是否为当前页面
foreach ( $nav_items as $key => $item ) {
	if ( $current_type == $item['type'] ) {
		$nav_items[ $key ]['active'] = 'active';
		//清零对应的消息计数
		$_SESSION[$item['count_key']] = 0;
	}
	else {
		$nav_items[ $key ]['active'] = '';
	}
}

$nav_items_html = '';
foreach ( $nav_items as $nav_item ) {

	$nav_items_html .= '
		<li class="nav-item">
			<a class="nav-link ' . $nav_item['active']. '" href="' . add_query_arg( $nav_item['type_key'], $nav_item['type'], $nav_item['page_link'] ) . '">'
	                   . $nav_item['name'] . ' <span class="badge bg-miku">' . $nav_item['count'] . '</span>
			</a>
		</li>';
}


$message_nav_component = '
	<nav>
		<ul class="nav nav-tabs justify-content-center  nav-fill">
			' . $nav_items_html . '
		</ul>
	</nav>';


?>

<div class="content page-message <?php echo $current_type; ?> mh-90vh">

    <header class="page-header ">
        <h4 class="my-4">
			<?php echo breadcrumbs_component(); ?>
        </h4>
		<?php echo $message_nav_component; ?>
    </header>


    <div class="page-content message-list accordion my-4" id="accordion">


    </div>

    <div class="my-2">
		<?php echo next_page_button( '下一页' ); ?>
        <input type="hidden" name="paged" value="0">
    </div>


</div>


<?php get_footer(); ?>
