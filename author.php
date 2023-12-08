<?php

namespace mikuclub;

use function mikuclub\get_custom_user;

use function mikuclub\print_author_statistics;
use function mikuclub\print_user_avatar;
use function mikuclub\print_user_badges;

get_header();

$current_user_id = get_current_user_id();
$author = get_custom_user(get_queried_object_id());

$user_avatar = print_user_avatar($author->user_image, 100);
$user_badges = print_user_badges($author->id);
$author_statistics = print_author_statistics($author->id);

$author_buttons_element = '';
//必须是登陆用户, 并且不能是作者自己
if ($current_user_id != $author->id)
{
    $author_buttons_element = print_user_follow_and_message_button($author->id);
}

$user_info = <<<HTML

        <div class="author-header my-2" data-author-id="{$author->id}">


            <div class="row">
                <div class="col-12 col-sm-auto align-self-center">
                    <div class="text-center">
                        {$user_avatar}
                    </div>
                </div>
                <div class="col mb-2 mb-xl-0">
                    <div class="fs-5 fw-bold text-center text-sm-start m-1">
                        {$author->display_name}
                    </div>

                    <div class="m-1 text-center text-sm-start">
                        {$user_badges}
                    </div>

                    <div class="my-2 overflow-hidden text-center text-sm-start text-dark-2" style="max-height: 96px;">
                        {$author->user_description}
                    </div>

                    <div class="user-functions row gx-2 justify-content-center justify-content-sm-start">
                        {$author_buttons_element}
                    </div>
                </div>
                <div class="col-12 col-xl-6 mt-2 mt-xl-0">
                    <div class="row row-cols-3 row-cols-md-6 text-center fs-75 fs-sm-875 g-2 h-100 align-content-center">
                        {$author_statistics}
                    </div>
                </div>
                
            </div>

            <div class="border-bottom my-2">
            </div>


        </div>

HTML;




$breadcrumbs = print_breadcrumbs_component();
$search_form = post_list_header_search_form('搜索UP的投稿');
$post_list_header_category = print_post_list_header_category();
$post_list_header =  print_post_list_header_component();
$post_list_component =  print_post_list_component();

$output = <<<HTML

	{$breadcrumbs}

    {$user_info}

    <div class="my-4 w-md-50 mx-auto">
        {$search_form}
    </div>
    <div class="my-2 border-bottom">
        {$post_list_header_category}
    </div>
	{$post_list_header}

	{$post_list_component}
	
	
HTML;

echo $output;

get_footer();
