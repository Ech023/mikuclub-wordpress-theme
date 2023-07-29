<aside class="sidebar">	
	<?php 
	//加载论坛侧边栏
	 if (function_exists('is_bbpress')  && is_bbpress()){
		//if ( is_active_sidebar('widget_bbs_sidebar') && function_exists('dynamic_sidebar') && dynamic_sidebar('widget_bbs_sidebar')) : endif; 
	}
	else 
	{	
	
	/*	
	
	?>
			<div class="widget widget_text">
				<div class="textwidget">
					<div class="social">
						<?php if( dopt('d_tqq_b') || dopt('d_weibo_b') || dopt('d_facebook_b') || dopt('d_twitter_b') ){ ?>

						<?php if( dopt('d_qqContact_b') ) echo '<a href="'.dopt('d_qqContact').'" rel="external nofollow" title="初音社官方Q群" target="_blank"><i class="qq fas fa-qq"></i></a>'; ?>
						<?php if( dopt('d_weibo_b') ) echo '<a href="'.dopt('d_weibo').'" rel="external nofollow" title="新浪微博" target="_blank"><i class="sinaweibo fas fa-weibo"></i></a>'; ?>
						<?php if( dopt('d_tqq_b') ) echo '<a  href="'.dopt('d_tqq').'" rel="external nofollow" title="B站空间" target="_blank"><i class="tencentweibo fas fa-youtube"></i></a>'; ?>
						<?php if( dopt('d_twitter_b') ) echo '<a href="'.dopt('d_twitter').'" rel="external nofollow" title="Twitter" target="_blank"><i class="twitter fas fa-twitter"></i></a>'; ?>
						<?php if( dopt('d_facebook_b') ) echo '<a href="'.dopt('d_facebook').'" rel="external nofollow" title="动漫周边" target="_blank"><i class="fascebook fas fa-shopping-cart"></i></a>'; ?>
						<?php if( dopt('d_weixin_b') ) echo '<a class="weixin"><i class="weixins fas fa-mobile-alt"></i><div class="weixin-popover"><div class="popover bottom in"><div class="popover-arrow"></div><div class="popover-title">二维码“'.dopt('d_weixin').'”</div><div class="popover-content"><img src="'.get_bloginfo('template_url').'/img/weixin.png" ></div></div></div></a>';?>
						<?php if( dopt('d_emailContact_b') ) echo '<a href="'.dopt('d_emailContact').'" rel="external nofollow" title="Email" target="_blank"><i class="email far fa-envelope"></i></a>'; ?>

						<?php } ?>
					</div>
				</div>
			</div>
		<?php
	 
	 	*/
	 		
				
			
	 
					//加载全站侧边栏
					if ( is_active_sidebar('widget_sitesidebar') && function_exists('dynamic_sidebar')  && dynamic_sidebar('widget_sitesidebar')) : endif; 

					//加载文章侧边栏
					if (is_single()){
						if ( is_active_sidebar('widget_postsidebar') && function_exists('dynamic_sidebar') && dynamic_sidebar('widget_postsidebar')) : endif; 
					}

					//如果是首页和最新文章页面
					else if (is_home())
					{


						//如果url里没有page 那就是首页
						if(strripos($_SERVER['REQUEST_URI'], 'page') == FALSE){
								//加载首页侧边栏
								if (is_active_sidebar('widget_home_sidebar') && function_exists('dynamic_sidebar') && dynamic_sidebar('widget_home_sidebar')) : endif; 
						}
						//如果是最新文章
						else
						{
								if (is_active_sidebar('widget_new_post_sidebar') &&function_exists('dynamic_sidebar') && dynamic_sidebar('widget_new_post_sidebar')) : endif; 
						}

					}
					//如果不是上述页面, 那就是 分类页, 标签页了
					else 
					{

						if (is_active_sidebar('widget_othersidebar') && function_exists('dynamic_sidebar') && dynamic_sidebar('widget_othersidebar')) : endif; 
					}
			
	}
	?>
</aside>