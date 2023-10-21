<?php
/*
	template name: 论坛页面
*/

use mikuclub\Session_Cache;

use function mikuclub\print_adult_404_content_for_no_logging_user;

get_header();

//如果用户有登陆 清空论坛消息通知计数
if(is_user_logged_in()){
	$_SESSION[ Session_Cache::USER_FORUM_NOTIFICATION_UNREAD_COUNT ] = 0;
}


?>

    <div class="content">

        <header class="page-header my-4">

        </header>

		<?php

  //如果未登录 输出404内容
  	if (!is_user_logged_in()) {
		echo print_adult_404_content_for_no_logging_user();
	}
	else {

		while ( have_posts() ) {
			the_post();
			?>

            <div class="page-content my-3">

				<?php the_content(); ?>

            </div>

			<?php
		}
	}

	?>


    </div>

<?php get_footer(); ?>