<?php
add_action( 'widgets_init', 'd_postlists' );

function d_postlists() {
	register_widget( 'd_postlist' );
}

class d_postlist extends WP_Widget {
	function d_postlist() {
		$control_ops=null;
		$widget_ops = array( 'classname' => 'd_postlist', 'description' => '图文展示（最新文章+热门文章+随机文章）' );
		$this->__construct( 'd_postlist', 'Yusi-聚合文章', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title        = apply_filters('widget_name', $instance['title']);
		$limit        = $instance['limit'];
		$cat          = $instance['cat'];
		$orderby      = $instance['orderby'];
		$more = $instance['more'];
		$link = $instance['link'];
		$img = $instance['img'];

		$mo='';
		$style='';
		if( $more!='' && $link!='' ) $mo='<a class="btn" href="'.$link.'">'.$more.'</a>';
		if( !$img ) $style = ' class="nopic"';
		echo $before_widget;
		echo $before_title.$mo.$title.$after_title; 
		echo '<ul'.$style.'>';
		echo dtheme_posts_list( $orderby,$limit,$cat,$img );
		echo '</ul>';
		echo $after_widget;
	}

	function form( $instance ) {
?>
		<p>
			<label>
				标题：
				<input style="width:100%;" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
			</label>
		</p>
		<p>
			<label>
				排序：
				<select style="width:100%;" id="<?php echo $this->get_field_id('orderby'); ?>" name="<?php echo $this->get_field_name('orderby'); ?>" style="width:100%;">
					<option value="comment_count" <?php selected('comment_count', $instance['orderby']); ?>>评论数</option>
					<option value="date" <?php selected('date', $instance['orderby']); ?>>发布时间</option>
					<option value="rand" <?php selected('rand', $instance['orderby']); ?>>随机</option>
                
				</select>
			</label>
		</p>
		<p>
			<label>
				分类限制：
				<a style="font-weight:bold;color:#f60;text-decoration:none;" href="javascript:;" title="格式：1,2 &nbsp;表限制ID为1,2分类的文章&#13;格式：-1,-2 &nbsp;表排除分类ID为1,2的文章&#13;也可直接写1或者-1；注意逗号须是英文的">？</a>
				<input style="width:100%;" id="<?php echo $this->get_field_id('cat'); ?>" name="<?php echo $this->get_field_name('cat'); ?>" type="text" value="<?php echo $instance['cat']; ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				显示数目：
				<input style="width:100%;" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="number" value="<?php echo $instance['limit']; ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				More 显示文字：
				<input style="width:100%;" id="<?php echo $this->get_field_id('more'); ?>" name="<?php echo $this->get_field_name('more'); ?>" type="text" value="<?php echo $instance['more']; ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				More 链接：
				<input style="width:100%;" id="<?php echo $this->get_field_id('link'); ?>" name="<?php echo $this->get_field_name('link'); ?>" type="url" value="<?php echo $instance['link']; ?>" size="24" />
			</label>
		</p>
		<p>
			<label>
				<input style="vertical-align:-3px;margin-right:4px;" class="checkbox" type="checkbox" <?php checked( $instance['img'], 'on' ); ?> id="<?php echo $this->get_field_id('img'); ?>" name="<?php echo $this->get_field_name('img'); ?>">显示图片
			</label>
		</p>
		
	<?php
	}
}

function dtheme_posts_list($orderby,$limit,$cat,$img) {
	global $post;
	$args = array(
		'order'            => 'DESC',
		'cat'              => $cat,
		'orderby'          => $orderby,
		'showposts'        => $limit,
		'ignore_sticky_posts' => 1
	);
	$content='';
	
	$myposts = get_posts( $args );
	foreach ( $myposts as $post ) {
		setup_postdata( $post );
		$content .= '<li><div class="thumbnail"><span class="posts_lisit_views"><i class="fa-solid fa-eye"></i> ' . get_post_views($post->ID) . '</span><a title="' . $post->post_title . '" href="' . get_permalink($post->ID) . '" target="_blank" ><img src="' . get_thumbnail_src($post->ID) . '" alt="' . $post->post_title . '" /></a></div><div class="text"><a title="' . $post->post_title . '" href="' . get_permalink($post->ID) . '" target="_blank" >' . $post->post_title . '</a></div></li>';
	}
	
	echo $content;
	
	wp_reset_postdata();
}

/*
function dtheme_posts_list2($orderby,$limit,$cat,$img) {
	$args = array(
		'order'            => 'DESC',
		'cat'              => $cat,
		'orderby'          => $orderby,
		'showposts'        => $limit,
		'ignore_sticky_posts' => 1
	);
	get_posts($args);
	while (have_posts()) : the_post(); 
	?>
		<li>
				<a title="<?php the_title(); ?>" href="<?php the_permalink(); ?>" target="_blank" >
						
					<div class="thumbnail">
							<span class="posts_lisit_views">
									<i class="fa-solid fa-eye"></i> <?php echo get_deel_views_by_id(''); ?>
							</span>
							<img src="<?php echo get_thumbnail_src($post->ID); ?>" alt="<?php the_title();?>" />
					</div>
					<div class="text">
							<?php the_title(); ?>
					</div>
			</a>
	</li>
	
<?php
	
    endwhile;
	wp_reset_query();

}
*/
?>