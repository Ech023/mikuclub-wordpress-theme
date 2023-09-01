<?php  
add_action( 'widgets_init', 'd_banners_taobao' );

function d_banners_taobao() {
	register_widget( 'd_banner_taobao' );
}

class d_banner_taobao extends WP_Widget {
	function d_banner_taobao() {
		$control_ops=null;
		$widget_ops = array( 'classname' => 'd_banner', 'description' => '显示一个广告(包括富媒体)' );
		$this->__construct( 'd_banner_taobao', 'Yusi-淘宝广告专用', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		$title = apply_filters('widget_name', $instance['title']);
		$code = $instance['code'];
		$code2 = $instance['code2'];

		echo $before_widget;
		echo '<div class="d_banner_inner">';
	
		echo $code;

		echo '</div>'.$after_widget;
	}

	function form($instance) {
?>
		<p>
			<label>
				广告名称：
				<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $instance['title']; ?>" class="widefat" />
			</label>
		</p>
		<p>
			<label>
				外区展示代码：
				<textarea id="<?php echo $this->get_field_id('code'); ?>" name="<?php echo $this->get_field_name('code'); ?>" class="widefat" rows="12" style="font-family:Courier New;"><?php echo $instance['code']; ?></textarea>
			</label>
		</p>
		<p>
			<label>
				里区展示代码：
				<textarea id="<?php echo $this->get_field_id('code2'); ?>" name="<?php echo $this->get_field_name('code2'); ?>" class="widefat" rows="12" style="font-family:Courier New;"><?php echo $instance['code2']; ?></textarea>
			</label>
		</p>
<?php
	}
}

?>