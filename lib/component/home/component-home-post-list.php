<?php
namespace mikuclub;

/**
 * 输出最新文章列表
 *
 * @param My_Post_Model[] $post_list
 * @param string $list_title
 * @param string $icon_class 图标名
 * @param string|null $more_link
 *
 * @return string html输出
 */
function print_home_post_list_component( $post_list, $list_title, $icon_class, $more_link = null) {

	$post_list_html = '';

	foreach ( $post_list as $my_post ) {

		$post_list_html .= <<<HTML
			
		    <div class="col">
				<div class="card border-0">
					<div class="card-img-container position-relative ">
						<div class="position-absolute end-0 bottom-0 me-1 mb-1">
							<div class="right-badge bg-transparent-half text-light rounded p-1 fs-75">
								<i class="fa-solid fa-eye"></i>
								{$my_post->post_views}
							</div>
						</div>
						<img class="card-img-top bg-light-2" src="{$my_post->post_image}" alt="{$my_post->post_title}"/>
					</div>
					<div class="card-body py-2 ">
						<div class="text-center text-2-rows">
							<a class="card-link stretched-link fs-75 fs-sm-875" title="{$my_post->post_title}" href="{$my_post->post_href}" target="_blank">
								{$my_post->post_title}
							</a>
						</div>
						
					</div>
				</div>
			</div>

HTML;

	}

	if ( $post_list_html ) {

		//如果没有链接,就隐藏相关按钮
		$more_link_class = !$more_link ? 'd-none' : '';

		//最终输出内容
		$post_list_html = <<<HTML

			<div class="home-post-list my-2 pb-2 border-bottom">
				<div class="row align-items-center my-2">
					<div class="col-auto">
						<h5 class="mb-0 fw-bold">
							<i class="{$icon_class}"></i> {$list_title}
						</h5>
					</div>
					<div class="col-auto">
						<a class="btn btn-sm btn-outline-secondary px-4 {$more_link_class}"  title="{$list_title}" href="{$more_link}" target="_blank">
						更多 <i class="fa-solid fa-angle-right"></i>
						</a>
					</div>
				</div>
				<div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xxl-6 gy-2">
					{$post_list_html}
				</div>
			</div>

HTML;

	}

	return $post_list_html;

}