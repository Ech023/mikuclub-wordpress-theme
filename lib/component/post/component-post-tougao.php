<?php

namespace mikuclub;

use mikuclub\constant\Config;
use mikuclub\constant\Post_Status;

/*
投稿页面相关的组件
*/

/**
 * 显示投稿编辑的元数据
 * @param int|null $post_id
 * @return string
 */
function print_tougao_post_meta_component($post_id)
{

    $output = '';

    if ($post_id)
    {

        $author_id = intval(get_post_field('post_author', $post_id));
        $author_href = get_author_posts_url($author_id);
        $author_name = get_the_author_meta('display_name', $author_id);

        //获取文章状态
        $post_status      = get_post_status($post_id);
        $post_status_text = Post_Status::get_description($post_status);
        $text_color_class = Post_Status::get_text_color_class($post_status);

        $post_date = get_the_date(Config::DATE_FORMAT, $post_id);
        $post_modified_date = get_the_modified_date(Config::DATE_FORMAT, $post_id);

        //只对管理员可见
        $author_meta_class = User_Capability::is_admin() ? '' : 'd-none';

        $output = <<<HTML

            <div class="row small">

                <div class="col-auto">
                    稿件状态: <span class="{$text_color_class}">{$post_status_text}</span>
                </div>
                <div class="col-auto">
                    创建时间: <span class="text-primary">{$post_date}</span>
                </div>
                <div class="col-auto">
                    最后修改: <span class="text-info">{$post_modified_date}</span>
                </div>
                <div class="col-auto {$author_meta_class}">
                    作者: <a href="{$author_href}" target="_blank">{$author_name}</a>
                </div>

            </div>
HTML;
    }

    return $output;
}


/**
 * 显示投稿编辑的管理按钮
 * @param int|null $post_id
 * @return string
 */
function print_tougao_post_manage_buttons_component($post_id)
{
    $output = '';



    if ($post_id)
    {
        $home = get_home_url();
        $post_href = get_permalink($post_id);
        $manage_page_href = $home . '/up_home_page';

        $post_status = get_post_status($post_id);
        $draft_button_class = $post_status === Post_Status::DRAFT ? 'd-none' : '';
        //管理员专用的按钮
        $admin_only_button_class = User_Capability::is_admin() ? '' : 'd-none';

        //置顶按钮
        if (!is_sticky_post($post_id))
        {
            $sticky_post_button_class = 'set-sticky-post';
            $sticky_post_button_text  = '置顶投稿';
        }
        //文章已被置顶
        else
        {
            $sticky_post_button_class = 'delete-sticky-post';
            $sticky_post_button_text  = '取消置顶';
        }

        $output = <<<HTML

          

                    <!--  查看投稿链接 -->
                    <div class="col-auto">
                        <a class="btn btn-light-2 fs-875 fs-md-100" href="{$post_href}" target='_blank'>
                            查看投稿
                        </a>
                    </div>
                    <!--  投稿管理链接 -->
                    <div class="col-auto d-none d-sm-block">
                        <a class="btn btn-light-2 fs-875 fs-md-100" href="{$manage_page_href}" target="_blank">
                            稿件管理列表
                        </a>
                    </div>
                    <!--  转为草稿 -->
                    <div class="col-auto {$draft_button_class}">
                        <button class="btn  btn-light-2 fs-875 fs-md-100 draft_post" data-post-id="{$post_id}">
                            转为草稿
                        </button>
                    </div>
                    <!--  删除投稿链接 -->
                    <div class="col-auto">
                        <button class="btn  btn-danger fs-875 fs-md-100 delete_post" data-post-id="{$post_id}">
                            删除
                        </button>
                    </div>
                    <!-- 弃用-->
                    <div class="col-auto d-none">
                        <button class="btn  btn-light-2 fs-875 fs-md-100 update-post-date" data-post-id="{$post_id}">
                            更新创建时间
                        </button>
                    </div>
                    <div class="col-auto {$admin_only_button_class}">
                        <button class="btn btn-primary fs-875 fs-md-100 {$sticky_post_button_class}" data-post-id="{$post_id}">
                            {$sticky_post_button_text}
                        </button>
                    </div>
                    <div class="col-auto {$admin_only_button_class}">
                        <button class="btn btn-warning fs-875 fs-md-100 reject_post" data-post-id="{$post_id}">
                            驳回投稿
                        </button>
                    </div>

           
HTML;
    }

    return $output;
}
