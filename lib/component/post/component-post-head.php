<?php

namespace mikuclub;

use mikuclub\constant\Config;
use mikuclub\constant\Expired;
use mikuclub\constant\Post_Feedback_Rank;
use mikuclub\constant\Post_Status;

/**
 * 输出文章页HTML头部
 *
 * @param int $post_id
 * @return string
 */
function print_post_head($post_id)
{

    $output = '';


    $author_id = intval(get_post_field('post_author', $post_id));

    $post_title = get_the_title($post_id);
    $breadcrumbs = print_breadcrumbs_component();

    $post_date = get_the_date(Config::DATE_FORMAT, $post_id);
    $post_modified_date = get_the_modified_date(Config::DATE_FORMAT, $post_id);
    $post_status = Post_Status::get_description(get_post_status($post_id));

    $post_like = get_post_like($post_id);
    $post_unlike = get_post_unlike($post_id);
    $post_rank = Post_Feedback_Rank::get_rank($post_like, $post_unlike);
    $post_views  = get_post_views($post_id);
    $post_comments_number = get_post_comments($post_id);
    $post_favorites  = get_post_favorites($post_id);
    $post_shares  = get_post_shares($post_id);


    $post_manage_buttons = print_post_head_manage_buttons($post_id, $author_id);

    $tag_component = print_post_head_tags($post_id);

    $post_head_author = print_post_head_author($author_id);

    $output = <<<HTML

    <div class="article-header">

        <div class="row my-2">

            <div class="col-12 col-lg-7">

                <!-- 文章标题 -->
                <h5 class="article-title my-2 fw-bold">
                    {$post_title}
                </h5>

                <!-- 文章信息 -->
                <div class="row align-items-center g-2 fs-75 fs-sm-875 ">

                    <!-- 网站面包屑-->
                    <div class="col-12 col-xl-auto">
                        {$breadcrumbs}
                    </div>

                    <!-- 文章发布时间 -->
                    <div class="col-auto">
                        <div class="post-date">
                            发布时间 {$post_date}
                        </div>
                    </div>
                    <!-- 文章修改时间 -->
                    <div class="col-auto">
                        <div class="post-modified-date ">
                            最后修改 {$post_modified_date}
                        </div>
                    </div>

                    <div class="col-auto">
                        <div class="post-status">
                            状态 {$post_status}
                        </div>
                    </div>

                </div>

                <div class="border-bottom py-2">

                    <div class="row align-items-center gx-3 gy-2 fs-75 fs-sm-875 ">

                            <!-- 评价等级 -->
                            <div class="col-auto">
                                <div class="post-feedback ">
                                    <i class="fa-solid fa-square-poll-vertical me-2"></i>
                                    <span>
                                        {$post_rank}
                                    </span>
                                </div>
                            </div>
                            <!-- 文章点赞 -->
                            <div class="col-auto">
                                <div class="post-likes">
                                    <i class="fa-solid fa-thumbs-up me-2" aria-hidden="true"></i>
                                    {$post_like} 好评
                                </div>
                            </div>
                            <!-- 文章差评 -->
                            <div class="col-auto">
                                <div class="post-likes">
                                    <i class="fa-solid fa-thumbs-down me-2"></i>
                                {$post_unlike} 差评
                                </div>
                            </div>
                            <!-- 文章点击量 -->
                            <div class="col-auto">
                                <div class="post-views">
                                    <i class="fa-solid fa-eye me-2"></i>
                                    {$post_views} 点击
                                </div>
                            </div>
                            <!-- 文章评论数量 -->
                            <div class="col-auto">
                                <div class="post-comments">
                                    <i class="fa-solid fa-comments me-2"></i>
                                    {$post_comments_number} 评论
                                </div>
                            </div>
                            <!-- 文章收藏 -->
                            <div class="col-auto">
                                <div class="post-favorite">
                                    <i class="fa-solid fa-heart me-2"></i>
                                    {$post_favorites} 收藏
                                </div>
                            </div>
                            <!-- 文章分享 -->
                            <div class="col-auto">
                                <div class="post-sharing me-3 my-1 my-md-0">
                                    <i class="fa-solid fa-share-square me-2"></i>
                                    {$post_shares} 分享
                                </div>
                            </div>
                        
                    </div>
                </div>
            
                <!-- 编辑按钮 -->
                {$post_manage_buttons}
                <!-- 标签 -->
                {$tag_component}
                

            </div>

            <div class="col-12 col-lg-5 mt-2 mt-lg-auto">
                {$post_head_author}
            </div>

        </div>

        

    </div>

HTML;


    return $output;
}




/**
 * 输出文章页HTML头部 快捷操作 按钮
 *
 * @param int $post_id
 * @param int $author_id
 * @return string
 */
function print_post_head_manage_buttons($post_id, $author_id)
{
    $output = '';

    $current_user_id = get_current_user_id();
    //如果是
    if ($current_user_id === $author_id || User_Capability::is_admin())
    {
        $edit_post_link = get_edit_post_link($post_id);

        $edit_post_button = <<<HTML
            <div class="col-auto">
                <a class="btn btn-sm fs-75 fs-sm-875 btn-light-2 px-4" href="{$edit_post_link}">
                    编辑
                </a>
            </div>
HTML;

        //如果文章已公布或者是待审
        $draft_post_button = '';
        if (in_array(get_post_status($post_id), [Post_Status::PUBLISH, Post_Status::PENDING]))
        {
            $draft_post_button = <<<HTML
            <div class="col-auto">
                <button class="draft_post btn btn-sm fs-75 fs-sm-875 btn-light-2 px-4" data-post-id="{$post_id}">
                    转为草稿
                </button>
            </div>
HTML;
        }

        $delete_post_button = <<<HTML
            <div class="col-auto">
                <button class="delete_post btn btn-sm fs-75 fs-sm-875 btn-light-2 px-4" data-post-id="{$post_id}">
                    删除
                </button>
            </div>
HTML;

        $reject_post_button = '';
        if (User_Capability::is_admin())
        {
            $reject_post_button = <<<HTML
            <div class="col-auto">
                <button class="reject_post btn btn-sm fs-75 fs-sm-875 btn-light-2 px-4 " data-post-id="{$post_id}">
                    退稿
                </button>
            </div>
HTML;
        }

        $output = <<<HTML
        <div class="border-bottom py-2">
            <div class="row g-2 align-items-center">
                <div class="col-auto d-none d-sm-block">
                    <i class="fa-solid fa-gear me-2"></i>
                </div>
                {$edit_post_button}
                {$draft_post_button}
                {$delete_post_button}
                {$reject_post_button}
            </div>
        </div>
HTML;
    }


    return $output;
}

/**
 * 输出文章页HTML头部作者信息
 *
 * @param int $author_id
 * @return string
 */
function print_post_head_author($author_id)
{
    $output = '';

    $author = get_custom_user($author_id);
    $author_avatar = print_user_avatar($author->user_image, 100);
    $user_badges = print_user_badges($author_id);
    $author_buttons_element = print_user_follow_and_message_button($author_id);

    $output = <<<HTML

        <div class="post-author-data bg-light-2 rounded p-2">
            <div class="row align-items-center">
                <div class="col-12 col-sm-auto text-center">
                    <a href="{$author->user_href}" title="查看UP主页面" >
                        {$author_avatar}
                    </a>
                </div>
                <div class="col">
                    <div class="d-sm-inline-block m-1 text-center text-sm-start">
                        <a href="{$author->user_href}" title="查看UP主页面" >
                            {$author->display_name}
                        </a>
                    </div>
                    <div class="d-sm-inline-block m-1 text-center text-sm-start">
                        {$user_badges}
                    </div>
                    <div class="fs-75 fs-sm-875 my-1 overflow-hidden text-dark-2 text-1-rows text-center text-sm-start">
                        {$author->user_description}
                    </div>
                    <div class="user-functions row gx-2 justify-content-center justify-content-sm-start">
                        {$author_buttons_element}
                    </div>
                </div>
            </div>
        </div>

HTML;

    return  $output;
}




/**
 * 输出文章页HTML头部标签信息
 *
 * @param int $post_id
 * @return string
 */
function print_post_head_tags($post_id)
{
    $output = '';

    //文章内容第一部分
    $output = File_Cache::get_cache_meta_with_callback(File_Cache::POST_TAGS, File_Cache::DIR_POST . DIRECTORY_SEPARATOR . $post_id, Expired::EXP_7_DAYS, function () use ($post_id)
    {
        $output = '';
        $array_tag = get_the_tags($post_id);

        //如果有标签
        if ($array_tag)
        {
            $tag_component = '';
            foreach ($array_tag as $tag)
            {
                $tag_link = get_tag_link($tag->term_id);
                $tag_component .= <<<HTML
                <div class="col-auto ">
                    <a class="btn btn-sm fs-75 fs-sm-875 btn-light-2" href="{$tag_link}" >{$tag->name}</a>
                </div>
HTML;
            }

            $output = <<<HTML
                <div>
                    <div class="tags row g-2 my-0 align-items-center ">
                        <div class="col-auto d-none d-sm-block">
                            <i class="fa-solid fa-tags me-2"></i>
                        </div>
                        {$tag_component}
                    </div>
                </div>
HTML;
        }
        return $output;
    });

    return  $output;
}
