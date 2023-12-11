<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Config;

/**
 * 幻灯片 组件
 *	@param int $cat_id
 * @return string
 */
function print_sticky_post_slide_component($cat_id)
{

    //获取置顶文章列表
    $sticky_post_list = get_sticky_post_list($cat_id);

    //如果是首页
    if (is_home() && !get_query_var(Post_Query::PAGED))
    {
        //如果存在第一单元广告
        if (get_theme_option(Admin_Meta::HOME_SLIDE_TOP_LIST_FIRST_ENABLE))
        {
            //抓取最后一个文章
            $last_post = array_pop($sticky_post_list);
            $last_post->post_image = get_theme_option(Admin_Meta::HOME_SLIDE_TOP_LIST_FIRST_IMAGE);
            $last_post->post_title = get_theme_option(Admin_Meta::HOME_SLIDE_TOP_LIST_FIRST_TITLE);
            $last_post->post_href = get_theme_option(Admin_Meta::HOME_SLIDE_TOP_LIST_FIRST_LINK);

           

            //把插入广告文章元素
            $first_pub_offset = Config::STICKY_POST_FIRST_LIST_LENGTH;
            array_splice($sticky_post_list, $first_pub_offset, 0, [$last_post]);
        }
        //如果存在第二单元广告
        if (get_theme_option(Admin_Meta::HOME_SLIDE_TOP_LIST_SECOND_ENABLE))
        {
            //抓取最后一个文章
            $last_post = array_pop($sticky_post_list);
            $last_post->post_image = get_theme_option(Admin_Meta::HOME_SLIDE_TOP_LIST_SECOND_IMAGE);
            $last_post->post_title = get_theme_option(Admin_Meta::HOME_SLIDE_TOP_LIST_SECOND_TITLE);
            $last_post->post_href = get_theme_option(Admin_Meta::HOME_SLIDE_TOP_LIST_SECOND_LINK);

            //把插入广告文章元素
            $second_pub_offset = Config::STICKY_POST_FIRST_LIST_LENGTH + 1;
            array_splice($sticky_post_list, $second_pub_offset, 0, [$last_post]);
        }
    }



    $first_part = print_sticky_post_slide_component_first_part(array_slice($sticky_post_list, 0, Config::STICKY_POST_FIRST_LIST_LENGTH));
    $second_part = print_sticky_post_slide_component_second_part(array_slice($sticky_post_list, Config::STICKY_POST_FIRST_LIST_LENGTH, Config::STICKY_POST_SECONDARY_LIST_LENGTH));

    $output = '';


    //最终输出内容
    $output = <<<HTML
        <div class="sticky_post_slide_component my-2">
            <div class="row gx-2">
                <div class="col-12 col-lg-6 col-xl-4 ">
                    {$first_part}
                </div>
                <div class="col-12 col-lg-6 col-xl-8 mt-2 mt-lg-auto">
                    {$second_part}
                </div>
            </div>
        </div>

HTML;


    return $output;
}


/**
 * 幻灯片 组件 第一部分
 *	@param My_Post_Sticky_Model[] $sticky_post_list
 * @return string
 */
function print_sticky_post_slide_component_first_part($sticky_post_list)
{
    $output = '';
    if (count($sticky_post_list) > 0)
    {

        //生成指示符
        $carousel_indicators_list = '';
        //生产幻灯片
        $carousel_item_list = '';

        for ($i = 0; $i < count($sticky_post_list); $i++)
        {
            $post_sticky = $sticky_post_list[$i];
            $active_class = ($i === 0) ? 'active' : '';

            $carousel_indicators_list .= <<<HTML
             <button type="button" data-bs-target="#sticky_post_slide" data-bs-slide-to="{$i}"
                 class="{$active_class}"></button>
HTML;

            $carousel_item_list .= <<< HTML
         
             <div class="carousel-item {$active_class}" data-bs-interval="10000">
                 <a class="position-relative" style="" href="{$post_sticky->post_href}" target="_blank">
                     <img class="d-block w-100 bg-light-2" src="{$post_sticky->post_image_large}" alt="{$post_sticky->post_title}" skip_lazyload />
                     <div class="carousel-caption d-none d-sm-block ">
                         <div class="small">{$post_sticky->post_title}</div>
                     </div>
                      <!-- 背景虚化用来突出标题 -->
                    <div class="carousel-background position-absolute bottom-0 w-100 rounded-bottom"></div>
                 </a>
             </div>
 
HTML;
        }



        $output = <<<HTML
    
        <div id="sticky_post_slide" class="carousel slide carousel-fade top-slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                {$carousel_indicators_list}
            </div>
            <div class="carousel-inner">
                {$carousel_item_list}
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#sticky_post_slide" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#sticky_post_slide" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
           
        </div>
    
HTML;
    }

    return $output;
}


/**
 * 幻灯片 组件 第二部分
 *
 * @param My_Post_Sticky_Model[] $sticky_post_list
 * @return string
 */
function print_sticky_post_slide_component_second_part($sticky_post_list)
{

    $output = '';
    if (count($sticky_post_list) > 0)
    {

        $post_list_html = '';

        for ($i = 0; $i < count($sticky_post_list); $i++)
        {
            $post_sticky = $sticky_post_list[$i];

            //如果是超过第四个位置的文章
            $display_class = $i >= 6 ? 'd-none d-xl-flex' : "";

            $post_list_html .= <<<HTML
	
                <div class="col {$display_class}">
                    <div class="card border-0">
                        <div class="card-img-container position-relative ">
                            <div class="position-absolute end-0 bottom-0 me-1 mb-1">
                                <div class="right-badge bg-transparent-half text-light rounded p-1 fs-75">
                                    <i class="fa-solid fa-thumbs-up"></i>
                                    {$post_sticky->post_likes}
                                </div>
                            </div>
                            <img class="card-img-top bg-light-2" src="{$post_sticky->post_image}" alt="{$post_sticky->post_title}"/>
                        </div>
                        <div class="card-body py-2">
                            <div class="text-2-rows">
                                <a class="card-link stretched-link fs-75 fs-sm-875"  title="{$post_sticky->post_title}" href="{$post_sticky->post_href}" target="_blank">
                                    {$post_sticky->post_title}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

HTML;
        }

        //最终输出内容
        $output = <<< HTML

            <div class="row row-cols-2 row-cols-lg-3 row-cols-xl-4  top-hot-post-list g-2">
                {$post_list_html}
            </div>
HTML;
    }


    return $output;
}
