<?php

namespace mikuclub;

use mikuclub\constant\Config;

/**
 * 相关文章组件
 * @return string
 */
function related_posts_component()
{

    $count = Config::RELATED_POST_LIST_LENGTH;

    $post_id = get_the_ID();

    $related_post_list = get_related_post_list($post_id);


    $related_post_list_html = '';
    foreach ($related_post_list as $my_post)
    {

        $related_post_list_html .= <<< HTML

            <div class="col card border-0 my-1">
                            <div class="card-img-container position-relative ">
                                <div class="position-absolute end-0 bottom-0 me-1 mb-1">
                                        <div class="right-badge bg-transparent-half text-light rounded small p-1">
                                                 <i class="fa-solid fa-eye"></i> {$my_post->post_views}
                                       </div>
                                </div>
                                <img class="card-img-top" src="{$my_post->post_image}" alt="{$my_post->post_title}"/>
                            </div>
                            
                            <div class="card-body  py-2 text-center text-2-rows">
                                 <a class="card-link stretched-link small" title="{$my_post->post_title}" href="{$my_post->post_href}" >
                                    {$my_post->post_title}
                                </a>
                            </div>
                        </div>

HTML;
    }

    $output = '';
    //确保有内容才输出
    if ($related_post_list_html)
    {
        $output = <<<HTML

        <div class="related-posts my-4">
        
            <div class="list-header row my-3">
                    <h4 class="col">
                        相关推荐
                    </h4>
            </div>
           <div class="list-body row row-cols-2 row-cols-lg-4"> 
            {$related_post_list_html}
           </div>
        </div>
        
      

HTML;
    }

    return $output;
}
