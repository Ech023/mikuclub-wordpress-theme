<?php
/*
	template name: 收藏页面
*/

//如果未登陆 重定向回首页

use function mikuclub\breadcrumbs_component;
use function mikuclub\next_page_button;
use function mikuclub\print_category_button_component;
use function mikuclub\redirect_for_not_logged;

redirect_for_not_logged();

get_header();

?>

<div class="content page-favorite">

    <header class="page-header">

        <h4 class="my-4">
            <?php echo breadcrumbs_component(); ?>
        </h4>

        <div class="my-4">
            
            <div class="inside-search ">

                <div class="input-group mb-4">

                    <input type="text" class="search form-control form-control-lg" placeholder="在收藏夹内搜索" autocomplete="off" />

                    <button class="btn btn-miku px-4">
                        <i class="fa-solid fa-search"></i>
                    </button>

                </div>

                <?php echo print_category_button_component(); ?>

            </div>

        </div>

        <div class="my-2">
            注: 如果投稿被UP退回或删除, 将会从收藏夹里消失
        </div>




    </header >



    <div class="page-content post-list my-3">



    </div>

    <div class="my-2">
        <?php echo next_page_button('下一页'); ?>

    </div>


</div>

<?php get_footer(); ?>