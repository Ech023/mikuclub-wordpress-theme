<?php

//如果不是管理员
namespace mikuclub;

use mikuclub\constant\Download_Link_Type;
use mikuclub\constant\Post_Meta;
use mikuclub\Input_Validator;
use mikuclub\User_Capability;

use function mikuclub\print_breadcrumbs_component;
use function mikuclub\convert_link_to_https;
use function mikuclub\get_down_link_from_old_post;
use function mikuclub\get_fail_down_post_list;
use function mikuclub\get_post_fail_times;
use function mikuclub\print_page_edit_link;


User_Capability::prevent_not_admin_user();

/*
	template name: 下载失效列表
	description:  管理下载失效文章
*/
get_header();


$author = Input_Validator::get_request_value('author_id', Input_Validator::TYPE_INT);
$cat = Input_Validator::get_request_value('category', Input_Validator::TYPE_INT);
$paged = Input_Validator::get_request_value('offset', Input_Validator::TYPE_INT);

$post_list = get_fail_down_post_list($author, $cat, $paged);

$post_list_html = '';

foreach ($post_list as $my_post)
{

    //获取下载地址
    $down = get_post_meta($my_post->id, Post_Meta::POST_DOWN, true);
    $down2 = get_post_meta($my_post->id, Post_Meta::POST_DOWN2, true);
    $down3 = get_post_meta($my_post->id, Post_Meta::POST_DOWN3, true);
    //获取访问密码
    $access_password  = get_post_meta($my_post->id, Post_Meta::POST_PASSWORD, true);
    $access_password2 = get_post_meta($my_post->id, Post_Meta::POST_PASSWORD2, true);
    $access_password3 = get_post_meta($my_post->id, Post_Meta::POST_PASSWORD3, true);

    //获取解压密码
    $unzip_password  = get_post_meta($my_post->id, Post_Meta::POST_UNZIP_PASSWORD, true);
    $unzip_password2 = get_post_meta($my_post->id, Post_Meta::POST_UNZIP_PASSWORD2, true);
    $unzip_password3 = get_post_meta($my_post->id, Post_Meta::POST_UNZIP_PASSWORD3, true);

    //获取解压密码2
    $unzip_sub_password  = get_post_meta($my_post->id, Post_Meta::POST_UNZIP_SUB_PASSWORD, true);
    $unzip_sub_password2 = get_post_meta($my_post->id, Post_Meta::POST_UNZIP_SUB_PASSWORD2, true);
    $unzip_sub_password3 = get_post_meta($my_post->id, Post_Meta::POST_UNZIP_SUB_PASSWORD3, true);

    //如果链接不存在, 尝试从文章内容中解析
    if (empty($down) && empty($down2))
    {

        $result = get_down_link_from_old_post($my_post->id);
        if (isset($result[Post_Meta::POST_DOWN]))
        {
            $down = $result[Post_Meta::POST_DOWN];
        }
        if (empty($down2) && isset($result[Post_Meta::POST_DOWN2]))
        {
            $down2 = $result[Post_Meta::POST_DOWN2];
        }
        if (empty($access_password) && isset($result[Post_Meta::POST_PASSWORD]))
        {
            $access_password = $result[Post_Meta::POST_PASSWORD];
        }
        if (empty($access_password2) && isset($result[Post_Meta::POST_PASSWORD2]))
        {
            $access_password2 = $result[Post_Meta::POST_PASSWORD2];
        }
    }

    $array_post_down = [
        [
            'meta_key' => Post_Meta::POST_DOWN,
            'down' => $down,
            'access_password' => $access_password,
            'unzip_password' => $unzip_password,
            'unzip_sub_password' => $unzip_sub_password,
        ],
        [
            'meta_key' => Post_Meta::POST_DOWN2,
            'down' => $down2,
            'access_password' => $access_password2,
            'unzip_password' => $unzip_password2,
            'unzip_sub_password' => $unzip_sub_password2,
        ],
        [
            'meta_key' => Post_Meta::POST_DOWN3,
            'down' => $down3,
            'access_password' => $access_password3,
            'unzip_password' => $unzip_password3,
            'unzip_sub_password' => $unzip_sub_password3,
        ],
    ];

    $down_html = '';
    foreach ($array_post_down as $post_down)
    {
        $meta_key = $post_down['meta_key'];
        $down = $post_down['down'];
        $access_password =  $post_down['access_password'];
        $unzip_password = $post_down['unzip_password'];
        $unzip_sub_password = $post_down['unzip_sub_password'];
        

        //修正链接
        if ($down)
        {
            $down_type = Download_Link_Type::get_type_by_link($down);

            $down = convert_link_to_https($down);
            //如果是支持的格式, 把访问密码增加到下载地址里
            $down = add_access_password_to_down_link($down, $access_password, $down_type);

            $down_html .= <<<HTML
                <div class="down-container row g-2 align-items-center">
                    <div class="badge-container col-auto">
                    <span class="badge bg-secondary">未检测</span>
                    </div>
                    <div class="col-8">
                        <a class="down text-break small" href="{$down}" target="_blank">
                        {$down}
                        </a>
                    </div>
                    <div class="col-2">
                        <button class="delete_down_link btn btn-sm btn-light-2" data-post_id="{$my_post->id}" data-meta_key="{$meta_key}">删除地址</button>
                    </div>
                </div>
               
HTML;

            if ($access_password)
            {
                $down_html .= <<<HTML
                <div class="small passowrd my-2">
                    提取码 <span class="fw-bold">{$access_password}</span>
                </div>
HTML;
            }
            if ($unzip_password)
            {
                $down_html .= <<<HTML
                <div class="small unzip_password my-2">
                    解压码 <span class="fw-bold">{$unzip_password}</span>
                </div>
HTML;
            }
            if ($unzip_sub_password)
            {
                $down_html .= <<<HTML
                <div class="small unzip_sub_password my-2">
                    解压码2 <span class="fw-bold">{$unzip_sub_password}</span>
                </div>
HTML;
            }

            $down_html .= '<hr/>';
        }
    }



    $post_fail_time = get_post_fail_times($my_post->id);
    $post_edit_link = get_edit_post_link($my_post->id);
    $baidu_fast_link = get_post_meta($my_post->id, 'baidu_fast_link', true);


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
                    <a class="small" title="{$my_post->post_title}" href="{$my_post->post_href}" target="_blank">
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
                  
                {$down_html}
                
                <div class="mt-2">
                        {$baidu_fast_link_html}
                </div>
            </div>
          <div class="col-1">
            <h5 class="text-danger fail-time">{$post_fail_time}</h5>
          </div>
             <div class="col-2">
                <button class="btn btn-secondary m-2 reset-fail-times">清零</button>
                <button  class="btn btn-primary m-2 disable-fail-times">关闭失效</button>
                <button  class="btn btn-warning m-2 reject_post" data-post-id="{$my_post->id}">一键退稿</button>
                <a class="btn btn-info m-2 edit-post" href="{$post_edit_link}" target="_blank">编辑</a>
                <button class="btn btn-danger m-2 delete_post" data-post-id="{$my_post->id}">删除</button>
              </div>
        </div>


HTML;
}


?>

<div class="page-fail-down-list">

    <div class="page-header">

        <?php echo print_breadcrumbs_component(); ?>



        <div class="text-end">
            <?php echo print_page_edit_link(); ?>
        </div>
    </div>
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