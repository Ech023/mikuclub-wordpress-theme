<?php

use mikuclub\constant\Admin_Meta;

use function mikuclub\print_breadcrumbs_component;

use function mikuclub\get_theme_option;
use function mikuclub\post_list_component;

get_header(); ?>


<header class="archive-header tag-header">
   
        <?php echo print_breadcrumbs_component(); ?>
    
</header>

<?php

if (get_theme_option(Admin_Meta::CATEGORY_TOP_ADSENSE_ENABLE))
{
    echo '<div class="pop-banner  text-center my-4">' . get_theme_option(Admin_Meta::CATEGORY_TOP_ADSENSE) . '</div>';
}

?>


<?php echo post_list_component() ?>



<?php

//get_sidebar(); 
get_footer();

?>