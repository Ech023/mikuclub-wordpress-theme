<?php

/**
 * 自定义文章时间显示格式
 *
 * @param $post_time
 *
 * @return string
 */
function timeago( $post_time ) {
	$post_time    = strtotime( $post_time );
	$current_time = time() - $post_time;
	if ( $current_time < 1 ) {
		return '刚刚';
	}
	$interval = [
		12 * 30 * 24 * 60 * 60 => '年前 (' . date( 'Y-m-d', $post_time ) . ')',
		30 * 24 * 60 * 60      => '个月前 (' . date( 'm-d', $post_time ) . ')',
		7 * 24 * 60 * 60       => '周前 (' . date( 'm-d', $post_time ) . ')',
		24 * 60 * 60           => '天前',
		60 * 60                => '小时前',
		60                     => '分钟前',
		1                      => '秒前'
	];
	foreach ( $interval as $secs => $str ) {
		$d = $current_time / $secs;
		if ( $d >= 1 ) {
			$r = round( $d );

			return $r . $str;
		}
	};
}

/**
 * 自定义美化版 var_dump
 *
 * @param mixed $expression
 */
function var_dump_formatted( $expression ) {

	echo '<pre>';
	var_dump( $expression );
	echo '</pre>';
}

function var_dump_wp_query( ) {

	global $wp_query;

	echo '<pre>';
	var_dump( $wp_query );
	echo '</pre>';
}



/**
 * 生成文章列表的缓存ID字符串
 * @return string
 */
function create_post_list_cache_key() {

	$cache_key = [ POST_LIST ];

	if ( get_query_var( 'page_type' ) ) {
		$cache_key[] = get_query_var( 'page_type' );
	} else {
		$cache_key[] = get_current_page_type();
	}

	$cache_key[] = get_query_var( 'cat' );
	$cache_key[] = get_query_var( 'category_name' );
	$cache_key[] = get_query_var( 'post_type' );
	$cache_key[] = get_query_var( 'post_status' );
	$cache_key[] = get_query_var( 'post__in' );
	$cache_key[] = get_query_var( 'post__not_in' );
	$cache_key[] = get_query_var( 's' );
	$cache_key[] = get_query_var( 'author' );
	$cache_key[] = get_query_var( 'author_name' );
	$cache_key[] = get_query_var( 'tag' );
	$cache_key[] = get_query_var( 'tag_id' );
	$cache_key[] = get_query_var( 'order' );
	$cache_key[] = get_query_var( 'orderby' );
	$cache_key[] = get_query_var( 'date_query' );
	$cache_key[] = get_query_var( 'meta_key' );
	$cache_key[] = get_query_var( 'meta_value' );
	$cache_key[] = get_query_var( 'meta_value_num' );
	$cache_key[] = get_query_var( 'meta_compare' );
	$cache_key[] = get_query_var( 'meta_query' );
	$cache_key[] = get_query_var( 'paged' );

	//过滤掉空值
	$cache_key = array_filter( $cache_key, function ( $key ) {
		return $key != '';
	} );

	//转换成字符串返回
	return implode( '_', $cache_key );
}



/**
 * 私信页分类导航
 *
 * @param $count
 * @param $number_on_single_page
 *
 * @return string
 */
function message_paging( $count, $number_on_single_page ) {
	$output = ''; //返回值变量
	if ( isset( $_GET['pagenum'] ) ) {
		$current_page = $_GET['pagenum'];
	}
	else {
		$current_page = 1;
	}

	$total_page = ceil( $count / $number_on_single_page ); //获取全部的分页数
	if ( $total_page > 1 )  //如果分页数大于1 才有必要输出分页导航
	{


		$output .= '<div class="message-pagination"><ul>';

		if ( $current_page > 4 ) {
			$index = $current_page - 3;
		}
		else {
			$index = 2;
		}


		if ( $current_page == 1 ) {
			$output .= '<li class="current-page"><span>1</span></li>';
		}
		else {
			$output .= '<li><a href="?pagenum=1">1</a></li>';
		}

		if ( $current_page > 5 ) {
			$output .= '<li><span>...</span></li>';
		}


		for ( $i = $index; $i < $total_page && $i < $index + 7; $i ++ ) {

			if ( $i == $current_page ) {
				$output .= '<li class="current-page"><span>' . $i . '</span></li>';
			}
			else {
				$output .= '<li><a href="?pagenum=' . $i . '">' . $i . '</a></li>';
			}
		}
		if ( $i < $total_page ) {
			$output .= '<li><span>...</span></li>';
		}


		if ( $current_page == $total_page ) {
			$output .= '<li class="current-page"><span>' . $total_page . '</span></li>';
		}
		else {
			$output .= '<li><a href="?pagenum=' . $total_page . '">' . $total_page . '</a></li>';
		}

		$output .= '</ul></div>';
	}

	return $output;
}






/*
 * 添加侧边栏小工具
if ( function_exists( 'register_sidebar' ) ) {
	register_sidebar( array(
		'name'          => '全站侧边栏',
		'id'            => 'widget_sitesidebar',
		'before_widget' => '<div class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<div class="title"><h2>',
		'after_title'   => '</h2></div>'
	) );
	register_sidebar( array(
		'name'          => '首页侧边栏',
		'id'            => 'widget_home_sidebar',
		'before_widget' => '<div class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<div class="title"><h2>',
		'after_title'   => '</h2></div>'
	) );
	register_sidebar( array(
		'name'          => '分类/标签/搜索页侧边栏',
		'id'            => 'widget_othersidebar',
		'before_widget' => '<div class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<div class="title"><h2>',
		'after_title'   => '</h2></div>'
	) );
	register_sidebar( array(
		'name'          => '文章页侧边栏',
		'id'            => 'widget_postsidebar',
		'before_widget' => '<div class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<div class="title"><h2>',
		'after_title'   => '</h2></div>'
	) );
	register_sidebar( array(
		'name'          => '最新文章侧边栏',
		'id'            => 'widget_new_post_sidebar',
		'before_widget' => '<div class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<div class="title"><h2>',
		'after_title'   => '</h2></div>'
	) );
	register_sidebar( array(
		'name'          => '论坛侧边栏',
		'id'            => 'widget_bbs_sidebar',
		'before_widget' => '<div class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<div class="title"><h2>',
		'after_title'   => '</h2></div>'
	) );
}*/


/**
 * 更改后台字体
 *//*
function Bing_admin_lettering() {
	echo '<style type="text/css">
        * { font-family: "Microsoft YaHei" !important; }
        i, .ab-icon, .mce-close, i.mce-i-aligncenter, i.mce-i-alignjustify, i.mce-i-alignleft, i.mce-i-alignright, i.mce-i-blockquote, i.mce-i-bold, i.mce-i-bullist, i.mce-i-charmap, i.mce-i-forecolor, i.mce-i-fullscreen, i.mce-i-help, i.mce-i-hr, i.mce-i-indent, i.mce-i-italic, i.mce-i-link, i.mce-i-ltr, i.mce-i-numlist, i.mce-i-outdent, i.mce-i-pastetext, i.mce-i-pasteword, i.mce-i-redo, i.mce-i-removeformat, i.mce-i-spellchecker, i.mce-i-strikethrough, i.mce-i-underline, i.mce-i-undo, i.mce-i-unlink, i.mce-i-wp-media-library, i.mce-i-wp_adv, i.mce-i-wp_fullscreen, i.mce-i-wp_help, i.mce-i-wp_more, i.mce-i-wp_page, .qt-fullscreen, .star-rating .star { font-family: dashicons !important; }
        .mce-ico { font-family: tinymce, Arial !important; }
        .fas, .far { font-family: FontAwesome !important; }
        .genericon { font-family: "Genericons" !important; }
        .appearance_page_scte-theme-editor #wpbody *, .ace_editor * { font-family: Monaco, Menlo, "Ubuntu Mono", Consolas, source-code-pro, monospace !important; }
        </style>';
}

add_action( 'admin_head', 'Bing_admin_lettering' );*/


/*==========================================================================*/
/*临时数据操作*/


/*已经可以删除*/

/*
function get_qq_union_id($data)
{

    //指定数量
    if (isset($data['number'])) {

        global $wpdb;
        $results = $wpdb->get_results(
            "
			SELECT *
			FROM mm_usermeta M1
			WHERE M1.meta_key=\"open_type_qq_access_token\" AND M1.meta_value <> \"\"
				AND  M1.user_id NOT IN (SELECT M2.user_id FROM mm_usermeta M2
			WHERE M2.meta_key =\"open_type_qq_union_id\" ) limit " . $data['number'] . "
			"
        );

        $final_output = [];

        foreach ($results as $single) {
            $token = $single->meta_value;

            $response = wp_remote_request('https://graph.qq.com/oauth2.0/me?access_token=' . $token . '&unionid=1');
            $body = wp_remote_retrieve_body($response);
            //去除外层
            $body = ltrim($body, "callback(");
            $body = rtrim($body, ");\n");
            $body = json_decode($body, true);

            $output = [];
            $output["user_id"] = $single->user_id;

            if (!empty($body["unionid"])) {
                $output["union_id"] = $body["unionid"];
                update_user_meta($single->user_id, "open_type_qq_union_id", $body["unionid"]);
            }
            if (!empty($body["error_description"])) {
                $output["error"] = $body["error"];
                $output["error_description"] = $body["error_description"];
                update_user_meta($single->user_id, "open_type_qq_access_token", "");
            }


            $final_output[] = $output;
        }


        return $final_output;

    } else {
        return "number参数未设置";
    }


}*/


/*============================================================================*/


/*
//sql更新或插入最新的数据到 post_list表里
function post_list_update($list_name, $list_value, $taxonomy_id)
{

    //如果列表不是空的, 则储存
    //if(!empty($list_value))
    //{

    global $wpdb;

    $now = date("Y-m-d H:i:s");

    $exist = $wpdb->get_var('SELECT list_id FROM mm_post_list WHERE list_name = "' . $list_name . '" AND cat_id = ' . $taxonomy_id);

    if ($exist == 0) {
        $wpdb->query('INSERT INTO mm_post_list (list_name, list_value, cat_id, list_date) VALUES ( "' . $list_name . '", "' . $list_value . '", ' . $taxonomy_id . ', "' . $now . '")');
    }
    else {
        $wpdb->query('UPDATE mm_post_list SET list_value="' . $list_value . '", list_date="' . $now . '" WHERE list_name= "' . $list_name . '" AND cat_id=' . $taxonomy_id);
    }

    //}
}

//点击排行榜
function hot_posts_init($count = 6, $target = 'target="_blank"')
{
    global $wpdb; //调用全局变量

    $list_name = 'hot_posts'; //设置在表中的 名称
    $content = '';

    //默认则获取标签对应taxonomy_id为0
    $taxonomy_id = 0;
    //如果是在分类或者标签页面,则获取标签对应taxonomy_id
    if (is_category() || is_tag()) {
        $current_query_object = get_queried_object();
        $taxonomy_id = $current_query_object->term_taxonomy_id;
    }


    $risult = $wpdb->get_row('SELECT list_date, list_value FROM mm_post_list WHERE list_name = "' . $list_name . '" AND cat_id = ' . $taxonomy_id); //sql 查询是否有该行存在

    if ($risult !== null) {
        //获取数据的储存时间
        $last_update_date = $risult->list_date;
        //计算时间差距
        $duration = strtotime(date("Y-m-d H:i:s")) - strtotime($last_update_date);
        //如果小于5个小时
        if ($duration < 18000) { //5小时过期一次
            //并且内容不是空的
            if ($risult->list_value != '') {
                // 根据缓存输出对应文章,
                $content .= hot_posts_print($risult->list_value, $count, $target);
            }
        }
        else {
            $content .= hot_posts_update_print($list_name, $taxonomy_id, $count, $target); //$update = true 更新数据
        }
    }
    else {
        $content .= hot_posts_update_print($list_name, $taxonomy_id, $count, $target); //$update = true 更新数据
    }

    //如果有数据, 才输出
    if ($content != '') {

        $content = '<div class="popular-posts"><h2><i class="fas fa-fire"></i> 热门榜</h2><div class="hot-list">' . $content . '</div></div>'; //排行榜开头
    }
    return $content;
}

//输出热门文章列表(缓存)
function hot_posts_print($list_value, $count, $target = 'target="_blank"')
{

    global $wpdb;
    $risult = $wpdb->get_results('SELECT  P.ID, P.post_title, M.meta_value FROM mm_posts P,mm_postmeta M WHERE  post_type="post" AND post_status="publish"  AND M.meta_key = "views" AND M.post_id=P.ID AND P.ID IN (' . $list_value . ') ORDER BY meta_value+0 DESC limit 0, ' . $count);

    $content = '';
    //初始化排行名次
    $num = 1;
    foreach ($risult as $row) {
        $content .= '<div class="hot-post-box cache"><span class="widget-hot-rank"><i class="fas fa-bolt"></i> ' . $num /* 输出排名 */ /*. '</span><span class="widget-hot-view"><i class="fas fa-eye"></i> ' . $row->meta_value /* 输出点击数 */ /*. '</span><a title="' . $row->post_title . '" href="' .  get_home_url() . '/' . $row->ID . '" ' . $target . '><img src="' . get_thumbnail_src($row->ID) . '" alt="' . $row->post_title . '"><div class="post-title">' . $row->post_title . '</div></a></div>';
$num++;
}

return $content;
}
/*
//输出热门文章列表(更新)
function hot_posts_update_print($list_name, $taxonomy_id, $count, $target = 'target="_blank"')
{
    global $wpdb;

    //设置时区
    //date_default_timezone_set('PRC');
    //计算15天前的UTC时间
    $expire_time = date("Y-m-d H:i:s", time() - 15 * 24 * 60 * 60);


    //如果不是在分类或标签页, 默认更新 全站点击排行
    if ($taxonomy_id == 0) {


        $risult = $wpdb->get_results('SELECT P.ID , P.post_title, M.meta_value FROM mm_posts P, mm_postmeta M WHERE P.post_type="post" AND  P.post_status="publish" AND P.post_date_gmt > "' . $expire_time . '" AND P.ID = M.post_id AND M.meta_key = "views" AND NOT EXISTS(SELECT * FROM mm_postmeta M2 WHERE P.ID=M2.post_id AND M2.meta_key = "main_cat" AND M2.meta_value=1120) ORDER BY M.meta_value+0 DESC LIMIT ' . $count);
    }
    else { //如果是在分类或标签页
        $risult = $wpdb->get_results('SELECT P.ID , P.post_title, M.meta_value FROM mm_posts P, mm_postmeta M, mm_term_relationships R WHERE P.post_type="post" AND  P.post_status="publish" AND P.post_date_gmt > "' . $expire_time . '" AND P.ID = M.post_id AND M.meta_key = "views" AND P.ID = R.object_id AND R.term_taxonomy_id = ' . $taxonomy_id . ' ORDER BY M.meta_value+0 DESC LIMIT ' . $count);

    }

    $post_list = '';
    $content = '';

    //如果有数据
    if (!is_null($risult)) {

        //初始化排行名次
        $num = 1;
        //创建一个空数组
        $array_post_list = array();

        foreach ($risult as $row) {

            $content .= '<div class="hot-post-box update"><span class="widget-hot-rank"><i class="fas fa-bolt"></i> ' . $num /* 输出排名 */ /*. '</span><span class="widget-hot-view"><i class="fas fa-eye"></i> ' . $row->meta_value /* 输出点击数 *//* . '</span><a title="' . $row->post_title . '" href="' .  get_home_url() . '/' . $row->ID . '" ' . $target . '><img src="' . get_thumbnail_src($row->ID) . '" alt="' . $row->post_title . '" /><div class="post-title">' . $row->post_title . '</div></a></div>';
$num++;
array_push($array_post_list, $row->ID); //储存有关文章id到数组里
}

$post_list = implode(",", $array_post_list); //转换数组为字符串 并用逗号分隔
}

post_list_update($list_name, $post_list, $taxonomy_id); //更新最新的点击榜数据到 数据库里
return $content;
}
/*
function rating_posts_init($count = 6, $target = 'target="_blank"')
{

    global $wpdb; //调用全局变量
    global $cat; //调用全局变量
    $list_name = 'rating_post'; //设置在表中的 名称
    $content = '';

    //默认则获取标签对应taxonomy_id为0
    $taxonomy_id = 0;
    //如果是在分类或者标签页面,则获取标签对应taxonomy_id
    if (is_category() || is_tag()) {
        $current_query_object = get_queried_object();
        $taxonomy_id = $current_query_object->term_taxonomy_id;
    }

    $risult = $wpdb->get_row("SELECT list_date, list_value FROM mm_post_list WHERE list_name = '$list_name' AND cat_id = $taxonomy_id"); //sql 查询是否有该行存在

    if ($risult !== null) { //如果存在
        $last_update_date = $risult->list_date;
        $duration = strtotime(date("Y-m-d H:i:s")) - strtotime($last_update_date);
        if ($duration < 18000) { //检测数据是否有过期5小时, 没有则输出缓存
            //并且内容不是空的
            if ($risult->list_value != '') {
                $content .= rating_posts_print($risult->list_value, $count, $target); //$update = false 不更新 纯输出
            }
        }
        else {
            $content .= rating_posts_update_print($list_name, $taxonomy_id, $count, $target); //$update = true 更新数据
        }
    }
    else {
        $content .= rating_posts_update_print($list_name, $taxonomy_id, $count, $target); //$update = true 更新数据
    }

    //如果有数据, 才输出
    if ($content != '') {

        $content = '<div class="rating-posts" ><h2><i class="fas fa-heart"></i>评分榜</h2><div class="rating-box">' . $content . '</div></div>'; //排行榜开头
    }
    return $content;
}

//输出最高评分文章列表(缓存)
function rating_posts_print($list_value, $count, $target = 'target="_blank"')
{

    global $wpdb;
    $risult = $wpdb->get_results('SELECT  P.ID, P.post_title, M.meta_value FROM mm_posts P,mm_postmeta M WHERE  post_type="post" AND post_status="publish"  AND M.meta_key = "count_like" AND M.post_id=P.ID AND P.ID IN (' . $list_value . ') ORDER BY meta_value+0 DESC limit 0, ' . $count);

    $content = '';
    //初始化排行名次
    $num = 1;
    foreach ($risult as $row) {

        $content .= '<div class="rating-div cache"><span class="widget-hot-rank"><i class="fas fa-bolt"></i> ' . $num . '</span><span class="rating-total"><i class="far fa-star" aria-hidden="true"></i> ' . $row->meta_value /* 输出评分总数 *//* . '</span><a title="' . $row->post_title . '" href="' .  get_home_url() . '/' . $row->ID . '" ' . $target . '><img src="' . get_thumbnail_src($row->ID) . '" alt="' . $row->post_title . '" /><div class="post-title">' . $row->post_title . '</div></a></div>';

$num++;
}

return $content;
}
/*
//输出最高评分文章列表(更新)
function rating_posts_update_print($list_name, $taxonomy_id, $count, $target = 'target="_blank"')
{
    global $wpdb;
    $content = '';
    $post_list = '';
    //计算15天前的UTC时间
    $expire_time = date("Y-m-d H:i:s", time() - 15 * 24 * 60 * 60);


    //如果不是在分类或标签页, 默认更新 全站评分排行
    if ($taxonomy_id == 0) {


        $risult = $wpdb->get_results('SELECT P.ID , P.post_title, M.meta_value FROM mm_posts P, mm_postmeta M WHERE P.post_type="post" AND  P.post_status="publish" AND P.post_date_gmt > "' . $expire_time . '" AND P.ID = M.post_id AND M.meta_key = "count_like" AND NOT EXISTS(SELECT * FROM mm_postmeta M2 WHERE P.ID=M2.post_id AND M2.meta_key = "main_cat" AND M2.meta_value=1120) ORDER BY M.meta_value+0 DESC LIMIT ' . $count);
    } //如果是在分类或标签页
    else {

        $risult = $wpdb->get_results('SELECT P.ID , P.post_title, M.meta_value FROM mm_posts P, mm_postmeta M, mm_term_relationships R WHERE P.post_type="post" AND  P.post_status="publish" AND P.post_date_gmt > "' . $expire_time . '" AND P.ID = M.post_id AND M.meta_key = "count_like" AND P.ID = R.object_id AND R.term_taxonomy_id = ' . $taxonomy_id . ' ORDER BY M.meta_value+0 DESC LIMIT ' . $count);
    }
    //如果有数据
    if (!is_null($risult)) {
        //创建一个空数组
        $array_post_list = array();
        //初始化排行名次
        $num = 1;

        foreach ($risult as $row) {
            //输出文章列表
            $content .= '<div class="rating-div update"><span class="widget-hot-rank"><i class="fas fa-bolt"></i> ' . $num . '</span><span class="rating-total"><i class="far fa-star" aria-hidden="true"></i> ' . $row->meta_value /* 输出评分总数 *//* . '</span><a title="' . $row->post_title . '" href="' .  get_home_url() . '/' . $row->ID . '" ' . $target . '><img src="' . get_thumbnail_src($row->ID) . '" alt="' . $row->post_title . '" /><div class="post-title">' . $row->post_title . '</div></a></div>';*/
/*
            $num++;

            //储存有关文章id到数组里
            array_push($array_post_list, $row->ID);
        }

        $post_list = implode(",", $array_post_list); //转换数组为字符串 并用逗号分隔
    }

    post_list_update($list_name, $post_list, $taxonomy_id); //更新最新的点击榜数据到 数据库里
    return $content;
}
*/
/*============================================================================*/
