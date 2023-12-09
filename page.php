<?php


namespace mikuclub;

use function mikuclub\print_breadcrumbs_component;
use function mikuclub\print_page_edit_link;

get_header();


while (have_posts())
{
	the_post();

	$breadcrumbs = print_breadcrumbs_component();
	$page_edit_link = print_page_edit_link();
	$content = get_the_content();

	$output = <<<HTML

		<div class="page-default">

			<div class="page-header row">

				<div class="col">
					{$breadcrumbs}
				</div>

				<div class="col-auto ms-auto">
					{$page_edit_link}
				</div>

			</div>
			<div class="page-content my-2">
				{$content}
			</div>
			
		</div>

HTML;

	echo $output;
}

get_footer();
