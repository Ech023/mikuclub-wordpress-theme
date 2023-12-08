<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;

use function mikuclub\get_bottom_menu;
use function mikuclub\get_friends_links;
use function mikuclub\get_theme_option;

?>


<!--关闭content-->
</div>
<!--关闭section-->
</section>


<footer id="footer" class="footer border-top mt-2 mb-5 px-3 px-md-4 ">

        <div class="row align-items-center">
            <div class="col-auto">
                <div class="mt-3 mb-1">
                    <a class="fw-bold" href="<?php echo get_home_url(); ?>">
                        <?php echo get_option('blogname'); ?>
                        <span>© 2014 - <?php echo date('Y'); ?></span>
                    </a>
                </div>

                <div class="small text-dark-2">
                    <?php echo get_theme_option(Admin_Meta::SITE_ANNOUNCEMENT_BOTTOM); ?>
                </div>
            </div>
            <div class="col-auto">
                <nav class="navbar navbar-expand small py-0">
                    <?php echo get_bottom_menu(); ?>
                </nav>
            </div>



            <div class="col-12 ">
                <!-- 随机语句-->
                <div id="custom-phrase" class="text-info">
                </div>
            </div>

        </div>

        <?php

        echo print_footer_statistics_component();

        //只有在 首页的时候 才会输出
        if (is_home() && !get_query_var(Post_Query::PAGED))
        {
            // 友情链接
            echo get_friends_links();
        }
        ?>


        <div class="py-3"></div>

</footer>


<?php wp_footer(); ?>

<!--关闭网页主体-->
</div>

<?php
echo print_footer_js_script_component();
echo print_phone_sidebar_menu_component();
echo print_float_bottom_menu_bar_component();
?>

</body>

</html>