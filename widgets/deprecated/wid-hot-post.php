<?php  
add_action( 'widgets_init', 'd_hot_posts' );

function d_hot_posts() {
	register_widget( 'd_hot_post' );
}

class d_hot_post extends WP_Widget {
	function d_hot_post() {
		$control_ops=null;
		$widget_ops = array( 'classname' => 'd_hot_post', 'description' => '显示15天的点击榜排行' );
		$this->__construct( 'd_hot_post', 'Yusi-点击排行榜', $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		
		echo hot_posts_most_views();

	}
	function form($instance) {

?>
		

<?php
	}
}


?>