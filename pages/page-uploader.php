<?php

/*
	template name: up主个人中心
	description: 显示统计数据, 管理投稿
*/

//如果未登陆 重定向回首页

use function mikuclub\breadcrumbs_component;
use function mikuclub\next_page_button;
use function mikuclub\print_author_statistics;
use function mikuclub\redirect_for_not_logged;

redirect_for_not_logged();


get_header();


$user = wp_get_current_user();

?>

<div class="content page-uploader">

    <header class="page-header">
        <h4 class="my-4">
            <?php echo breadcrumbs_component(); ?>
        </h4>

        <div class="row row-cols-3 row-cols-md-6 text-center g-2">
            <?php echo print_author_statistics($user->ID); ?>
        </div>

        <div class="my-4">

            <div class="input-group">
                <input class="form-control" name="search-post" placeholder="投稿搜索..." value="<?php echo (isset($_GET['s ']) ? $_GET['s '] : '') ?>" autocomplete="new-password" />

                <button class="search-post-button btn btn-miku" type="submit">搜索</button>

            </div>



        </div>

    </header>

    <hr />

    <div class="page-content post-list my-4">


    </div>

    <div class="my-2">
        <?php echo next_page_button('下一页'); ?>
        <input type="hidden" name="paged" value="0">
        <input type="hidden" name="author" value="<?php echo $user->ID; ?>">
    </div>


</div>

<?php get_footer(); ?>