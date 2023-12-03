<?php

use function mikuclub\print_breadcrumbs_component;
use function mikuclub\print_page_edit_link;

get_header();


?>


<div class="page-header">

	<?php echo print_breadcrumbs_component(); ?>



	<div class="text-end">
		<?php echo print_page_edit_link(); ?>
	</div>
</div>

<?php
while (have_posts())
{
	the_post();
?>

	<div class="page-content my-3">

		<?php the_content(); ?>

	</div>


<?php
	//comments_template( '', true );
}
?>


<?php get_footer(); ?>