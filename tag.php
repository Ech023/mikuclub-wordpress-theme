<?php get_header(); ?>

    <div class="content">
        <header class="archive-header tag-header">
            <h4>
		        <?php echo breadcrumbs_component(); ?>
            </h4>
        </header>

        <?php

            if ( dopt( 'd_adindex_02_b' ) ) {
                echo '<div class="pop-banner  text-center my-4">' . dopt( 'd_adindex_02' ) . '</div>';
            }

        ?>


	    <?php  echo post_list_component() ?>

    </div>


<?php

//get_sidebar(); 
get_footer();

?>