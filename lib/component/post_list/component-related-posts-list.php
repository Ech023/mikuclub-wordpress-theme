<?php

namespace mikuclub;

use mikuclub\constant\Config;

/**
 * 相关文章组件
 * @return string
 */
function print_related_post_list_component()
{

    $count = Config::RELATED_POST_LIST_LENGTH;

    $post_id = get_the_ID();

    $related_post_list = get_related_post_list($post_id);


    $related_post_list_component = '';
    foreach ($related_post_list as $my_post)
    {

        $related_post_list_component .= <<< HTML
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
                    <div class="card-body py-2">
                        <div class="text-2-rows">
                            <a class="card-link stretched-link fs-75 fs-sm-875" title="{$my_post->post_title}" href="{$my_post->post_href}" >
                                {$my_post->post_title}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

HTML;
    }

    $output = '';
    //确保有内容才输出
    if ($related_post_list_component)
    {
        $output = <<<HTML

        <div class="related-posts my-2">
            <div class="list-header my-2">
                <h5 class="mb-0 fw-bold">
                    相关推荐
                </h5>
            </div>
           <div class="list-body row row-cols-2 row-cols-lg-3 row-cols-xl-6"> 
                {$related_post_list_component}
           </div>
        </div>
        
      

HTML;
    }

    return $output;
}
