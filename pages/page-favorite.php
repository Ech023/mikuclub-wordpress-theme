<?php
/*
	template name: 收藏页面
*/

//如果未登陆 重定向回首页

use mikuclub\User_Capability;

use function mikuclub\print_breadcrumbs_component;



User_Capability::prevent_not_logged_user();

get_header();

?>

<div class="page-favorite">

    <div class="page-header">

       
            <?php echo print_breadcrumbs_component(); ?>
        
        <div class="my-4">
            
            <div class="inside-search ">

                <div class="input-group mb-4">

                    <input type="text" class="search form-control form-control-lg" placeholder="在收藏夹内搜索" autocomplete="off" />

                    <button class="btn btn-miku px-4">
                        <i class="fa-solid fa-search"></i>
                    </button>

                </div>

              

            </div>

        </div>

        <div class="my-2">
            注: 如果投稿被UP退回或删除, 将会从收藏夹里消失
        </div>




    </div >



    <div class="page-content post-list my-3">



    </div>

  


</div>

<?php get_footer(); ?>