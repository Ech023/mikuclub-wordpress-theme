<?php  
add_action( 'widgets_init', 'd_comments' );

function d_comments() {
	register_widget( 'd_comment' );
}

class d_comment extends WP_Widget {
	function d_comment() {
		$control_ops=null;
		$widget_ops = array( 'classname' => 'd_comment', 'description' => '显示网友最新评论（头像+名称+评论）' );
		$this->__construct( 'd_comment', 'Yusi-最新评论', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_name', $instance['title']);
		$limit = $instance['limit'];
		//$outer = $instance['outer'];
		//$outpost = $instance['outpost'];
		//$more = $instance['more'];
		//$link = $instance['link'];

		//$mo='';
		//if( $more!='' && $link!='' ) $mo='<a class="btn" href="'.$link.'">'.$more.'</a>';
		
		echo $before_widget;
		echo $before_title.$title.$after_title; 
		echo '<ul>';
		echo mod_newcomments( $limit);
		echo '</ul>';
		echo $after_widget;
	}

	function form($instance) {

?>
		<p>
			<label>
				标题：
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" />
			</label>
		</p>
		<p>
			<label>
				显示数目：
				<input class="widefat" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" type="number" value="<?php echo $instance['limit']; ?>" />
			</label>
		</p>
		<p>
			<label>
				排除某用户ID：
				<input class="widefat" id="<?php echo $this->get_field_id('outer'); ?>" name="<?php echo $this->get_field_name('outer'); ?>" type="number" value="<?php echo $instance['outer']; ?>" />
			</label>
		</p>
		<p>
			<label>
				排除某文章ID：
				<input class="widefat" id="<?php echo $this->get_field_id('outpost'); ?>" name="<?php echo $this->get_field_name('outpost'); ?>" type="text" value="<?php echo $instance['outpost']; ?>" />
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

<?php
	}
}

function mod_newcomments( $limit){
	global $wpdb;
	$output ='';
	
	//$sql = "SELECT DISTINCT ID, post_title, post_password, comment_id, comment_post_id, comment_author, comment_date, comment_approved,comment_author_email, comment_type,comment_author_url, SUBSTRING(comment_content,1,40) AS com_excerpt FROM $wpdb->comments LEFT OUTER JOIN $wpdb->posts ON ($wpdb->comments.comment_post_id = $wpdb->posts.ID) WHERE comment_post_id!='".$outpost."' AND user_id!='".$outer."' AND comment_approved = '1' AND comment_type = '' AND post_password = '' ORDER BY comment_date_gmt DESC LIMIT $limit";
	
	$sql = 'SELECT C.comment_id, C.comment_post_id, C.comment_author, C.comment_author_email, C.comment_date, C.comment_content, C.user_id FROM mm_comments C WHERE C.comment_approved = 1 ORDER BY C.comment_id DESC LIMIT '.$limit;
	
	
	$comments = $wpdb->get_results($sql);
	
	foreach ( $comments as $comment ) {
		$output .= '<li><a href="'.get_permalink($comment->comment_post_ID).'#comment-'.$comment->comment_ID.'" title="'.$comment->comment_author.'的评论">' . print_user_avatar( get_my_user_avatar($comment->user_id)) . ' <div class="muted"><span class="user_img">' . $comment->comment_author . '</span><span class="comment_date"><i class="far fa-clock"></i> ' . date("m-d h:i", strtotime($comment->comment_date)) . '</span><p>' . convert_smilies(strip_tags($comment->comment_content)) . '</p></div></a></li>';
	}
	
	echo $output;
};

?>