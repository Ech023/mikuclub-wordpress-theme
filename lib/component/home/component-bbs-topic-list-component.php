<?php

namespace mikuclub;

/**
 * 主页帖子列表 组件
 * @return string html内容
 */
function print_bbs_topic_list_component()
{


	//获取帖子列表
	$topic_list = get_recent_forum_topic_list(12);
	//论坛地址
	$forums_link = get_home_url() . '/forums';

	$topic_list_html = '';

	foreach ($topic_list as $topic)
	{

		$topic_list_html .= <<< HTML

		<div class="col">
			<div>
				<a class="lh-lg small" title="{$topic->post_title}" href="{$topic->post_href}" target="_blank" >
					<i class="fa-solid fa-comment-dots" aria-hidden="true"></i>
					<span class="mx-2">{$topic->post_title}</span>
					<span class="badge text-bg-primary">
						<i class="fa-solid fa-user"></i>
						<span class="d-d-inline-block text-truncate" style="max-width: 100px;">{$topic->post_author->display_name}</span>
					</span>
					<span class="badge text-bg-secondary">回复 {$topic->post_replay_number} / 查看 {$topic->post_views}</span>
			
				</a>
			</div>
		</div>

HTML;
	}


	//最终输出内容
	return <<< HTML

		<div class="topic-list my-2 pb-2 border-bottom">
			<div class="row align-items-center my-2">
				<div class="col-auto">
					<h5 class="mb-0 fw-bold">
						<i class="fa-solid fa-rss" aria-hidden="true"></i> 论坛最新帖子
					</h5>
				</div>
				<div class="col-auto">
					<a class="btn btn-sm btn-outline-secondary"  title="进入论坛" href="{$forums_link}" target="_blank">
						进入论坛 <i class="fa-solid fa-angle-right"></i>
					</a>
				</div>
			</div>
			<div class="row row-cols-1 row-md-cols-2 row-cols-xl-3 gy-2">
			
					{$topic_list_html}
			
			</div>
		</div>

HTML;
}
