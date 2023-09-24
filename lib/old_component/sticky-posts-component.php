<?php
namespace mikuclub;

use mikuclub\constant\Config;

/**
 * 幻灯片 组件
 * @return string
 */
function sticky_posts_component() {


	//获取列表规定长度
	$stick_count = Config::STICKY_POST_COUNT;
	//获取置顶文章列表
	$sticky_post_list = get_sticky_posts( $stick_count );

	/*插入临时广告
	移除广告的时候 记得 移除 rel="nofollow"
	*//*
		if ( is_home() ) {
			$pub             = new stdClass();
			$pub->post_href  = "https://7segu.taobao.com/?mm_sycmid=1_107094_d185748d4a828471510653e2787f7546";
			$pub->post_image = "https://cdn.mikuclub.it/pub/七色谷首页.jpg";
			$pub->post_title = "【七色谷】 午夜生活摆脱寂寞 一款解决需求的玩具";
			array_splice( $sticky_post_list, 1, 0, [ $pub ] );
    }*/

	//实际列表长度
	$sticky_post_list_length = count( $sticky_post_list );


	$output = '';

	//确保文章列表不是空的
	if ( $sticky_post_list_length ) {

		//生成指示符item
		$carousel_indicators_list = '';
		for ( $i = 0; $i < $sticky_post_list_length; $i ++ ) {

			$class_name               = ( $i == 0 ) ? 'active' : '';
			$carousel_indicators_list .= '<li data-bs-target="#carousel" data-bs-slide-to="' . $i . '"
                class="' . $class_name . '"></li>';

		}

		//创建指示器用来识别第一个图
		$isFirst            = true;
		$carousel_item_list = '';

		$count_replace = 0;

		//创建幻灯片内容
		foreach ( $sticky_post_list as $my_post_sticky ) {

			$class_name = ( $isFirst == true ) ? 'active' : '';

			$carousel_item_list .= <<< HTML
        
        <div class="carousel-item {$class_name}" data-bs-interval="5000">
            <a href="{$my_post_sticky->post_href}" target="_blank" rel="nofollow" class="">
                <img class="d-block w-100" src="{$my_post_sticky->post_image}" alt="{$my_post_sticky->post_title}"
                     skip_lazyload/>
                <div class="carousel-caption d-none d-sm-block">
                    <h6>{$my_post_sticky->post_title}</h6>
                </div>
            </a>
        </div>

HTML;

			$isFirst = false;
		}


		//最终输出内容
		$output = <<<HTML

	<div id="carousel" class="carousel top-slide" data-bs-ride="carousel">
	    <ol class="carousel-indicators">
	        {$carousel_indicators_list}
	    </ol>
	    <div class="carousel-inner">
	        {$carousel_item_list}
		</div>
	<a class="carousel-control-prev" href="#carousel" role="button" data-bs-slide="prev">
	    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
	    <span class="sr-only">Previous</span>
	</a>
	<a class="carousel-control-next" href="#carousel" role="button" data-bs-slide="next">
	    <span class="carousel-control-next-icon" aria-hidden="true"></span>
	    <span class="sr-only">Next</span>
	</a>
	<div class="carousel-background"></div>
	
	</div>

HTML;

	}


	return $output;

}