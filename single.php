<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Web_Domain;

use function mikuclub\print_adult_404_content_for_no_logging_user;
use function mikuclub\get_theme_option;
use function mikuclub\is_adult_category;
use function mikuclub\print_post_content;

get_header();

$output = '';

//如果未登录 访问成人成人文章 输出404内容
if (!is_user_logged_in() && is_adult_category())
{
    $output = print_adult_404_content_for_no_logging_user();
}
else
{

    //确定当前 WordPress 查询是否有可循环的文章。
    while (have_posts())
    {

        //获取当前文章信息
        the_post();

        $post_id = get_the_ID();

        $output .= print_post_head($post_id);


        //电脑端 文章页 - 页面标题下
        if (get_theme_option(Admin_Meta::POST_TITLE_BOTTOM_ADSENSE_PC_ENABLE) && !Web_Domain::is_mikuclub_uk())
        {
            $output .= '<div class="pop-banner d-none d-md-block text-center pb-2 border-bottom">' . get_theme_option(Admin_Meta::POST_TITLE_BOTTOM_ADSENSE_PC) . '</div>';
        }

        //手机端 文章页 - 页面标题下
        if (get_theme_option(Admin_Meta::POST_TITLE_BOTTOM_ADSENSE_PHONE_ENABLE) && !Web_Domain::is_mikuclub_uk())
        {
            $output .= '<div class="pop-banner d-md-none text-center pb-2 border-bottom">' . get_theme_option(Admin_Meta::POST_TITLE_BOTTOM_ADSENSE_PHONE) . '</div>';
        }

        // 文章内容
        $post_content = print_post_content($post_id);
        $output .= <<<HTML
        
            <div class="article-content">
                {$post_content}
            </div>
HTML;

        // 相关推荐
        $related_posts_component = print_related_post_list_component();

        $output .= <<<HTML
            <div class="article-footer">
                {$related_posts_component}
            </div>
HTML;

        //广告：文章页 - 评论区上方
        if (get_theme_option(Admin_Meta::POST_COMMENT_ADSENSE_PC_ENABLE))
        {
            $output .= '<div class="pop-banner text-center my-2 d-none d-md-block">' . get_theme_option(Admin_Meta::POST_COMMENT_ADSENSE_PC) . '</div>';
        }

        //手机广告 - 评论区上方
        if (get_theme_option(Admin_Meta::POST_COMMENT_ADSENSE_PHONE_ENABLE))
        {
            $output .= '<div class="pop-banner text-center my-2 d-md-none">' . get_theme_option(Admin_Meta::POST_COMMENT_ADSENSE_PHONE) . '</div>';
        }
    }
}

echo $output;

comments_template('', true);

get_footer();
