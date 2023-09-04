<?php

use function mikuclub\post_list_component;

get_header(); 

?>

    <div class="content">
        <header class="archive-header">
            <h1><?php
				if ( is_day() ) {
					 the_time( 'Y年m月j日' );
				} elseif ( is_month() ) {
					 the_time( 'Y年m月' );
				} elseif ( is_year() ) {
					 the_time( 'Y年' );
				}
				?>的内容</h1>
        </header>
	    <?php  echo post_list_component() ?>
    </div>


<?php

//get_sidebar();
get_footer();

?>