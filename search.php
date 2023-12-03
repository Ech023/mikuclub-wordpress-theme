<?php

namespace mikuclub;

get_header();

?>

<header class="search-header">

   
    <?php echo print_breadcrumbs_component(); ?>
    

    <div class="my-4">
        <form class="site-search-form">
            <div class="input-group mb-4">

                <input type="text" class="form-control form-control-lg" name="search" autocomplete="off" value="<?php echo sanitize_text_field(get_query_var('s')); ?>" />

                <button type="submit" class="btn btn-lg btn-miku"><i class="fa-solid fa-search"></i> 搜索</button>

            </div>


            <?php echo print_category_button_component(); ?>

            <?php echo print_sub_category_button_component(); ?>

        </form>
    </div>

</header>

<?php

echo post_list_component();
?>



<?php
//get_sidebar();
get_footer();
?>