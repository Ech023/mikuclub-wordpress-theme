<?php
namespace mikuclub;

/**
 * 输出最新文章列表
 *
 * @param My_Post_Hot[] $post_list
 * @param string $list_title
 * @param string $more_link
 * @param string $icon 图标名
 *
 * @return string html输出
 */
function recently_posts_component( $post_list, $list_title, $more_link, $icon) {

	$post_list_html = '';

	foreach ( $post_list as $my_post ) {

		$post_list_html .= <<<HTML
			

			<div class="col card border-0">
				<div class="card-img-container position-relative ">
					<div class="position-absolute end-0 bottom-0 me-1 mb-1">
				            <div class="right-badge bg-transparent-half text-light rounded small p-1">
				                     <i class="fa-solid fa-eye"></i> {$my_post->post_views}
		                   </div>
					</div>
				    <img class="card-img-top" src="{$my_post->post_image}" alt="{$my_post->post_title}"/>
				</div>
				
				<div class="card-body  py-2 text-center text-2-rows">
					 <a class="card-link stretched-link small" title="{$my_post->post_title}" href="{$my_post->post_href}" target="_blank">
					 	{$my_post->post_title}
					</a>
				</div>
                  
            </div>

HTML;

	}

	if ( $post_list_html ) {

		//最终输出内容
		$post_list_html = <<<HTML

		<div class="post-recently-list home-list">
			<div  class="list-header row my-3">
				<h4 class="col">
					 <a title="{$list_title}" href="{$more_link}" target="_blank">
			                <i class="{$icon}"></i> {$list_title}
	                  </a>
		        </h4>
		        <div class="more-link col d-flex justify-content-end align-items-center">
		            <a class="btn btn-outline-secondary" title="{$list_title}" href="{$more_link}" target="_blank">
		                更多 <i class="fa-solid fa-angle-right"></i>
		            </a>
		        </div>
			</div>
			<div class="row row-cols-2 row-cols-md-4 row-cols-xl-5 gy-2">
				{$post_list_html}
			</div>
		</div>

HTML;

	}

	return $post_list_html;

}