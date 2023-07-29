<?php
/*
	template name: 我的关注
*/

//如果未登陆 重定向回首页
redirect_for_not_logged();

get_header();

//获取用户关注列表
$user_followed = get_user_followed();

$empty_error = '';

//如果列表不是空
if ( $user_followed ) {
	global $wp_query;

	$args = [
		'posts_per_page'      => get_option( 'posts_per_page' ),
		'ignore_sticky_posts' => '1',
		'author'              => implode( ',', $user_followed ),
		'paged'               => get_query_var( 'paged', 1 ),
		'no_cache'            => true, //缓存
		'page_type'           => 'page'
	];

	unset( $wp_query->query['pagename'] );
	unset( $wp_query->query['page'] );

	$wp_query->query = array_merge( $args, $wp_query->query );

	//改变主循环
	$wp_query->query( $wp_query->query );
	//修复当前页面属性
	$wp_query->is_home     = $wp_query->is_archive = $wp_query->is_author = false;
	$wp_query->is_singular = $wp_query->is_page = true;


}
else {
	//如果没有关注, 输出错误提示
	$empty_error = '	<div class="m-5 mw-100 flex-fill">
    			<h4 class="text-center">抱歉, 您还没有添加任何关注</h4>
    			<br/><br/><br/><br/><br/>
			</div>';
}


?>

    <div class="content page-followed">

        <header class="page-header">
            <h4  class="my-4">
				<?php
                echo breadcrumbs_component();
				?>
            </h4>

            <div class="text-end">
	            <?php
	            echo '当前正在关注 '.count($user_followed).' 名用户';
	            ?>
            </div>
        </header>


        <!--        分隔符-->
        <hr/>

		<?php

		if ( $user_followed ) {
			echo post_list_component();
		}
		else {
			echo $empty_error;
		}

		?>

    </div>

<?php get_footer(); ?>