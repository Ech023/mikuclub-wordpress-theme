<!--关闭网页main主体-->
</section>


<?php

use mikuclub\constant\Admin_Meta;

use function mikuclub\current_user_is_admin;

use function mikuclub\get_bottom_menu;
use function mikuclub\get_friends_links;
use function mikuclub\get_new_post_count;
use function mikuclub\get_theme_option;
use function mikuclub\sidebar_menu_component;
use function mikuclub\site_categories_total_count;
use function mikuclub\site_comments_total_count;
use function mikuclub\site_posts_total_count;
use function mikuclub\site_tags_total_count;

 $home = get_home_url(); ?>

<footer id="footer" class="footer mt-5 px-3 px-sm-5">

    <div class="py-3">

        <div class="row">


            <!--        底部菜单-->
            <div class="col-12 col-md-6 my-2">
                <div class="bottom-menu row">
                    <nav class="navbar navbar-expand flex-wrap flex-lg-nowrap">
                        <a class="navbar-brand"
                           href="<?php echo get_home_url(); ?>"><?php echo get_option( 'blogname' ); ?> © 2014 -
                            <span id="current-year"></span></a>
						<?php echo get_bottom_menu(); ?>
                    </nav>
                </div>

                <div class="my-2">

					<?php 
						echo get_theme_option( Admin_Meta::SITE_ANNOUNCEMENT_BOTTOM);
					 ?>

                </div>


            </div>


            <div class="col-12 col-md-6 my-2 d-flex align-items-center justify-content-center">
                <!-- 随机语句-->
                <p id="custom-phrase" class="text-info">
                </p>
            </div>

        </div>


		<?php
		//给管理员看的统计信息
		if ( current_user_is_admin() ) { ?>

            <div class="admin-info my-2">

                <ul class="list-group list-group-horizontal-lg">

                    <li class="list-group-item flex-fill">
                        站点统计:
                    </li>
                    <li class="list-group-item">
						<?php echo site_posts_total_count(); ?> 篇投稿
                    </li>
                    <li class="list-group-item">
						<?php echo site_comments_total_count() ?> 条评论
                    </li>
                    <li class="list-group-item">
						<?php echo site_categories_total_count(); ?> 个分类
                    </li>
                    <li class="list-group-item">
						<?php echo site_tags_total_count(); ?> 个标签
                    </li>
                    <li class="list-group-item">
						<?php echo timer_stop( 0 ); ?> 响应时间
                    </li>
                    <li class="list-group-item">
						<?php echo get_num_queries(); ?> 查询次数
                    </li>

                </ul>

            </div>

		<?php } ?>


		<?php

		//只有在 首页的时候 才会输出
		if ( is_home() && strripos( $_SERVER['REQUEST_URI'], 'page' ) === false ) {
			?>
            <!--        友情链接-->
            <div class="friends-link my-2">
				<?php echo get_friends_links() ?>
            </div>
		<?php } ?>


    </div>


</footer>

<!-- Toast吐司提示框-->
<div id="toast">
</div>

<?php
wp_footer();



//流量统计代码.
if (get_theme_option(Admin_Meta::SITE_BOTTOM_TRACK_CODE_ENABLE))
{
    echo '<!-- 底部流量统计代码 -->';
    echo get_theme_option(Admin_Meta::SITE_BOTTOM_TRACK_CODE);
}

//底部公共代码
if (get_theme_option(Admin_Meta::SITE_BOTTOM_CODE_ENABLE))
{
    echo '<!-- 底部公共代码 -->';
    echo get_theme_option(Admin_Meta::SITE_BOTTOM_CODE);
}

?>

<script>

	<?php

	//底部动态JS代码

	$new_post_count = get_new_post_count( 3 );

	echo <<< HTML
    
         $(function () {
                //设置最新文章数量
                showNewPostCountInTopMenu({$new_post_count});
            });
        


HTML;

	//如果是文章页面
	if ( is_single() ) {

		$post_id = get_the_ID();

		echo <<< HTML

        //记录浏览记录
        setHistoryPostArray({$post_id});
        //增加文章点击数
        addPostViews({$post_id});

HTML;


	}

	?>

</script>


<?php
//check_query_cost();
?>

<!--关闭网页主体-->
</div>

<?php echo sidebar_menu_component(); ?>

</body>
</html>