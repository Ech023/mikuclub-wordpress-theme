<?php

namespace mikuclub;

use mikuclub\constant\Page_Type;

/**
 * 文章列表组件
 * @return string
 */
function post_list_component()
{
	global $wp_query;

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

			$author_avatar .= '
     
            <a href="' . $my_post->post_author->user_href . '" title="查看UP主空间" target="_blank">
                ' . print_user_avatar($my_post->post_author->user_image, 40) . '
            </a>';

			$author_name .= '

            
                <a class="card-link small" title="查看UP主空间"
                   href=" ' . $my_post->post_author->user_href . '" target="_blank">
                    ' . $my_post->post_author->display_name . '
                </a>
            ';
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

 		<div class="col card border-0 my-1 {$post_container_class}">

    
            <div class="card-img-container position-relative">
                <div class="position-absolute end-0 top-0 me-1 mt-1">
                    
                </div>
                
                <div class="position-absolute end-0 bottom-0 me-1 mb-1">
                    <div class="right-badge bg-transparent-half text-light rounded small p-1">
                        <i class="fa-solid fa-eye"></i> {$my_post->post_views}
                    </div>
                </div>
          
          		<div>
          			<a class="" href="{$my_post->post_href}" title="{$my_post->post_title}" target="_blank">
          		              <img class="card-img-top" src="{$my_post->post_image}" alt="{$my_post->post_title}" />
                      </a>
				</div>
  
                                          
            </div>
            <div class="card-body  my-2 py-2 row g-0">

                <div class="col-3 d-none d-md-block">
                    {$author_avatar}
                </div>
                <div class="col-12 col-md-9">
             
                     <h6 class="post-title text-1-rows text-2-rows-sm small medium-bold-sm">
                        <a class="" href="{$my_post->post_href}" title="{$my_post->post_title}" target="_blank">
							{$my_post->post_title}
                        </a>
                    </h6>
                    
                    <div class="my-2">
					    {$author_name}
					</div>
					
                    
	                <div class="small d-none d-md-block ">
	                        <span class="me-2"><i class="fa-solid fa-clock"></i> {$my_post->post_date} </span>
	                        <span class=""><i class="fa-solid fa-comments"></i> {$my_post->post_comments}</span>
	                        <span class="me-1 d-none"><i class="fa-solid fa-star"></i> {$my_post->post_likes}</span>
	                        <span class="d-none"><i class="fa-solid fa-heart"></i> {$my_post->post_favorites}</span>
	                </div>
					

                </div>
                

            </div>
        </div>


HTML;
	}


	// $post_list_output = '';
	// $post_list_output = post_list_order_component();
	$post_list_header = print_post_list_header_component();
	$post_list_pagination = pagination_component();

	//如果列表为空
	if (count($post_list) === 0)
	{
		$post_list_html .= <<<HTML

			<div class="m-5 mw-100 flex-fill">
    			<h4 class="text-center">抱歉, 没有找到相关内容</h4>
    			<br/>
				<br/>
				<br/>
				<br/>
				<br/>
			</div>

HTML;

	}

	$parameters = $wp_query->query;
	$parameters[Post_Query::CUSTOM_PAGE_TYPE] = Page_Type::get_current_type();
	//储存列表的请求参数
	$json_parameters = htmlspecialchars(json_encode($parameters));

	$output = <<<HTML
		<div class="post-list-container" data-parameters='{$json_parameters}'>
			{$post_list_header}

			<div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xxl-5 post-list my-2">
				$post_list_html
			</div>
			<!-- 自动加载列表的触发标记 -->
			<div class="post_list_footer">
			</div>
			
		</div>

HTML;

	return $output;
}
