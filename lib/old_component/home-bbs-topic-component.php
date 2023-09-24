<?php
namespace mikuclub;
/**
 * 主页帖子列表 组件
 * @return string html内容
 */
function home_bbs_topic_component() {


	//获取帖子列表
	$topic_list = get_recent_forums_topic( 10 );
	//论坛地址
	$forums_link = get_home_url() . '/forums';

	$topic_list_html = '';

	foreach ( $topic_list as $topic ) {

		$topic_list_html .= <<< HTML

		<div class="single-topic col">
			<a title="{$topic->post_title}" href="{$topic->post_href}" target="_blank" >
				<i class="fa-solid fa-comment-dots" aria-hidden="true"></i>
				 {$topic->post_title}
				 <span class="badge bg-miku">{$topic->post_date}</span>
			</a>
		</div>

HTML;

	}


	//最终输出内容
	return <<< HTML

<div class="topic-list home-list my-3">
	<div  class="list-header row my-3">
		<h4 class="col">
			<a class=""  title="最新帖子" href="{$forums_link}" target="_blank">
                <i class="fa-solid fa-rss" aria-hidden="true"></i> 论坛最新帖子
             </a>
        </h4>
        <div class="more-link col d-flex justify-content-end align-items-center">
            <a class="btn btn-outline-secondary"  title="论坛最新帖子" href="{$forums_link}" target="_blank">
                更多 <i class="fa-solid fa-angle-right"></i>
            </a>
        </div>
	</div>
	<div class="row row-cols-2 gy-3">
	
			{$topic_list_html}
	
	</div>

</div>


HTML;

}

