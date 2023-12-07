<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;

/**
 * 输出底部JS代码
 *
 * @return string
 */
function print_footer_js_script_component()
{

    $post_id = is_single() ? get_the_ID() : 0;

    $output = '';

    //流量统计代码.
    if (get_theme_option(Admin_Meta::SITE_BOTTOM_TRACK_CODE_ENABLE))
    {
        $output .= '<!-- 底部流量统计代码 -->';
        $output .= get_theme_option(Admin_Meta::SITE_BOTTOM_TRACK_CODE);
    }

    //底部公共代码
    if (get_theme_option(Admin_Meta::SITE_BOTTOM_CODE_ENABLE))
    {
        $output .= '<!-- 底部公共代码 -->';
        $output .= get_theme_option(Admin_Meta::SITE_BOTTOM_CODE);
    }

    //获取最新发布文章的数量
    $new_post_count = get_new_post_count(3);

    $output .= <<<HTML

        <script>
            $(function () {

                //设置最新文章数量
                showNewPostCountInTopMenu({$new_post_count});
                //记录浏览记录 如果ID不存在则什么都不会发生
                setHistoryPostArray({$post_id});
                //增加文章点击数 如果ID不存在则什么都不会发生
                addPostViews({$post_id});

            });
        </script>

HTML;

    return $output;
}

/**
 * 输出底部统计数据
 * @return string
 */
function print_footer_statistics_component()
{
    $output = '';

    if (User_Capability::is_admin())
    {

        $post_count = get_site_post_count();
        $comment_count = get_site_comment_count();
        $category_count =  get_site_category_count();
        $tag_count = get_site_tag_count();
        $timer_stop =  timer_stop(0);
        $num_queries =  get_num_queries();

        $output = <<<HTML

            <div class="admin-info my-2">

                <ul class="list-group list-group-horizontal-lg">

                    <li class="list-group-item flex-fill">
                        站点统计:
                    </li>
                    <li class="list-group-item">
                        {$post_count} 篇投稿
                    </li>
                    <li class="list-group-item">
                        {$comment_count} 条评论
                    </li>
                    <li class="list-group-item">
                        {$category_count} 个分类
                    </li>
                    <li class="list-group-item">
                        {$tag_count} 个标签
                    </li>
                    <li class="list-group-item">
                        {$timer_stop} 响应时间
                    </li>
                    <li class="list-group-item">
                        {$num_queries} 查询次数
                    </li>

                </ul>

            </div>

HTML;
    }

    return $output;
}
