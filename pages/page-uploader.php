<?php

/*
	template name: up主个人中心
	description: 显示统计数据, 管理投稿
*/

//如果未登陆 重定向回首页
redirect_for_not_logged();


get_header();


$user = wp_get_current_user();

?>

<div class="content page-uploader">

    <header class="page-header">
        <h4 class="my-4">
            <?php echo breadcrumbs_component(); ?>
        </h4>

        <div class="row text-center my-2 justify-content-center">

            <div class="col-6 col-sm-2 my-2 my-sm-0">
                <div>粉丝数</div>
                <div class="fw-bold large"><?php echo get_user_fans_count($user->ID); ?></div>
            </div>

            <div class="col-6 col-sm-2 my-2 my-sm-0">
                <div>投稿数</div>
                <div class="fw-bold large"><?php echo get_user_post_count($user->ID); ?></div>
            </div>

            <div class="col-6 col-sm-2 my-2 my-sm-0">
                <div>查看数</div>
                <div class="fw-bold large"><?php echo get_user_post_total_views($user->ID); ?></div>
            </div>

            <div class="col-6 col-sm-2 my-2 my-sm-0">
                <div>收到评论数</div>
                <div class="fw-bold large"><?php echo get_user_post_total_comments($user->ID); ?></div>
            </div>

            <div class="col-6 col-sm-2 my-2 my-sm-0">
                <div>收到点赞数</div>
                <div class="fw-bold large"><?php echo get_user_post_total_likes($user->ID); ?></div>
            </div>



        </div>

        

    </header>

    <div class="my-4">

        <div class="input-group">
            <input class="form-control" name="search-post" placeholder="投稿搜索..." value="<?php echo (isset($_GET['s ']) ? $_GET['s '] : '') ?>" autocomplete="new-password" />

            <button class="search-post-button btn btn-miku" type="submit">搜索</button>

        </div>


        
    </div>


    <div class="page-content post-list my-4">


    </div>

    <div class="my-2">
        <?php echo next_page_button('下一页'); ?>
        <input type="hidden" name="paged" value="0">
        <input type="hidden" name="author" value="<?php echo $user->ID; ?>">
    </div>


</div>

<?php get_footer(); ?>