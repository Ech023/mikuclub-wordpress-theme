<?php
/*
* template name: 签到页面
*/

namespace mikuclub;

use mikuclub\constant\Web_Domain;

use function mikuclub\print_breadcrumbs_component;
use function mikuclub\print_page_edit_link;

get_header();

while (have_posts())
{
    the_post();

    $breadcrumbs = print_breadcrumbs_component();
    $page_edit_link = print_page_edit_link();
    $content = get_the_content();

    $max         = 1256;
    $rand_image = rand(1, $max);
    //在左方添加0
    $rand_image = str_pad(strval($rand_image), 3, '0', STR_PAD_LEFT);

    $link = 'https://' . Web_Domain::CDN_MIKUCLUB_FUN . '/project_sekai_cg/' . $rand_image . '.jpg';



    $output = <<<HTML

        <div class="page-qiandao">

            <div class="page-header row">

                <div class="col">
                    {$breadcrumbs}
                </div>

                <div class="col-auto ms-auto">
                    {$page_edit_link}
                </div>

            </div>
            <div class="page-content my-2">

                <div class="qiandao-img text-center my-4 row">
                    <div class="col-12">
                        <a href="{$link}" data-lightbox="qiandao-images">
                            <img class="img-fluid" src="{$link}" alt="签到壁纸">
                        </a>
                    </div>
                </div>

                <hr class="my-4" />

                <div class="qiandao-button-container my-4 text-center">
                </div>

                <div class="my-4">
                    {$content}
                </div>

                <hr />

            </div>

        </div>

HTML;

    echo $output;

    comments_template('', true);
}


get_footer();
