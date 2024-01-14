<?php

//ini_set('display_errors',1);            //错误信息
//ini_set('display_startup_errors',1);    //php启动错误信息
//error_reporting(-1);                    //打印出所有的 错误信息


namespace mikuclub;

use mikuclub\constant\Category;
use mikuclub\constant\Config;
use mikuclub\User_Capability;
use mikuclub\constant\User_Meta;
use mikuclub\constant\Web_Domain;


//导入加载器
require_once 'lib/autoload.php';


/**
 * 在前台加载 第三方脚本和CSS
 *
 * @return void
 */
function setup_front_end_external_css_and_script()
{

    //移除现有js脚本
    wp_deregister_script('l10n');
    wp_deregister_script('jquery');

    /*
    七牛云 staticfile CDN库
    ==========================================================================
    */

    // //jquery库
    // wp_enqueue_script('jquery', 'https://cdn.staticfile.org/jquery/3.5.1/jquery.min.js', [], false, false);

    // //fontanwesome图标库
    // wp_enqueue_style('fontawesome', 'https://cdn.staticfile.org/font-awesome/6.5.1/css/fontawesome.min.css', [], false);
    // wp_enqueue_style('fontawesome-solid', 'https://cdn.staticfile.org/font-awesome/6.5.1/css/solid.min.css', [], false);
    // wp_enqueue_style('fontawesome-brand', 'https://cdn.staticfile.org/font-awesome/6.5.1/css/brands.min.css', [], false);

    // //bootstrap库
    // wp_enqueue_style('twitter-bootstrap-css', 'https://cdn.staticfile.org/twitter-bootstrap/5.3.2/css/bootstrap.min.css', [], false);
    // wp_enqueue_script('twitter-bootstrap-js', 'https://cdn.staticfile.org/twitter-bootstrap/5.3.2/js/bootstrap.bundle.min.js', [], false, true);


    // //图片灯箱 lightbox2库
    // wp_enqueue_style('lightbox2-css', 'https://cdn.staticfile.org/lightbox2/2.11.1/css/lightbox.min.css', [], false);
    // wp_enqueue_script('lightbox2-js', 'https://cdn.staticfile.org/lightbox2/2.11.1/js/lightbox.min.js', [], false, true);

    //UA解析 UAParser JS库
    // wp_enqueue_script('ua-parser', 'https://cdn.staticfile.org/UAParser.js/1.0.35/ua-parser.min.js', [], false, true);

    // //图片裁剪 JS库
    // wp_enqueue_script('cropper-js', 'https://cdn.staticfile.org/cropperjs/2.0.0-alpha.1/cropper.min.js', [], false, true);
    // wp_enqueue_style('cropper-css', 'https://cdn.staticfile.org/cropperjs/2.0.0-alpha.1/cropper.min.css', [], false);
    // wp_enqueue_script('jquery-cropper', 'https://cdn.staticfile.org/jquery-cropper/1.0.1/jquery-cropper.min.js', [], false, true);


    // /**
    //  * 页内平滑滚动
    //  * @see https://github.com/flesler/jquery.scrollTo
    //  */
    // wp_enqueue_script('jquery-scrollto', 'https://cdn.staticfile.org/jquery-scrollTo/2.1.3/jquery.scrollTo.min.js', [], false, true);





    // /*
    // Boot CDN库
    // ==========================================================================
    // */

    // //jquery库 BootCDN版本
    // wp_enqueue_script('jquery', 'https://cdn.bootcdn.net/ajax/libs/jquery/3.7.1/jquery.min.js', [], false, false);

    // //fontanwesome图标库 BootCDN版本
    // wp_enqueue_style('fontawesome', 'https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.2/css/fontawesome.min.css', [], false);
    // wp_enqueue_style('fontawesome-solid', 'https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.2/css/solid.min.css', [], false);
    // wp_enqueue_style('fontawesome-brand', 'https://cdn.bootcdn.net/ajax/libs/font-awesome/6.4.2/css/brands.min.css', [], false);

    // //bootstrap库
    // wp_enqueue_style('twitter-bootstrap-css', 'https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/5.3.1/css/bootstrap.min.css', [], false);
    // wp_enqueue_script('twitter-bootstrap-js', 'https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/5.3.1/js/bootstrap.bundle.min.js', [], false, true);


    // //图片灯箱 lightbox2库
    // wp_enqueue_style('lightbox2-css', 'https://cdn.bootcdn.net/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css', [], false);
    // wp_enqueue_script('lightbox2-js', 'https://cdn.bootcdn.net/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js', [], false, true);

    // //UA解析 UAParser JS库
    // wp_enqueue_script('ua-parser', 'https://cdn.bootcdn.net/ajax/libs/UAParser.js/1.0.36/ua-parser.min.js', [], false, true);

    // //图片裁剪 JS库
    // wp_enqueue_script('cropperjs-js', 'https://cdn.bootcdn.net/ajax/libs/cropperjs/2.0.0-alpha.1/cropper.min.js', [], false, true);
    // wp_enqueue_style('cropperjs-css', 'https://cdn.bootcdn.net/ajax/libs/cropperjs/2.0.0-alpha.1/cropper.min.css', [], false);
    // wp_enqueue_script('jquery-cropper', 'https://cdn.bootcdn.net/ajax/libs/jquery-cropper/1.0.1/jquery-cropper.min.js', [], false, true);


    // /**
    //  * 页内平滑滚动
    //  * @see https://github.com/flesler/jquery.scrollTo
    //  */
    // wp_enqueue_script('jquery-scrollto', 'https://cdn.bootcdn.net/ajax/libs/jquery-scrollTo/2.1.3/jquery.scrollTo.min.js', [], false, true);


    /*
    字节跳动 CDN库
    ==========================================================================
    */

    //jquery库 
    wp_enqueue_script('jquery', 'https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-y/jquery/3.6.0/jquery.min.js', [], false, false);

    //fontanwesome图标库 
    wp_enqueue_style('fontawesome', 'https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-y/font-awesome/6.0.0/css/fontawesome.min.css', [], false);
    wp_enqueue_style('fontawesome-solid', 'https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-y/font-awesome/6.0.0/css/solid.min.css', [], false);
    wp_enqueue_style('fontawesome-brand', 'https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-y/font-awesome/6.0.0/css/brands.min.css', [], false);

    //bootstrap库 用staticfile地址, 因为字节跳动没有新版
    wp_enqueue_style('twitter-bootstrap-css', 'https://cdn.staticfile.org/twitter-bootstrap/5.3.2/css/bootstrap.min.css', [], false);
    wp_enqueue_script('twitter-bootstrap-js', 'https://cdn.staticfile.org/twitter-bootstrap/5.3.2/js/bootstrap.bundle.min.js', [], false, true);

    //图片灯箱 lightbox2库
    wp_enqueue_style('lightbox2-css', 'https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-y/lightbox2/2.11.3/css/lightbox.min.css', [], false);
    wp_enqueue_script('lightbox2-js', 'https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-y/lightbox2/2.11.3/js/lightbox.min.js', [], false, true);

    //UA解析 UAParser JS库
    wp_enqueue_script('ua-parser', 'https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-y/UAParser.js/1.0.2/ua-parser.min.js', [], false, true);

    //图片裁剪 JS库
    wp_enqueue_script('cropperjs-js', 'https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-y/cropperjs/2.0.0-alpha.2/cropper.min.js', [], false, true);
    wp_enqueue_style('cropperjs-css', 'https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-y/cropperjs/2.0.0-alpha.2/cropper.min.css', [], false);
    wp_enqueue_script('jquery-cropper', 'https://lf3-cdn-tos.bytecdntp.com/cdn/expire-1-y/jquery-cropper/1.0.1/jquery-cropper.min.js', [], false, true);


    /**
     * 页内平滑滚动
     * @see https://github.com/flesler/jquery.scrollTo
     */
    wp_enqueue_script('jquery-scrollto', 'https://cdn.bootcdn.net/ajax/libs/jquery-scrollTo/2.1.3/jquery.scrollTo.min.js', [], false, true);

    /*
    本地 CDN库
    ==========================================================================
    */

    //fontanwesome图标库 本地版本
    // $template_directory_uri = get_template_directory_uri();
    // wp_enqueue_style('fontawesome', $template_directory_uri . '/css/font-awesome/6.5.1/css/fontawesome.min.css', [], false);
    // wp_enqueue_style('fontawesome-solid', $template_directory_uri . '/css/font-awesome/6.5.1/css/solid.min.css', [], false);
    // wp_enqueue_style('fontawesome-brand', $template_directory_uri . '/css/font-awesome/6.5.1/css/brands.min.css', [], false);

}

/**
 * 在前台加载自定义CSS
 *
 * @return void
 */
function setup_front_end_css()
{

    $custom_styles = [
        //基础CSS
        [
            'name' => 'style-system',
            'path' => '/css/style-system.css',
        ],
        //夜间模式CSS
        [
            'name' => 'style-darkmode',
            'path' => '/css/style-darkmode.css',
        ],
        //自定义CSS
        [
            'name' => 'style',
            'path' => '/css/style.css',
        ],
        //图片相关CSS
        [
            'name' => 'style-image',
            'path' => '/css/style-image.css',
        ],
        //文章页CSS
        [
            'name' => 'style-single',
            'path' => '/css/style-single.css',
        ],
        //论坛CSS
        [
            'name' => 'style-forums',
            'path' => '/css/style-forums.css',
        ],
        //投稿页CSS
        [
            'name' => 'style-tougao',
            'path' => '/css/style-tougao.css',
        ],
    ];

    $template_directory_uri = get_template_directory_uri();

    foreach ($custom_styles as $style)
    {
        wp_enqueue_style($style['name'], $template_directory_uri . $style['path'], [], Config::CSS_JS_VERSION);
    }
}


/**
 * 在前台加载自定义脚本
 *
 * @return void
 */
function setup_front_end_script()
{

    $custom_scripts = [

        //JS变量
        [
            'name' => 'js-constant',
            'path' => '/js/common/constant.js',
            'in_footer' => false,
        ],
        //自定义基础JS函数和变量
        [
            'name' => 'js-base',
            'path' => '/js/common/base.js',
            'in_footer' => false,
        ],
        //自定义JS 小弹窗类
        [
            'name' => 'js-class-toast',
            'path' => '/js/class/class-toast.js',
            'in_footer' => false,
        ],
        //自定义JS 模态窗类
        [
            'name' => 'js-class-modal',
            'path' => '/js/class/class-modal.js',
            'in_footer' => false,
        ],
        //自定义JS 文章类
        [
            'name' => 'js-class-post',
            'path' => '/js/class/class-post.js',
            'in_footer' => false,
        ],
        //自定义JS 用户类
        [
            'name' => 'js-class-user',
            'path' => '/js/class/class-user.js',
            'in_footer' => false,
        ],
        //自定义JS 消息类
        [
            'name' => 'js-class-message',
            'path' => '/js/class/class-message.js',
            'in_footer' => false,
        ],
        //自定义JS 评论类
        [
            'name' => 'js-class-comment',
            'path' => '/js/class/class-comment.js',
            'in_footer' => false,
        ],
        //自定义JS 评论类
        [
            'name' => 'js-class-ua-parser',
            'path' => '/js/class/class-ua-parser.js',
            'in_footer' => false,
        ],

        // //名言名句变量
        // [
        //     'name' => 'js-phrases',
        //     'path' => '/js/phrases.js',
        //     'in_footer' => false,
        // ],

        //通用JS
        [
            'name' => 'js-function',
            'path' => '/js/function.js',
            'in_footer' => false,
        ],
        //模态窗JS
        [
            'name' => 'js-function-modal',
            'path' => '/js/function-modal.js',
            'in_footer' => false,
        ],
        //浮动菜单相关JS
        [
            'name' => 'js-function-float-menu-bar',
            'path' => '/js/function-float-menu-bar.js',
            'in_footer' => false, //必须在顶部, 不然会有视觉延时
        ],

        //通用JS AJAX的函数
        [
            'name' => 'js-function-ajax',
            'path' => '/js/function-ajax.js',
            'in_footer' => true,
        ],

        //JS 监听
        [
            'name' => 'js-listener',
            'path' => '/js/listener.js',
            'in_footer' => false,
        ],
        //JS 广告
        [
            'name' => 'js-pub',
            'path' => '/js/pub.js',
            'in_footer' => false,
        ],
        //JS 文章列表头部加载
        [
            'name' => 'function-post-list-header',
            'path' => '/js/function-post-list-header.js',
            'in_footer' => true,
        ],
        //JS 文章列表加载
        [
            'name' => 'js-post-list',
            'path' => '/js/function-post-list.js',
            'in_footer' => true,
        ],
        //JS 文章
        [
            'name' => 'js-post',
            'path' => '/js/function-post.js',
            'in_footer' => true,
        ],
        //JS 用户
        [
            'name' => 'js-user',
            'path' => '/js/function-user.js',
            'in_footer' => true,
        ],
        //JS 投稿页面
        [
            'name' => 'js-tougao',
            'path' => '/js/page-tougao.js',
            'in_footer' => false,
        ],
        //JS 失效列表 页面
        [
            'name' => 'js-fail-down-list',
            'path' => '/js/page-fail-down-list.js',
            'in_footer' => false,
        ],

        //JS 收藏页面
        [
            'name' => 'js-favorite',
            'path' => '/js/page-favorite.js',
            'in_footer' => false,
        ],
        //JS 消息页面
        [
            'name' => 'js-message',
            'path' => '/js/page-message.js',
            'in_footer' => false,
        ],
        //JS 文章页面
        [
            'name' => 'js-page-post',
            'path' => '/js/page-post.js',
            'in_footer' => false,
        ],
        //JS 文章页面评论功能
        [
            'name' => 'js-page-post-comment',
            'path' => '/js/page-post-comment.js',
            'in_footer' => false,
        ],

        //JS UP主投稿管理页面
        [
            'name' => 'js-uploader',
            'path' => '/js/page-uploader.js',
            'in_footer' => false,
        ],
        //JS 用户个人信息页
        [
            'name' => 'js-profile',
            'path' => '/js/page-profile.js',
            'in_footer' => false,
        ],
        //JS 签到页
        [
            'name' => 'js-qiandao',
            'path' => '/js/page-qiandao.js',
            'in_footer' => false,
        ],
        //JS 浏览历史
        [
            'name' => 'js-history',
            'path' => '/js/page-history.js',
            'in_footer' => false,
        ],
        //JS 关注页
        [
            'name' => 'js-followed',
            'path' => '/js/page-followed.js',
            'in_footer' => false,
        ],
        //JS 论坛
        [
            'name' => 'js-forums',
            'path' => '/js/page-forums.js',
            'in_footer' => false,
        ],

    ];

    $template_directory_uri = get_template_directory_uri();

    foreach ($custom_scripts as $script)
    {
        wp_enqueue_script($script['name'], $template_directory_uri . $script['path'], [], Config::CSS_JS_VERSION, $script['in_footer']);
    }
}


/**
 * 在前台添加自定义JS变量
 *
 * @return void
 */
function setup_front_end_script_variable()
{

    //动态生成js数据
    $dynamic_variable = [
        'home' => get_site_url(),
        'apiRoot' => esc_url_raw(rest_url()),
        //添加nonce数据, 来支持调用rest api
        'nonce' => wp_create_nonce('wp_rest'),
        'user_id' => get_current_user_id(),
        'is_admin' => User_Capability::is_admin(),
        'is_premium_user' => User_Capability::is_premium_user(),
        User_Meta::USER_BLACK_LIST => get_user_black_list(get_current_user_id()),
        User_Meta::USER_FAVORITE_POST_LIST => get_user_favorite(),
    ];
    //如果是文章页
    if (is_single())
    {
        $post_id = get_the_ID();
        $dynamic_variable['post_id'] = $post_id;
        $dynamic_variable['post_main_cat'] = get_post_main_cat_id($post_id);
        $dynamic_variable['post_author_id'] = get_post_field('post_author', $post_id);
    }
    //如果是分类页
    if (is_category())
    {
        $dynamic_variable['category_id'] = get_queried_object_id();
    }

    //wp_localize_script('js-base', 'MY_SITE', $dynamic_variable);
    wp_add_inline_script('js-base', 'const MY_SITE =' . json_encode($dynamic_variable) . ';', 'before');
}








/**
 * 把http格式的链接转换成https格式
 *
 * @param string $link
 * @return string
 */
function convert_link_to_https($link)
{
    $http_start = 'http:';
    $https_start = 'https:';

    //链接不是空
    if ($link)
    {
        //如果链接是 // 斜杠开头, 需要手动添加头部
        if (stripos($link, "//") === 0)
        {
            //添加头部
            $link = $https_start . $link;
        }
        else
        {
            //把http转换成https
            $link = str_replace($http_start, $https_start, $link);
        }
    }

    return $link;
}





/**
 * 获取随机顶部封面图地址
 * @param int $index 可选 支持指定数字
 * @return string
 */
function get_random_head_background_image($index = null)
{
    //图片数量
    $image_count = 125;
    //如果未指定
    if ($index === null)
    {
        //通过	一年中的第几天 + 当前小时 然后 除余 图片数量 得出 随机数
        $index = (date('z') + date('G')) % $image_count + 1;
    }

    return 'https://' . Web_Domain::CDN_MIKUCLUB_FUN . '/top/' . $index . '.webp';
}




/**
 * 使用file1 替换默认图片域名
 *
 * @param string|string[] $image_src
 * @return string|string[]
 */
function fix_image_domain_with_file_1($image_src)
{
    $array_search = array_merge(Web_Domain::get_array_site_domain(), Web_Domain::get_array_file_domain());

    $new_domain = Web_Domain::FILE1_MIKUCLUB_FUN;

    $images_src = str_replace($array_search, $new_domain, $image_src);

    return $images_src;
}

/**
 * 使用file2 替换默认图片域名
 *
 * @param string|string[] $image_src
 * @return string|string[]
 */
function fix_image_domain_with_file_2($image_src)
{

    $array_search = array_merge(Web_Domain::get_array_site_domain(), Web_Domain::get_array_file_domain());

    $new_domain = Web_Domain::FILE2_MIKUCLUB_FUN;

    $images_src = str_replace($array_search, $new_domain, $image_src);

    return $images_src;
}

/**
 * 使用file3 替换默认图片域名
 *
 * @param string|string[] $image_src
 * @return string|string[]
 */
function fix_image_domain_with_file_3($image_src)
{

    $array_search = array_merge(Web_Domain::get_array_site_domain(), Web_Domain::get_array_file_domain());

    $new_domain = Web_Domain::FILE3_MIKUCLUB_FUN;

    $images_src = str_replace($array_search, $new_domain, $image_src);

    return $images_src;
}

/**
 * 使用file4 替换默认图片域名
 *
 * @param string|string[] $image_src
 * @return string|string[]
 */
function fix_image_domain_with_file_4($image_src)
{
    $array_search = array_merge(Web_Domain::get_array_site_domain(), Web_Domain::get_array_file_domain());

    $new_domain = Web_Domain::FILE4_MIKUCLUB_FUN;

    $images_src = str_replace($array_search, $new_domain, $image_src);

    return $images_src;
}

/**
 * 使用file5 替换默认图片域名
 *
 * @param string|string[] $image_src
 * @return string|string[]
 */
function fix_image_domain_with_file_5($image_src)
{
    $array_search = array_merge(Web_Domain::get_array_site_domain(), Web_Domain::get_array_file_domain());

    $new_domain = Web_Domain::FILE5_MIKUCLUB_FUN;

    $images_src = str_replace($array_search, $new_domain, $image_src);

    return $images_src;
}

/**
 * 使用file6 替换默认图片域名
 *
 * @param string|string[] $image_src
 * @return string|string[]
 */
function fix_image_domain_with_file_6($image_src)
{
    $array_search = array_merge(Web_Domain::get_array_site_domain(), Web_Domain::get_array_file_domain());

    $new_domain = Web_Domain::FILE6_MIKUCLUB_FUN;

    $images_src = str_replace($array_search, $new_domain, $image_src);

    return $images_src;
}

/**
 * 使用static 替换默认图片域名
 *
 * @param string|string[] $image_src
 * @return string|string[]
 */
function fix_image_domain_with_static($image_src)
{
    $array_search = array_merge(Web_Domain::get_array_site_domain(), Web_Domain::get_array_file_domain());

    $new_domain = Web_Domain::STATIC_MIKUCLUB_FUN;

    $images_src = str_replace($array_search, $new_domain, $image_src);

    return $images_src;
}

/**
 * 修正域名地址为主域名
 *
 * @param string|string[] $link
 * @return string|string[]
 */
function fix_site_domain_with_domain_main($link)
{
    $array_search = Web_Domain::get_array_site_domain();

    $new_domain = Web_Domain::get_main_site_domain();

    $result = str_replace($array_search, $new_domain, $link);

    return $result;
}

/**
 * 修正域名地址为当前用户访问的主域名
 *
 * @param string|string[] $link
 * @return string|string[]
 */
function fix_site_domain_with_current_domain($link)
{
    $array_search = Web_Domain::get_array_site_domain();

    //获取当前在访问的主域名 不包含https前缀
    $new_domain  = get_site_url();
    $new_domain = preg_replace("(^https?://)", "", $new_domain);

    $result = str_replace($array_search, $new_domain, $link);

    return $result;
}

/**
 * 修复链接的HTTPS前缀
 *
 * @param string[] $array_link
 * @return string[]
 */
function fix_https_prefix($array_link)
{
    $old_prefix = '//';
    $new_prefix = 'https:';

    $result = array_map(function ($element) use ($old_prefix, $new_prefix)
    {
        // 检查字符串是否以'//'开头，如果就加上https协议
        return (substr($element, 0, 2) === $old_prefix) ? $new_prefix . $element : $element;
    }, $array_link);

    return $result;
}

/**
 * 格式化DEBUG输出
 *
 * @param mixed $value
 * @return void
 */
function var_dump_formatted($value)
{

    echo '<pre>';
    var_dump($value);
    echo '</pre>';
}
