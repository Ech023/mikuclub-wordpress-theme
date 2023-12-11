<?php

namespace mikuclub;

get_header();

$search_value = sanitize_text_field(get_query_var('s'));
$search_form = post_list_header_search_form();


$breadcrumbs = print_breadcrumbs_component();
$post_list_header_category = print_post_list_header_category();
$post_list_header_order =  print_post_list_header_order();
$post_list_header_download_type = print_post_list_header_download_type();
$post_list_header_user_black_list = print_post_list_header_user_black_list();

$post_list_component =  print_post_list_component();

$output = <<<HTML

	{$breadcrumbs}

    <div class="my-4 w-md-50 mx-auto">
        {$search_form}
    </div>

    <div class="my-2 border-bottom">
        {$post_list_header_category}
    </div>
	{$post_list_header_order}
    {$post_list_header_download_type}
    {$post_list_header_user_black_list}

	{$post_list_component}
	
	
HTML;

echo $output;

//get_sidebar();
get_footer();
