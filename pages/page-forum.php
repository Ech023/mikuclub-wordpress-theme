<?php
/*
	template name: 论坛页面
*/

namespace mikuclub;

use mikuclub\Session_Cache;

use function mikuclub\print_adult_404_content_for_no_logging_user;

get_header();

//如果用户有登陆 清空论坛消息通知计数
if (is_user_logged_in())
{
	Session_Cache::set(Session_Cache::USER_FORUM_NOTIFICATION_UNREAD_COUNT, 0);
}


//如果未登录 输出404内容
if (!is_user_logged_in())
{
	echo print_adult_404_content_for_no_logging_user();
}
else
{

	while (have_posts())
	{
		the_post();

		echo <<<HTML
	
		<div class="page-forum">
			<div class="page-header">
			</div>
			<div class="page-content my-2">
		
HTML;

		the_content();

		echo <<<HTML
			</div>
		</div>
HTML;
	}
}



get_footer();
