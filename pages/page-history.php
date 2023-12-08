<?php
/*
	template name: 历史页面
*/

//如果未登陆 重定向回首页

use mikuclub\User_Capability;

use function mikuclub\print_breadcrumbs_component;




User_Capability::prevent_not_logged_user();

get_header();

?>

<div class="page-history">

    <div class="page-header">

        <div class="row my-4">
            <div class="col">
                
                    <?php echo print_breadcrumbs_component(); ?>
               

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
                        <i class="fa-solid fa-search"></i>
                    </button>

                </div>

          

            </div>

        </div>


        <div class="my-2">
            注: 如果投稿被UP退回或删除, 将会从历史里消失
        </div>







    </div>


    <div class="page-content post-list my-3">

    </div>

  

</div>

<?php get_footer(); ?>