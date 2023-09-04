<?php

//如果不是管理员

use mikuclub\Post_Meta;

use function mikuclub\breadcrumbs_component;
use function mikuclub\convert_link_to_https;
use function mikuclub\get_down_link_from_old_post;
use function mikuclub\get_fail_down_post_list;
use function mikuclub\get_post_fail_times;
use function mikuclub\print_page_edit_link;
use function mikuclub\redirect_for_not_admin;

redirect_for_not_admin();

/*
	template name: 下载失效列表
	description:  管理下载失效文章
*/
get_header();


$post_list = get_fail_down_post_list();

$post_list_html = '';

foreach ($post_list as $my_post)
{

    //获取下载地址
    $down1 = get_post_meta($my_post->id, Post_Meta::POST_DOWN, true);
    $down2 = get_post_meta($my_post->id, Post_Meta::POST_DOWN2, true);
    //获取密码
    $password  = get_post_meta($my_post->id, Post_Meta::POST_PASSWORD, true);
    $password2 = get_post_meta($my_post->id, Post_Meta::POST_PASSWORD2, true);

    //如果链接不存在, 尝试从文章内容中解析
    if (empty($down1) && empty($down2))
    {

        $result = get_down_link_from_old_post($my_post->id);
        if (isset($result[Post_Meta::POST_DOWN]))
        {
            $down1 = $result[Post_Meta::POST_DOWN];
        }
        if (empty($down2) && isset($result[Post_Meta::POST_DOWN2]))
        {
            $down2 = $result[Post_Meta::POST_DOWN2];
        }
        if (empty($password) && isset($result[Post_Meta::POST_PASSWORD]))
        {
            $password = $result[Post_Meta::POST_PASSWORD];
        }
        if (empty($password2) && isset($result[Post_Meta::POST_PASSWORD2]))
        {
            $password2 = $result[Post_Meta::POST_PASSWORD2];
        }
    }

    //修正链接头部
    $down1          = convert_link_to_https($down1);
    $down2          = convert_link_to_https($down2);
    $post_fail_time = get_post_fail_times($my_post->id);
    $post_edit_link = get_edit_post_link($my_post->id);
    $baidu_fast_link = get_post_meta($my_post->id, 'baidu_fast_link', true);

    $down1_html = '';
    if ($down1)
    {
        $down1_html = '<a class="down" href="' . $down1 . '" target="_blank">
                       ' . $down1 . '
                    </a>';
    }
    $down2_html = '';
    if ($down2)
    {
        $down2_html = '<hr/><a class="down" href="' . $down2 . '" target="_blank">
                       ' . $down2 . '
                    </a>';
    }

    $baidu_fast_link_html = '';
    if ($baidu_fast_link)
    {
        $baidu_fast_link_html = '<hr/><div>快传链接</div><div class="text-break">' . $baidu_fast_link . '</div>';
    }


    $post_list_html .= <<< HTML

        <div class="row my-2 list-item" data-post-id="{$my_post->id}">
            <div class="col-3">
                 <a class="" title="{$my_post->post_title}" href="{$my_post->post_href}" target="_blank">
                    <img class="img-fluid" src="{$my_post->post_image}" alt="{$my_post->post_title}"/>
                </a>
            </div>
            <div class="col-2 ">
                <div >
                    <a class="" title="{$my_post->post_title}" href="{$my_post->post_href}" target="_blank">
                        {$my_post->post_title}
                    </a>
                </div>
                <div class="mt-2">
                    <a class="card-link small" title="查看UP主空间" href="{$my_post->post_author->user_href}" target="_blank">
                        作者:  {$my_post->post_author->display_name}
                    </a>
                </div>
            </div>
            <div class="col-4">
                  
            
     
                <div class="mt-2">
                       {$down1_html}
                </div>

                <div class="passowrd mt-2">
                    {$password}
                </div>
                
                <div class="mt-2">
                        {$down2_html}
                </div>
                <div class="passowrd2 mt-2">
                    {$password2}
                </div>
                
                <div class="mt-2">
                        {$baidu_fast_link_html}
                </div>
            </div>
          <div class="col-1 d-flex align-items-center">
            <h4 class="text-danger mb-2 fail-time">{$post_fail_time}</h4>
          </div>
             <div class="col-2">
                <button class="btn btn-secondary m-2 reset-fail-times">清零</button>
                <button  class="btn btn-primary m-2 disable-fail-times">关闭失效</button>
                <button  class="btn btn-warning m-2 reject-post">一键退稿</button>
                <a class="btn btn-info m-2 edit-post" href="{$post_edit_link}" target="_blank">编辑</a>
                <button class="btn btn-danger m-2 delete-post">删除</button>
              </div>
        </div>


HTML;
}


?>

<div class="content page-fail-down-list">

    <header class="page-header">
        <h4 class="my-4">
            <?php echo breadcrumbs_component(); ?>
        </h4>


        <div class="text-end">
            <?php echo print_page_edit_link(); ?>
        </div>
    </header>
    <hr />
    <div class="page-content">

        <div class="my-4">
            <form>
                <div class="input-group">
                    <input class="form-control" name="author_id" placeholder="作者ID" value="<?php echo (isset($_GET['author_id']) ? $_GET['author_id'] : '') ?>" />

                    <button class="btn btn-miku" type="submit">搜索作者</button>

                </div>
            </form>
        </div>

        <div class="">

            <?php echo $post_list_html; ?>

        </div>


    </div>
</div>

<?php


get_footer(); ?>