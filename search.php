<?php

use function mikuclub\breadcrumbs_component;
use function mikuclub\post_list_component;
use function mikuclub\print_categoria_radio_box;

get_header();

?>
<div class="content">



    <header class="search-header">

        <h4>
            <?php echo breadcrumbs_component(); ?>
        </h4>


        <div class="my-4">
            <form class="search-form">
                <div class="input-group mb-4">

                    <input type="text" class="form-control form-control-lg search-form" name="search" autocomplete="off" value="<?php echo sanitize_text_field(get_query_var('s')); ?>" />

                    <button type="submit" class="btn btn-lg btn-miku"><i class="fa-solid fa-search"></i> 搜索</button>

                </div>

                <?php echo print_categoria_radio_box(); ?>


            </form>
        </div>


    </header>

    <?php
    echo post_list_component();
    ?>


</div>

<?php
//get_sidebar();
get_footer();
?>