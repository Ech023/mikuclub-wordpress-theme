<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;

use function mikuclub\print_breadcrumbs_component;

use function mikuclub\get_theme_option;


get_header(); ?>

<?php

$breadcrumbs = print_breadcrumbs_component();

$ad_banner = '';
if (get_theme_option(Admin_Meta::CATEGORY_TOP_ADSENSE_ENABLE))
{
    $ad_banner = '<div class="pop-banner text-center my-2 pb-2 border-bottom">' .  get_theme_option(Admin_Meta::CATEGORY_TOP_ADSENSE) . '</div>';
}

$post_list_header = print_post_list_header_component();
$post_list_component = print_post_list_component();

$output = <<<HTML

	{$breadcrumbs}

	{$ad_banner}

	{$post_list_header}

	{$post_list_component}
	
	
HTML;

echo $output;


//get_sidebar(); 
get_footer();

?>