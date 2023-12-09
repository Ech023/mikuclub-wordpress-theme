<?php

namespace mikuclub;

use mikuclub\constant\Page_Type;
use mikuclub\constant\Post_Template;

/**
 * 文章列表组件
 * @param array<string,mixed> $custom_post_query
 * @param string $post_template
 * @return string
 */
function print_post_list_component($custom_post_query = [], $post_template = Post_Template::DEFAULT)
{
	global $wp_query;


	$post_list_html = '';


	$class_post_list = 'row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xxl-6';
	//如果文章模板为 投稿管理类型
	if ($post_template === Post_Template::MANAGE_POST)
	{
		//更改默认的文章列表类名
		$class_post_list = '';
	}

	$parameters = array_merge($wp_query->query, $custom_post_query);
	// unset($parameters['pagename']);

	$parameters[Post_Query::CUSTOM_PAGE_TYPE] = Page_Type::get_current_type();
	//储存列表的请求参数
	$json_parameters = htmlspecialchars(json_encode($parameters));



	$output = <<<HTML
		<div class="post-list-container" data-parameters='{$json_parameters}' data-post-template="{$post_template}">
			
			<div class="post-list row my-2 {$class_post_list}">
				$post_list_html
			</div>
			<!-- 自动加载列表的触发标记 -->
			<div class="post_list_footer">
			</div>
			
		</div>

HTML;

	return $output;
}

/**
 *	@deprecated version
 *	@return string
 */
function print_default_post_list_component()
{
	//获取默认文章列表
	$post_list = get_wp_query_post_list();

	$post_list_html = '';

	foreach ($post_list as $my_post)
	{

		$author_avatar = '';
		$author_name   = '';
		//不在作者页面才显示
		if (!is_author())
		{
			$avatar_image = print_user_avatar($my_post->post_author->user_image, 40);
			$author_avatar = <<<HTML

				<a href="{$my_post->post_author->user_href}" title="查看UP主空间" target="_blank">
                   {$avatar_image}
                </a>


HTML;
			$author_name = <<<HTML
				<a class="card-link small text-dark-2" title="查看UP主空间" href="{$my_post->post_author->user_href}" target="_blank">
                    {$my_post->post_author->display_name}
                </a>
HTML;
		}

		//当前分类id和文章所属子分类不一样的时候才输出分类链接
		/*
		$category_link = '';
		if ( get_queried_object_id() != $my_post->post_cat_id ) {
			$category_link = '
			<div class="right-badge bg-transparent-half rounded small p-1">
                        <a class=" text-light " href="' . $my_post->post_cat_href . '" title="查看相关分类"  target="_blank">
							' . $my_post->post_cat_name . '
                        </a>
            </div>
			';
		}*/

		$post_container_class = add_mask_class_to_black_user_post_container($my_post->post_author->id);


		$post_list_html .= <<< HTML

 		<div class="col">
		 	<div class="card border-0 my-1 {$post_container_class}">
    
				<div class="card-img-container position-relative">
				
					
					<div class="position-absolute end-0 bottom-0 me-1 mb-1 text-light fs-75">
						<div class="d-none d-sm-inline-block bg-transparent-half rounded p-1">
							<i class="fa-solid fa-thumbs-up"></i> {$my_post->post_likes}
						</div>
						<div class="d-none d-sm-inline-block bg-transparent-half rounded p-1">
							<i class="fa-solid fa-comments"></i> {$my_post->post_comments}
						</div>
						<div class="d-inline-block bg-transparent-half rounded p-1">
							<i class="fa-solid fa-eye"></i> {$my_post->post_views}
						</div>
					</div>
			
					<div>
						<a class="" href="{$my_post->post_href}" title="{$my_post->post_title}" target="_blank">
							<img class="card-img-top bg-light-2" src="{$my_post->post_image}" alt="{$my_post->post_title}" />
						</a>
					</div>
		
				</div>
				<div class="row my-2 align-items-center">
				
					<div class="col-12 mb-2">
						<div class="post-title text-3-rows">
							<a class="fs-75 fs-sm-875" href="{$my_post->post_href}" title="{$my_post->post_title}" target="_blank">
								{$my_post->post_title}
							</a>
						</div>
						
					</div>
					<div class="col-auto d-none d-md-block">
						{$author_avatar}
					</div>
					<div class="col">

						<div class="text-1-rows">
							{$author_name}
						</div>
						<div class="fs-75 d-none d-md-block text-dark-2">
							{$my_post->post_modified_date}
						</div>

					</div>

				
				</div>
			</div>
        </div>


HTML;
	}

	return $post_list_html;
}
