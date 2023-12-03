<?php

/*
	template name: up主个人中心
	description: 显示统计数据, 管理投稿
*/

//如果未登陆 重定向回首页

use mikuclub\User_Capability;

use function mikuclub\print_breadcrumbs_component;
use function mikuclub\next_page_button;
use function mikuclub\print_author_statistics;


User_Capability::prevent_not_logged_user();


get_header();


$user = wp_get_current_user();

?>

<div class="page-uploader">

    <div class="page-header">
       
            <?php echo print_breadcrumbs_component(); ?>
       

        <div class="row row-cols-3 row-cols-md-6 text-center g-2">
            <?php echo print_author_statistics($user->ID); ?>
        </div>

        <div class="my-4">

            <div class="input-group">
                <input class="form-control" name="search-post" placeholder="投稿搜索..." value="<?php echo (isset($_GET['s ']) ? $_GET['s '] : '') ?>" autocomplete="new-password" />

                <button class="search-post-button btn btn-miku" type="submit">搜索</button>

            </div>



        </div>

    </div>

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