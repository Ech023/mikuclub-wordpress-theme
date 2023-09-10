<?php
namespace mikuclub;
/**
 * 首页组件
 * @return mixed|string
 */
function home_main_page()
{

    global $wp_query;

    $cache_key = 'home';

    //关注用户列表长度
    $followed_post_list_length = 6;
    //默认列表长度
    $default_list_length = 15;
    //分类列表长度
    $cat_list_length = 15;

    //获取过期时间
    $expired = Expired::get_home_exp_time();
    //分区id数组
    $cat_ids_list = [
        [
            'id' => 7, //歌姬PV
            'icon' => 'fa-solid fa-headphones'
        ],
        [
            'id' => 3, // MMD区
            'icon' => 'fa-solid fa-play-circle'
        ],
        [
            'id' => 9, //音乐区
            'icon' => 'fa-solid fa-music'
        ],
        [
            'id' => 789, //图片区
            'icon' => 'fa-solid fa-image'
        ],
        [
            'id' => 8, //演唱会
            'icon' => 'fa-solid fa-drum'
        ],

        [
            'id' => 182, //游戏区
            'icon' => 'fa-solid fa-gamepad'
        ],
        [
            'id' => 942, //动漫区
            'icon' => "fa-solid fa-dragon"
        ],
        [
            'id' => 9305, //视频区
            'icon' => 'fa-solid fa-film'
        ],

        [
            'id' => 465, //软件区
            'icon' => 'fa-solid fa-file-alt'
        ],

        [
            'id' => 294, //小说区
            'icon' => 'fa-solid fa-book'
        ],

        [
            'id' => 8621, //学习区
            'icon' => 'fa-solid fa-graduation-cap'
        ],

        [
            'id' => 4, //公告区
            'icon' => 'fa-solid fa-scroll'
        ],

        [
            'id' => 1, //其他区
            'icon' => 'fa-solid fa-boxes'
        ],



    ];

    //获取缓存
    $home_content_output = File_Cache::get_cache_meta($cache_key, File_Cache::DIR_COMPONENTS, $expired);

    //如果缓存失效 则重新计算
    if (empty($home_content_output))
    {

        //幻灯片组件
        $home_sticky = sticky_posts_component();
        //热门文章列表
        $home_hot_posts = top_hot_posts_component();

        //输出顶部幻灯片+顶部热门
        $home_content_output = <<<HTML
            <div class="row my-4">
                <div class="col-12 col-lg-6 col-xl-5">
                    {$home_sticky}
                </div>
                <div class="col-12 col-lg-6 col-xl-7 my-3 my-lg-0">
                    {$home_hot_posts}
                </div>
            </div>

HTML;


        //首页 幻灯片下方横幅
        if (dopt('d_adindex_01_b'))
        {
            $home_content_output .= '<div class="pop-banner d-none d-md-block text-center my-4 py-2">' . dopt('d_adindex_01') . '</div>';
        }
        if (dopt('Mobiled_adindex_02_b'))
        {
            $home_content_output .= '<div class="pop-banner d-block d-md-none text-center my-3">' . dopt('Mobiled_adindex_02') . '</div>';
        }

        //最新主题帖子
        $home_content_output .= home_bbs_topic_component();

        $home_content_output .= '<div class="my-4"><a class="btn btn-outline-secondary w-100" title="最新帖子" href="' . get_home_url() . '/forums">进入论坛 <i class="fa-solid fa-angle-right"></i></a></div>';

        //因为缓存导致关注列表不输出
        //输出我关注的用户新投稿
        //$followed_post_list_title = '我关注的';
        //$followed_post_list_link  = get_home_url() . "/followed";
        //$followed_post_list       = get_my_followed_post_list( $followed_post_list_length );
        //$home_content_output      .= recently_posts_component( $followed_post_list, $followed_post_list_title, $followed_post_list_link, '' );

        // 首页 - 最新发布上方横幅 广告
        if (dopt('d_adindex_03_b'))
        {
            $home_content_output .= '<div class="pop-banner d-none d-md-block text-center my-4 py-2">' . dopt('d_adindex_03') . '</div>';
        }
        if (dopt('Mobiled_adindex_03_b'))
        {
            $home_content_output .= '<div class="pop-banner d-block d-md-none text-center my-3">' . dopt('Mobiled_adindex_03') . '</div>';
        }

        //输出最新文章列表
        $recently_post_list_title = '最新发布';
        $recently_post_list_link = get_home_url() . "/page/1";
        $recently_post_list = [];


        /*插入飞机杯临时广告========================*/
        //$publish_post = get_post(167905);
        //array_splice($wp_query->posts, 1, 0, [$publish_post]);
        /*========================*/

        //创建对应长度的文章列表
        for ($i = 0; $i < $default_list_length; $i++)
        {
            $recently_post_list[] = new My_Post_Hot($wp_query->posts[$i]);
        }

        $recently_posts_component = recently_posts_component($recently_post_list, $recently_post_list_title, $recently_post_list_link, '');
        $hot_posts_sidebar_component = get_home_hot_posts_sidebar_component_by_random(null, 6);

        $home_content_output .= <<<HTML
        <div class="my-4 row">

                <div class="col-12 col-xl-9">
                    {$recently_posts_component}
                </div>
                <div class="col-3 d-none d-xl-block">
                    {$hot_posts_sidebar_component}
                </div>

        </div>


HTML;



        //获取不同分区的 最新文章列表
        for ($i = 0; $i < count($cat_ids_list); $i++)
        {
            $cat_id = $cat_ids_list[$i]['id'];
            $cat_icon = $cat_ids_list[$i]['icon'];
            $cat_name = get_the_category_by_ID($cat_id);
            $cat_link = get_category_link($cat_id);
            $post_list = get_cat_recently_post_list($cat_id, $cat_list_length);

            $recently_posts_component = recently_posts_component($post_list, $cat_name, $cat_link, $cat_icon);

            $hot_posts_sidebar_component = get_home_hot_posts_sidebar_component_by_random($cat_id, 6);

            $home_content_output .= <<<HTML
            <div class="my-4 row">
    
                <div class="col-12 col-xl-9">
                    {$recently_posts_component}
                </div>
                <div class="col-3 d-none d-xl-block">
                    {$hot_posts_sidebar_component}
                </div>
    
            </div>
HTML;
        }


        $home_footer_ad = '';
        if (dopt('Mobiled_home_footer_b'))
        {
            $home_footer_ad =  '<div class="pop-banner d-none d-md-none text-center my-2">' . dopt('Mobiled_home_footer') . '</div>';
        }

        //页底 查看更多
        $home_content_output .= <<<HTML

        {$home_footer_ad}
    
        <div class="row text-center my-4">
            <div class="col">
                <a class=" btn btn-outline-secondary btn-lg" title="最新发布" href="{$recently_post_list_link}">
		                查看更多 <i class="fa-solid fa-angle-right"></i>
		            </a>
            </div>
        </div>

HTML;


        //替换首页图片域名为西部数码自定义CDN地址
        //============================================================
        /*
        $array_old_domain = [
            'href="https://www.mikuclub.online',
            'href="https://www.mikuclub.eu',
            'href="https://www.mikuclub.win',
        ];

        $main_domain = 'href="https://www.mikuclub.cc';
        //把A链接里的可能存在的备用域名改为主域名
        $home_content_output = str_replace($array_old_domain, $main_domain, $home_content_output);
        */

        $home_content_output = fix_site_domain_with_domain_main($home_content_output);


        //=============================================================

        //创建缓存
        File_Cache::set_cache_meta($cache_key, File_Cache::DIR_COMPONENTS, $home_content_output);
    }


    return $home_content_output;
}
