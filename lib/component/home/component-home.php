<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Category;
use mikuclub\constant\Config;
use mikuclub\constant\Constant;
use mikuclub\constant\Expired;
use mikuclub\constant\Post_Meta;

/**
 * 首页组件
 * @return mixed|string
 */
function print_home_component()
{


    $output = print_home_component_first_part();

    //关注用户发布的文章列表
    $followed_post_list_title = '我关注的用户投稿';
    $followed_post_list_link  = get_home_url() . "/followed";
    $followed_post_list = get_my_followed_post_list(Config::HOME_MY_FOLLOWED_POST_LIST_LENGTH);

    $output .= print_home_post_list_component($followed_post_list, $followed_post_list_title, 'fa-solid fa-user-plus', $followed_post_list_link);


    $output .= print_home_component_second_part();

    return $output;
}

/**
 * 输出主页的第一部分 (幻灯片热门文章+论坛最新帖子列表)
 *
 * @return string
 */
function print_home_component_first_part()
{



    //获取第一部分的缓存
    $output = File_Cache::get_cache_meta(File_Cache::HOME_PART_1, File_Cache::DIR_COMPONENTS, Expired::EXP_15_MINUTE);
    if (empty($output))
    {
        $output = '';

        //幻灯片组件
        $output = print_sticky_post_slide_component(Category::NO_ADULT_CATEGORY);

        //首页 幻灯片下方横幅
        if (get_theme_option(Admin_Meta::HOME_SLIDE_BOTTOM_ADSENSE_PC_ENABLE))
        {
            $output .= '<div class="pop-banner d-none d-md-block text-center my-2 pb-2 border-bottom">' . get_theme_option(Admin_Meta::HOME_SLIDE_BOTTOM_ADSENSE_PC) . '</div>';
        }
        if (get_theme_option(Admin_Meta::HOME_SLIDE_BOTTOM_ADSENSE_PHONE_ENABLE))
        {
            $output .= '<div class="pop-banner d-block d-md-none text-center my-2 pb-2 border-bottom">' . get_theme_option(Admin_Meta::HOME_SLIDE_BOTTOM_ADSENSE_PHONE) . '</div>';
        }

        //最新主题帖子
        $output .= print_bbs_topic_list_component();

        // 首页 - 最新发布上方横幅 广告
        if (get_theme_option(Admin_Meta::HOME_RECENTLY_LIST_TOP_ADSENSE_PC_ENABLE))
        {
            $output .= '<div class="pop-banner d-none d-md-block text-center my-2 pb-2 border-bottom">' . get_theme_option(Admin_Meta::HOME_RECENTLY_LIST_TOP_ADSENSE_PC) . '</div>';
        }
        if (get_theme_option(Admin_Meta::HOME_RECENTLY_LIST_TOP_ADSENSE_PHONE_ENABLE))
        {
            $output .= '<div class="pop-banner d-block d-md-none text-center my-2 pb-2 border-bottom">' . get_theme_option(Admin_Meta::HOME_RECENTLY_LIST_TOP_ADSENSE_PHONE) . '</div>';
        }

        $output = $output;

        //修正缓存里的地址
        $output_to_save = fix_site_domain_with_domain_main($output);
        //创建缓存
        File_Cache::set_cache_meta(File_Cache::HOME_PART_1, File_Cache::DIR_COMPONENTS, $output_to_save);
    }

    return $output;
}

/**
 * 输出主页的第二部分 (全站热门文章+单独分类的热门)
 *
 * @return string
 */
function print_home_component_second_part()
{
    global $wp_query;


    //获取第二部分的缓存
    $output = File_Cache::get_cache_meta(File_Cache::HOME_PART_2, File_Cache::DIR_COMPONENTS, Expired::EXP_30_MINUTE);
    if (empty($output))
    {

        //获取全站点击文章列表
        $hot_post_list = get_hot_post_list(Category::NO_ADULT_CATEGORY, Post_Meta::POST_VIEWS, Config::HOME_POST_LIST_LENGTH);
        $output .= print_home_post_list_component($hot_post_list, '全站点击',  'fa-solid fa-newspaper');


        //分区id数组
        $array_cat = [
            [
                'id' => Category::DIVA, //歌姬PV
                'icon' => 'fa-solid fa-headphones'
            ],
            [
                'id' => Category::MMD, // MMD区
                'icon' => 'fa-solid fa-play-circle'
            ],
            [
                'id' => Category::MUSIC, //音乐区
                'icon' => 'fa-solid fa-music'
            ],
            [
                'id' => Category::IMAGE, //图片区
                'icon' => 'fa-solid fa-image'
            ],
            [
                'id' =>  Category::LIVE, //演唱会
                'icon' => 'fa-solid fa-drum'
            ],

            [
                'id' =>  Category::GAME, //游戏区
                'icon' => 'fa-solid fa-gamepad'
            ],
            [
                'id' =>  Category::ANIME, //动漫区
                'icon' => "fa-solid fa-dragon"
            ],
            [
                'id' => Category::DANCE, //舞蹈区
                'icon' => 'fa-solid fa-record-vinyl'
            ],
            [
                'id' =>  Category::VIDEO, //视频番剧区
                'icon' => 'fa-solid fa-film'
            ],

            [
                'id' =>  Category::SOFTWARE, //软件区
                'icon' => 'fa-solid fa-file-alt'
            ],

            [
                'id' =>  Category::FICTION, //小说区
                'icon' => 'fa-solid fa-book'
            ],

            [
                'id' =>  Category::TUTORIAL, //教程区
                'icon' => 'fa-solid fa-graduation-cap'
            ],

        ];

        foreach ($array_cat as $cat)
        {

            $cat_id = $cat['id'];
            $cat_icon = $cat['icon'];
            $cat_name = get_the_category_by_ID($cat_id);
            $cat_link = get_category_link($cat_id);
            $post_list = get_hot_post_list($cat_id, Post_Meta::POST_VIEWS, Config::HOME_POST_LIST_LENGTH);

            $output .= print_home_post_list_component($post_list, $cat_name, $cat_icon, $cat_link);
        }

        $recently_post_list_link = get_home_url() . "/page/1";

        //页底 查看更多
        $output .= <<<HTML

        <div class="my-2 text-center">
            <a class="btn btn-outline-secondary btn-lg w-50" title="最新发布" href="{$recently_post_list_link}">
                查看更多 <i class="fa-solid fa-angle-right"></i>
            </a>
        </div>

HTML;

        //修正缓存里的地址
        $output_to_save = fix_site_domain_with_domain_main($output);
        //创建缓存
        File_Cache::set_cache_meta(File_Cache::HOME_PART_2, File_Cache::DIR_COMPONENTS, $output_to_save);
    }

    return $output;
}
