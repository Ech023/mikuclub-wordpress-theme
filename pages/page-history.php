<?php
/*
	template name: 历史页面
*/

//如果未登陆 重定向回首页
redirect_for_not_logged();

get_header();

?>

<div class="content page-history">

    <header class="page-header">

        <div class="row my-4">
            <div class="col">
                <h4 class="">
                    <?php echo breadcrumbs_component(); ?>
                </h4>

            </div>
            <div class="col-auto">
                <div class="">
                    <button class="clear_history btn btn-secondary">清空历史</button>
                </div>

            </div>

        </div>

        
        <div class="my-4">
            
            <div class="inside-search ">

                <div class="input-group mb-4">

                    <input type="text" class="search form-control form-control-lg" placeholder="在历史内搜索" autocomplete="off" />

                    <button class="btn btn-miku px-4">
                        <i class="fas fa-search"></i>
                    </button>

                </div>

                <?php echo print_categoria_radio_box(); ?>

            </div>

        </div>


        <div class="my-2">
            注: 如果投稿被UP退回或删除, 将会从历史里消失
        </div>







    </header>


    <div class="page-content post-list my-3">

    </div>

    <div class="my-2">
        <?php echo next_page_button('下一页'); ?>
    </div>


</div>

<?php get_footer(); ?>