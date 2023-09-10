<?php

use function mikuclub\breadcrumbs_component;
use function mikuclub\print_page_edit_link;

get_header();


?>

    <div class="content">

        <header class="page-header">
			<h4 class="my-4">
				<?php echo breadcrumbs_component(); ?>
            </h4>


            <div class="text-end">
				<?php echo print_page_edit_link(); ?>
            </div>
        </header>

		<?php
		while ( have_posts() ) {
			the_post();
			?>

            <div class="page-content my-3">

				<?php the_content(); ?>

            </div>


			<?php
			//comments_template( '', true );
		}
		?>


    </div>

<?php get_footer(); ?>