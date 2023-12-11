<?php
/*
  template name: 投稿页面
  description: 新建投稿和编辑投稿页面
 */

namespace mikuclub;

use mikuclub\constant\Config;
use mikuclub\User_Capability;

use function mikuclub\print_breadcrumbs_component;
use function mikuclub\is_sticky_post;


//如果未登陆 重定向回首页
User_Capability::prevent_not_logged_user();

$home = get_home_url();

get_header();



while (have_posts())
{
    the_post();

    $post_id = $_GET['pid'] ?? null;
    $breadcrumbs = print_breadcrumbs_component();
    $post_meta_component = print_tougao_post_meta_component($post_id);
    $post_manage_buttons = print_tougao_post_manage_buttons_component($post_id);

    $float_submit_button_text = $post_id ? '更新' : '提交';
    $float_submit_button = <<<HTML

        <div class="fixed-submit-button-div position-fixed bottom-0 start-0 end-0 bg-light-1 text-center p-2 border-top mb-5" style="z-index: 99">
            <div class="row justify-content-center g-2">
                {$post_manage_buttons}
                <div class="col-12 col-md-3">
                    <button class="btn btn-miku w-100 px-5 submit_post fs-875 fs-md-100">
                        {$float_submit_button_text}
                    </button>
                </div>
            </div>
        </div>
HTML;


    $output = <<<HTML

        <div class="page-tougao">

            <div class="page-header row align-items-center">

                <div class="col-auto">
                    {$breadcrumbs}
                </div>
                <div class="col-auto">
                    {$post_meta_component}
                </div>
                <div class="col-auto ms-auto my-2 my-xxl-0 d-none d-xl-block">
                    <div class="row gx-2">
                        {$post_manage_buttons}
                    </div>
                </div>

            </div>
            <div class="page-content my-2">
            
            
HTML;

    echo $output;

    the_content();

    echo <<<HTML
			</div>
            {$float_submit_button}
		</div>
HTML;
}

get_footer();


