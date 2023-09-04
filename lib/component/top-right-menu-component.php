<?php
namespace mikuclub;
/*
 * 顶部右上角菜单组件
 */
function top_right_menu_component() {

	//当前用户ID
	$user_id = get_current_user_id();
	//网站主页地址
	$home = get_home_url();

	?>


    <nav class="navbar navbar-expand px-0 px-md-3">
        <ul class="navbar-nav justify-content-end flex-fill align-items-center">

			<?php
			//	未登录的用户
			if ( $user_id < 1 ) { ?>


                <li class="nav-item">
                    <a class="sign nav-link btn btn-miku px-md-4 px-2 mt-1" href="<?php echo wp_login_url(); ?>" title="登录/注册"><i
                                class="fa-solid fa-sign-in-alt"></i> 登录 / 注册 </a>
                </li>


			<?php } else { ?>


                <li class="user-profile with-sub-menu nav-item dropdown me-2 me-md-0">
                    <a class="user-img  nav-link" href="<?php echo $home; ?>/user_profile"
                       title="用户信息">
						<?php echo print_user_avatar( get_my_user_avatar( $user_id ), 30 ); ?>
                    </a>
                    <div class="dropdown-menu ">

                        <div class="dropdown-item-text text-truncate small fw-bold" style="max-width: 200px;">
							<?php echo get_the_author_meta( 'display_name', $user_id ); ?>
                        </div>
                        <div class="dropdown-item-text text-nowrap small">
                            积分 <?php echo get_user_points( $user_id ); ?></div>
                        <div class="dropdown-item-text text-nowrap small">
                            等级 <?php echo get_user_level( $user_id ); ?></div>

                        <div class="dropdown-divider"></div>

                        <a class="user-profile dropdown-item small"
                           href="<?php echo $home; ?>/user_profile"
                           title="个人中心">
                            个人中心
                        </a>

                        <div class="dropdown-divider"></div>

                        <a class="dropdown-item small" href="<?php echo wp_logout_url(); ?>">
                            退出
                        </a>
                    </div>
                </li>

                <li class="nav-item d-none d-md-block">
                    <a class="nav-link" href="<?php echo $home; ?>/char" title="签到">
                        签到
                    </a>
                </li>

                <li class="message-center with-sub-menu nav-item dropdown me-2 me-md-0">
					<?php
					$has_message   = '';
					$message_count = '';
					if ( get_user_unread_message_total_count() > 0 ) {
						$has_message   = 'has_message';
						$message_count = get_user_unread_message_total_count();
					}
					$message_page_link = $home . '/message';
					$forum_link =  $home . '/forums';

					?>
                    <a class="<?php echo $has_message ? 'text-miku' : ''; ?> nav-link"
                       href="<?php echo add_query_arg( 'type', Message_Type::PRIVATE_MESSAGE, $message_page_link ); ?>"
                       title="消息" target="_blank">
                        <i class="fa-solid fa-envelope d-md-none"></i> <span class="d-none d-md-inline">消息</span> <span
                                class="message_count"><?php echo $message_count; ?></span>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item"
                           href="<?php echo add_query_arg( 'type', Message_Type::PRIVATE_MESSAGE, $message_page_link ); ?>"
                           target="_blank">
                            我的私信
                            <span>
                                <?php if ( isset( $_SESSION[ Session_Cache::PRIVATE_MESSAGE_COUNT ] ) && $_SESSION[ Session_Cache::PRIVATE_MESSAGE_COUNT ] > 0 )
	                                echo $_SESSION[ Session_Cache::PRIVATE_MESSAGE_COUNT ] ?>
                            </span>
                        </a>

                        <a class="dropdown-item"
                           href="<?php echo add_query_arg( 'type', Message_Type::COMMENT_REPLY, $message_page_link ); ?>"
                           target="_blank">
                            评论回复
                            <span>
                                <?php if ( isset( $_SESSION[ Session_Cache::COMMENT_REPLY_COUNT ] ) && $_SESSION[ Session_Cache::COMMENT_REPLY_COUNT ] > 0 )
	                                echo $_SESSION[ Session_Cache::COMMENT_REPLY_COUNT ] ?>
                            </span>
                        </a>

                        <a class="dropdown-item"
                           href="<?php echo add_query_arg( 'show_notification', '1', $forum_link ); ?>"
                           target="_blank">
                            论坛回帖
                            <span>
                                <?php if ( isset( $_SESSION[ Session_Cache::FORUM_REPLY_COUNT ] ) && $_SESSION[ Session_Cache::FORUM_REPLY_COUNT ] > 0 )
	                                echo $_SESSION[ Session_Cache::FORUM_REPLY_COUNT ] ?>
                            </span>
                        </a>
                    </div>

                </li>
                <li class="nav-item me-2 me-md-0">
                    <a class="nav-link" href="<?php echo $home; ?>/favorite" title="收藏夹"
                       target="_blank">
                        <i class="fa-solid fa-heart d-md-none"></i><span class="d-none d-md-block">收藏夹</span>
                    </a>
                </li>
                <li class="nav-item me-2 me-md-0">
                    <a class="nav-link" href="<?php echo $home; ?>/history" title="历史记录"
                       target="_blank">
                        <i class="fa-solid fa-history d-md-none"></i><span class="d-none d-md-block">历史</span>
                    </a>
                </li>
                <li class="tougao-manage nav-item me-2 me-md-0">
                    <a class="nav-link" href="<?php echo $home; ?>/up_home_page" title="稿件管理"
                       target="_blank">
                        <i class="fa-solid fa-list-alt d-md-none"></i><span class="d-none d-md-block">稿件管理</span>
                    </a>
                </li>
                <li class="tougao nav-item ">
                    <a class="nav-link btn btn-miku btn-sm px-3 px-md-5" href="<?php echo $home; ?>/submit" title="新投稿"
                       target="_blank">
                        <i class="fa-solid fa-upload d-md-none"></i><span class="d-none d-md-block">投稿</span>
                    </a>
                </li>


			<?php } ?>
        </ul>
    </nav>

<?php } ?>