<?php  

/*
 * 移除不使用的插件
include('wid-banner.php');
include('wid-banner-taobao.php');
include('wid-hot-post.php');
include('wid-postlist.php');
include('wid-comment.php');
include('wid-tags.php');
include('wid-textbanner.php');
include('wid-subscribe.php');
*/

function unregister_d_widget(){
    unregister_widget('WP_Widget_Search');
    unregister_widget('WP_Widget_Recent_Comments');
    unregister_widget('WP_Widget_Tag_Cloud');
    unregister_widget('WP_Nav_Menu_Widget');
}
//移除不必要的挂件
add_action('widgets_init','unregister_d_widget');

?>