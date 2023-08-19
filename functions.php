<?php

//ini_set('display_errors',1);            //错误信息
//ini_set('display_startup_errors',1);    //php启动错误信息
//error_reporting(-1);                    //打印出所有的 错误信息

//导入主题页面

use LDAP\Result;

require_once 'admin/config.php';

//导入自定义模型类
require_once 'class/index.php';

//导入自定义函数
require_once 'functions/index.php';

//导入组件
require_once 'component/index.php';

//导入挂件配置
require_once 'widgets/index.php';


/*
 * 在前台加载自定义脚本和CSS
 */
function setup_front_script()
{

    //移除现有js脚本
    wp_deregister_script('l10n');
    wp_deregister_script('jquery');

    /*七牛云 staticfile CDN库*/


    //jquery库
    wp_enqueue_script('jquery', 'https://cdn.staticfile.org/jquery/3.5.1/jquery.min.js', false, '3.5.1', false);

    //vue3库
    //wp_enqueue_script('vue-3', 'https://cdn.staticfile.org/vue/3.3.4/vue.global.prod.min.js', false, '3.3.4', false);

    //fontanwesome图标库
    wp_enqueue_style('fontawesome', 'https://cdn.staticfile.org/font-awesome/6.3.0/css/fontawesome.min.css', false, '5.13');
    wp_enqueue_style('fontawesome-solid', 'https://cdn.staticfile.org/font-awesome/6.3.0/css/solid.min.css', false, '5.13');
    wp_enqueue_style('fontawesome-brand', 'https://cdn.staticfile.org/font-awesome/6.3.0/css/brands.min.css', false, '5.13');
    //wp_enqueue_style('fontawesome-regular', 'https://cdn.staticfile.org/font-awesome/6.3.0/css/regular.min.css', false, '5.13');

    //bootstrap库
    wp_enqueue_style('twitter-bootstrap-css', 'https://cdn.staticfile.org/twitter-bootstrap/5.3.1/css/bootstrap.min.css', false, '5.3.1');
    wp_enqueue_script('twitter-bootstrap-js', 'https://cdn.staticfile.org/twitter-bootstrap/5.3.1/js/bootstrap.bundle.min.js', false, '5.3.1', true);


    //图片灯箱 lightbox2库
    wp_enqueue_style('lightbox2-css', 'https://cdn.staticfile.org/lightbox2/2.11.1/css/lightbox.min.css', false, '1.0');
    wp_enqueue_script('lightbox2-js', 'https://cdn.staticfile.org/lightbox2/2.11.1/js/lightbox.min.js', false, '1.0', true);

    //UA解析 JS库
    wp_enqueue_script('ua-parser', 'https://cdn.staticfile.org/UAParser.js/1.0.35/ua-parser.min.js', false, '1.0.35', true);

    //图片裁剪 JS库
    wp_enqueue_script('cropper-js', 'https://cdn.staticfile.org/cropperjs/2.0.0-alpha.1/cropper.min.js', false, '2.0.0', true);
    wp_enqueue_style('cropper-css', 'https://cdn.staticfile.org/cropperjs/2.0.0-alpha.1/cropper.min.css', false, '2.0.0');
    wp_enqueue_script('jquery-cropper', 'https://cdn.staticfile.org/jquery-cropper/1.0.1/jquery-cropper.min.js', false, '1.0.1', true);





    $custom_styles = [
        //基础CSS
        [
            'name' => 'style-system',
            'path' => '/css/style-system.css',
            'version' => '3.08'
        ],
        //自定义CSS
        [
            'name' => 'style',
            'path' => '/style.css',
            'version' => '3.10'
        ],
        //论坛CSS
        [
            'name' => 'style-forums',
            'path' => '/css/style-forums.css',
            'version' => '3.23'
        ],
        //主页CSS
        [
            'name' => 'style-home',
            'path' => '/css/style-home.css',
            'version' => '3.03'
        ],
        //投稿页CSS
        [
            'name' => 'style-tougao',
            'path' => '/css/style-tougao.css',
            'version' => '1.07'
        ],
        //手机CSS
        [
            'name' => 'style-wap',
            'path' => '/css/style-wap.css',
            'version' => '1.02'
        ],
        //夜间模式CSS
        [
            'name' => 'style-darkmode',
            'path' => '/css/style-darkmode.css',
            'version' => '1.17'
        ],
    ];

    foreach ($custom_styles as $style)
    {
        wp_enqueue_style($style['name'], get_template_directory_uri() . $style['path'], false, $style['version']);
    }

    $custom_scripts = [

        //自定义JS 小弹窗类
        [
            'name' => 'js-class-toast',
            'path' => '/js/class-toast.js',
            'version' => '1.05',
            'in_footer' => false,
        ],
        //自定义JS 模态窗类
        [
            'name' => 'js-class-modal',
            'path' => '/js/class-modal.js',
            'version' => '1.05',
            'in_footer' => false,
        ],
        //自定义JS 文章类
        [
            'name' => 'js-class-post',
            'path' => '/js/class-post.js',
            'version' => '1.08',
            'in_footer' => false,
        ],
        //自定义JS 用户类
        [
            'name' => 'js-class-user',
            'path' => '/js/class-user.js',
            'version' => '1.04',
            'in_footer' => false,
        ],
        //自定义JS 消息类
        [
            'name' => 'js-class-message',
            'path' => '/js/class-message.js',
            'version' => '1.04',
            'in_footer' => false,
        ],
        //自定义JS 评论类
        [
            'name' => 'js-class-comment',
            'path' => '/js/class-comment.js',
            'version' => '1.16',
            'in_footer' => false,
        ],
        //自定义JS 评论类
        [
            'name' => 'js-class-ua-parser',
            'path' => '/js/class-ua-parser.js',
            'version' => '1.04',
            'in_footer' => false,
        ],
        //自定义基础JS函数和变量
        [
            'name' => 'js-base',
            'path' => '/js/base.js',
            'version' => '1.20',
            'in_footer' => false,
        ],
        //名言名句变量
        [
            'name' => 'js-phrases',
            'path' => '/js/phrases.js',
            'version' => '1.03',
            'in_footer' => false,
        ],

        //通用JS
        [
            'name' => 'js-function',
            'path' => '/js/function.js',
            'version' => '1.46',
            'in_footer' => false,
        ],
        //通用JS AJAX的函数
        [
            'name' => 'js-function-ajax',
            'path' => '/js/function-ajax.js',
            'version' => '1.12',
            'in_footer' => false,
        ],
        //JS 页面加载完成后自动运行
        [
            'name' => 'js-on-load',
            'path' => '/js/script-on-load.js',
            'version' => '1.05',
            'in_footer' => false,
        ],
        //JS 监听
        [
            'name' => 'js-listener',
            'path' => '/js/listener.js',
            'version' => '1.26',
            'in_footer' => false,
        ],
        //JS 广告
        [
            'name' => 'js-pub',
            'path' => '/js/pub.js',
            'version' => '1.14',
            'in_footer' => false,
        ],
        //JS 投稿页面
        [
            'name' => 'js-tougao',
            'path' => '/js/page-tougao.js',
            'version' => '1.39',
            'in_footer' => false,
        ],
        //JS 失效列表 页面
        [
            'name' => 'js-fail-down-list',
            'path' => '/js/page-fail-down-list.js',
            'version' => '1.08',
            'in_footer' => false,
        ],
        //JS 作者页面
        [
            'name' => 'js-author',
            'path' => '/js/page-author.js',
            'version' => '1.06',
            'in_footer' => false,
        ],
        //JS 收藏页面
        [
            'name' => 'js-favorite',
            'path' => '/js/page-favorite.js',
            'version' => '1.10',
            'in_footer' => false,
        ],
        //JS 消息页面
        [
            'name' => 'js-message',
            'path' => '/js/page-message.js',
            'version' => '1.07',
            'in_footer' => false,
        ],
        //JS 文章页面
        [
            'name' => 'js-post',
            'path' => '/js/page-post.js',
            'version' => '1.24',
            'in_footer' => false,
        ],
        //JS 文章页面评论功能
        [
            'name' => 'js-post-comment',
            'path' => '/js/page-post-comment.js',
            'version' => '1.10',
            'in_footer' => false,
        ],

        //JS UP主投稿管理页面
        [
            'name' => 'js-uploader',
            'path' => '/js/page-uploader.js',
            'version' => '1.15',
            'in_footer' => false,
        ],
        //JS 用户个人信息页
        [
            'name' => 'js-profile',
            'path' => '/js/page-profile.js',
            'version' => '1.07',
            'in_footer' => false,
        ],
        //JS 签到页
        [
            'name' => 'js-qiandao',
            'path' => '/js/page-qiandao.js',
            'version' => '1.07',
            'in_footer' => false,
        ],
        //JS 浏览历史
        [
            'name' => 'js-history',
            'path' => '/js/page-history.js',
            'version' => '1.11',
            'in_footer' => false,
        ],
        //JS 关注页
        [
            'name' => 'js-followed',
            'path' => '/js/page-followed.js',
            'version' => '1.00',
            'in_footer' => false,
        ],
        //JS 论坛
        [
            'name' => 'js-forums',
            'path' => '/js/page-forums.js',
            'version' => '1.05',
            'in_footer' => false,
        ],
        //JS 暗夜模式
        [
            'name' => 'js-darkmode',
            'path' => '/js/darkmode.js',
            'version' => '1.10',
            'in_footer' => false, //必须在顶部, 不然会有视觉延时
        ],

    ];

    foreach ($custom_scripts as $script)
    {
        wp_enqueue_script($script['name'], get_template_directory_uri() . $script['path'], false, $script['version'], $script['in_footer']);
    }


    //动态生成js数据
    $dynamic_variable = [
        'home' => get_site_url(),
        'apiRoot' => esc_url_raw(rest_url()),
        //添加nonce数据, 来支持调用rest api
        'nonce' => wp_create_nonce('wp_rest'),
        'user_id' => get_current_user_id(),
        MY_USER_BLACK_LIST => get_user_black_list(get_current_user_id()),
        'is_admin' => current_user_is_admin(),
        'is_premium_user' => current_user_can_publish_posts(),
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



    wp_localize_script('js-base', 'MY_SITE', $dynamic_variable);
}

add_action('wp_enqueue_scripts', 'setup_front_script');


/**
 *  后台自定义CSS和JS
 */
function custom_admin_script()
{


    wp_enqueue_style('custom-admin-css', get_template_directory_uri() . '/css/style-admin.css', false, '1.03');
}

add_action('admin_enqueue_scripts', 'custom_admin_script');


//登陆页面 自定义css
function custom_login_script()
{

    //bootstrap css
    wp_enqueue_style('twitter-bootstrap-css', 'https://cdn.staticfile.org/twitter-bootstrap/4.5.0/css/bootstrap.min.css', false, '4.50');

    $version = '1.14';
    wp_enqueue_style('custom-system-css', get_template_directory_uri() . '/css/style-system.css', false, $version);
    wp_enqueue_style('custom-login-css', get_template_directory_uri() . '/css/style-login.css', false, $version);


    wp_enqueue_script('custom-login-js', get_template_directory_uri() . '/js/login.js', false, $version, true);
}

add_action('login_enqueue_scripts', 'custom_login_script');


/**
 * 检测是否在魔法区分类页或者文章页
 * 如果是, 并且没有登录 则使用js跳转页面
 */

/**
 * 检测是否是成人区分类
 * @return bool
 */
function is_adult_category()
{

    $is_adult_category = false;



    if (is_category())
    {
        $is_adult_category = (array_search(get_queried_object_id(), ADULT_CATEGORY_IDS) !== false);
    }

    else if (is_single())
    {
        //如果是在魔法区分类页和文章页
        $is_adult_category = in_category(ADULT_CATEGORY_IDS);
    }


    return $is_adult_category;
}


/**
 * 通过API上传图片的时候 添加自定义元数据
 *
 * @param int $post_id
 **/
function action_on_add_attachment($post_id)
{

    if (isset($_POST['meta_key']) && $_POST['meta_key'] && isset($_POST['meta_value']) && $_POST['meta_value'])
    {
        //添加元数据到对应的附件里
        update_post_meta($post_id, $_POST['meta_key'], $_POST['meta_value']);
    }
}

//在上传图片的时候激活挂钩
add_action('add_attachment', 'action_on_add_attachment');


/**
 * 输出子分类列表
 *
 * @param int $cat_id
 *
 * @return string
 */
function print_sub_categories($cat_id)
{

    $output = '';
    $args = ['parent' => $cat_id];
    //获取子分类数组不是空
    $sub_categories = get_categories($args);
    //如果子分类数组
    if ($sub_categories)
    {

        $output = '<div class="sub-categories-container">
                                <h2><i class="fas fa-th-large" aria-hidden="true"></i> 子分区</h2>
                                <div class="sub-categories">
                            ';

        foreach ($sub_categories as $sub_category)
        {
            $link = get_category_link($sub_category->term_id);
            $name = $sub_category->name;
            $post_count = $sub_category->count;
            $output .= "<div class=\"sub-category\">
                                    <a href=\"{$link}\">
                                        {$name}
                                        <span class=\"post-count\">( {$post_count} )</span>
                                    </a>
                                    
                                </div>";
        }

        $output .= '</div>
                        </div>';
    }

    return $output;
}


/**
 * 获取顶部左菜单
 * @return string 菜单html列表
 */
function get_top_left_menu()
{


    $meta_cache_key = 'top_left_menu';
    //获取缓存
    $menu_item_list = '';
    //$menu_item_list = get_transient_cache_meta( $meta_cache_key );
    //如果缓存无效
    if (!$menu_item_list)
    {
        $menu_item_list = wp_nav_menu([
            'theme_location' => 'top_left_menu',
            'menu_class' => 'navbar-nav',
            'container' => '',
            'fallback_cb' => 'WP_Bootstrap_Navwalker::fallback',
            'walker' => new WP_Bootstrap_Navwalker(),
            'echo' => false,
        ]);
        //set_transient_cache_meta( $meta_cache_key, $menu_item_list, EXPIRED_1_HOUR );
    }

    return $menu_item_list;
}


/**
 * 获取顶主菜单
 * @return string 菜单html列表
 */
function get_main_menu()
{

    $meta_cache_key = 'main_menu';
    //获取缓存
    //$menu_item_list = get_transient_cache_meta( $meta_cache_key );
    $menu_item_list = '';

    //如果缓存无效
    if (!$menu_item_list)
    {

        $menu_item_list = wp_nav_menu([
            'theme_location' => 'nav',
            'echo' => false,
            'container' => '',
            'depth' => 2,
            'menu_class' => 'navbar-nav flex-fill flex-wrap',
            'fallback_cb' => 'WP_Bootstrap_Navwalker::fallback',
            'walker' => new WP_Bootstrap_Navwalker(),
        ]);

        //set_transient_cache_meta( $meta_cache_key, $menu_item_list, EXPIRED_1_HOUR );
    }

    return $menu_item_list;
}

/**
 * 获取底部菜单
 * @return string 菜单html列表
 */
function get_bottom_menu()
{

    $meta_cache_key = 'bottom_menu';
    //获取缓存
    //$menu_item_list = get_transient_cache_meta( $meta_cache_key );
    $menu_item_list = '';
    //如果缓存无效
    if (!$menu_item_list)
    {
        $menu_item_list = wp_nav_menu([
            'theme_location' => 'bottom_menu',
            'menu_class' => 'nav',
            'container' => '',
            'fallback_cb' => 'WP_Bootstrap_Navwalker::fallback',
            'walker' => new WP_Bootstrap_Navwalker(),
            'echo' => false,
        ]);
        //set_transient_cache_meta( $meta_cache_key, $menu_item_list, EXPIRED_1_HOUR );
    }

    return $menu_item_list;
}

/**
 *输出友情链接列表
 * @return string 友情链接 html 代码
 */
function get_friends_links()
{


    $meta_cache_key = 'friends_links';
    //获取缓存
    $links_list_html = get_cache_meta($meta_cache_key, '', EXPIRED_1_DAY);
    //如果缓存无效
    if (!$links_list_html)
    {

        //获取链接列表
        $links_list = get_bookmarks();
        $links_list_html = '';
        foreach ($links_list as $link)
        {

            $links_list_html .= <<< HTML

                        <li class="nav-item">
                            <a class="nav-link" title="{$link->link_name}" href="{$link->link_url}" target="_blank">
                                {$link->link_name}
                            </a> 
                        </li>
HTML;
        }

        //最终输出
        $links_list_html = <<<HTML
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link disabled" href="#">友情链接: </a> 
                    </li>
					{$links_list_html}
                </ul>
	
HTML;

        set_cache_meta($meta_cache_key, '', $links_list_html);
    }

    return $links_list_html;
}


/**
 * 获取当前页面的类型
 * @return string 类型名
 */
function get_current_page_type()
{

    $output = 'unknown';
    //判断当前页面类型
    if (is_home())
    {
        $output = 'home';
    }
    else if (is_category())
    {
        $output = 'category';
    }
    else if (is_tag())
    {
        $output = 'tag';
    }
    else if (is_search())
    {
        $output = 'search';
    }
    else if (is_author())
    {
        $output = 'author';
    }
    else if (is_single())
    {
        $output = 'single';
    }
    else if (is_page())
    {
        $output = 'page';
    }

    return $output;
}


/**
 * 设置自定义查询参数
 * 用来限制用户可以用的查询参数
 *
 * @param arry $custom_query_vars
 */
function set_custom_query_vars($custom_query_vars)
{


    if ($custom_query_vars['paged'])
    {
        set_query_var('paged', $custom_query_vars['paged']);
    }

    //文章参数
    if ($custom_query_vars['ignore_sticky_posts'])
    {
        set_query_var('ignore_sticky_posts', $custom_query_vars['paged']);
    }
    if ($custom_query_vars['post_type'])
    {
        set_query_var('post_type', $custom_query_vars['post_type']);
    }
    if ($custom_query_vars['post_status'])
    {
        set_query_var('post_status', $custom_query_vars['post_status']);
    }
    if ($custom_query_vars['post__in'])
    {
        set_query_var('post__in', $custom_query_vars['post__in']);
    }
    if ($custom_query_vars['post__not_in'])
    {
        set_query_var('post__not_in', $custom_query_vars['post__not_in']);
    }

    //分类参数
    if ($custom_query_vars['cat'])
    {
        set_query_var('cat', $custom_query_vars['cat']);
    }
    if ($custom_query_vars['category_name'])
    {
        set_query_var('category_name', $custom_query_vars['category_name']);
    }

    //搜索参数
    if ($custom_query_vars['s'])
    {
        set_query_var('s', $custom_query_vars['s']);
    }
    //作者参数
    if ($custom_query_vars['author'])
    {
        set_query_var('author', $custom_query_vars['author']);
    }
    if ($custom_query_vars['author_name'])
    {
        set_query_var('author_name', $custom_query_vars['author_name']);
    }

    //标签参数
    if ($custom_query_vars['tag'])
    {
        set_query_var('tag', $custom_query_vars['tag']);
    }
    if ($custom_query_vars['tag_id'])
    {
        set_query_var('tag_id', $custom_query_vars['tag_id']);
    }


    //顺序 和时间参数

    if ($custom_query_vars['order'])
    {
        set_query_var('order', $custom_query_vars['order']);
    }
    if ($custom_query_vars['orderby'])
    {
        set_query_var('orderby', $custom_query_vars['orderby']);
    }
    if ($custom_query_vars['date_query'])
    {
        set_query_var('date_query', $custom_query_vars['date_query']);
    }

    //元数据参数
    if ($custom_query_vars['meta_key'])
    {
        set_query_var('meta_key', $custom_query_vars['meta_key']);
    }
    if ($custom_query_vars['meta_value'])
    {
        set_query_var('meta_value', $custom_query_vars['meta_value']);
    }
    if ($custom_query_vars['meta_value_num'])
    {
        set_query_var('meta_value_num', $custom_query_vars['meta_value_num']);
    }
    if ($custom_query_vars['meta_compare'])
    {
        set_query_var('meta_compare', $custom_query_vars['meta_compare']);
    }
    if ($custom_query_vars['meta_query'])
    {
        set_query_var('meta_query', $custom_query_vars['meta_query']);
    }

    //自定义参数 当前页面类型
    if ($custom_query_vars['page_type'])
    {
        set_query_var('page_type', $custom_query_vars['page_type']);
    }
}


/**
 * 添加自定义地址栏query变量
 * 这样wp query将会存储到自己的变量里
 *
 * @param array $query_vars
 *
 * @return mixed
 */
function add_custom_query_vars($query_vars)
{
    $query_vars[] = CUSTOM_ORDERBY;
    $query_vars[] = CUSTOM_ORDER_DATA_RANGE;
    $query_vars[] = AUTHOR_INTERNAL_SEARCH;

    return $query_vars;
}

add_filter('query_vars', 'add_custom_query_vars');


/**
 * 显示每个query的耗时
 */
function check_query_cost()
{
    if (current_user_can('level_10'))
    {
        echo "Made " . get_num_queries() . " queries in " . timer_stop(0) . " seconds";
        global $wpdb;
        echo "<pre style='position: relative;'>";
        for ($i = 0; $i < count($wpdb->queries); $i++)
        {
            if ($wpdb->queries[$i][1] > 0.01)
            {
                print_r($wpdb->queries[$i]);
            }
            //echo $wpdb->queries[$i][1] ;
        }
        echo "</pre>";
    }
}

/**
 * 把链接替换成https格式
 *
 * @param string $link
 *
 * @return string
 */
function convert_link_to_https($link)
{

    //链接不是空
    if ($link)
    {
        //统一https头部
        $link = str_replace("http://", "https://", $link);
        if (stripos($link, "//") === 0)
        {
            //添加头部
            $link = "https:" . $link;
        }
    }

    return $link;
}


/**
 *输出页面编辑链接
 */
function print_page_edit_link()
{
    global $post;

    $output = '';
    if (current_user_is_admin())
    {
        $post_type_object = get_post_type_object($post->post_type);
        $link = admin_url(sprintf($post_type_object->_edit_link . '&amp;action=edit', $post->ID));
        $output = '<a class="btn btn-secondary" href="' . $link . '">编辑页面</a>';
    }

    return $output;
}


/**
 * 转发文章到微博
 * @return mixed
 */
function share_to_sina()
{


    $args = [
        'post_type' => 'post',
        'post_status' => POST_STATUS_PUBLISH,
        'meta_query' => [
            [
                'key' => POST_SHARE_TO_WEIBO,
                'value' => '3',
                'compare' => '<',
                'type' => 'NUMERIC',
            ],
        ],
        'cat' => -ADULT_CATEGORY_MAIN_ID, // 排除魔法区文章
        'posts_per_page' => 1,
        'ignore_sticky_posts' => 1,

    ];
    //查询文章
    $result = get_posts($args);
    //如果结果不是空
    if ($result)
    {

        $post = $result[0];
        //获取文章id
        $post_id = $post->ID;
        //获取文章标题
        $post_title = $post->post_title;
        //获取文章内容
        $post_content = get_post_content_for_weibo($post_id);


        $appkey = '173298400';
        $username = 'hexie2108@sina.com';
        $userpassword = 'Wenjie2108@@';


        /* 获取文章标签关键词 */
        $tags = wp_get_post_tags($post_id);
        $keywords = '';
        foreach ($tags as $tag)
        {
            //拼接标签
            $keywords = $keywords . '#' . $tag->name . "# ";
        }

        //去除标签后的标题
        $title = strip_tags($post_title);
        //限制长度
        $title = mb_substr($title, 0, 32, 'utf-8');


        $top_image = get_images_full_size($post_id)[0];


        $body = [
            'title' => $title, //头条的标题
            'content' => $post_content, //头条的正文
            'cover' => $top_image, //头条的封面
            'summary' => $title, //头条的导语
            'text' => $title . '   ' . $keywords . '    全文地址: ' . get_permalink($post_id), //简介的内容
            'source' => $appkey
        ];

        $headers = ['Authorization' => 'Basic ' . base64_encode("$username:$userpassword")];

        //微博接口地址
        $api_url = 'https://api.weibo.com/proxy/article/publish.json';
        //发送请求
        $response = wp_remote_post($api_url, ['body' => $body, 'headers' => $headers]);

        $responseBody = wp_remote_retrieve_body($response);

        //如果内容获取正确
        if ($responseBody)
        {
            $msg = json_decode($responseBody);
            //如果状态码 正确, 说明转发成功,
            if ($msg->code == '100000')
            {
                //删除 meta标识
                delete_post_meta($post_id, POST_SHARE_TO_WEIBO);

                return true;
            }
            //如果状态码异常
            else
            {
                //更新失败计数器
                $count = get_post_meta($post_id, POST_SHARE_TO_WEIBO, true);
                $count++;
                update_post_meta($post_id, POST_SHARE_TO_WEIBO, $count);

                return $msg;
            }
        }
        else
        {
            return false;
        }
    }

    return 'all posts are shared';
}

/**
 * 输出weibo文章内容
 *
 * @param int $post_id
 *
 * @return string
 */
function get_post_content_for_weibo($post_id)
{

    $post = get_post($post_id);  //获取文章主体
    $post_title = $post->post_title; //获取文章标题

    $post_link = get_permalink($post_id); // 获取文章链接

    //以数组x数组的方式返回元数据 [ 'meta_key' => [meta_value] ]
    $metadata = get_post_meta($post_id);
    //获取图片地址数组
    $images_src = get_images_large_size($post_id);
    $images_full_src = get_images_full_size($post_id);


    //获取来源url变量
    $source_url = trim($metadata['source'][0]);
    //获取来源说明
    $source_text = trim($metadata['source_name'][0]);

    //bilibili视频地址
    $bilibili_video = trim($metadata['bilibili'][0]);

    $post_content_part = $post->post_content; //描述部分
    $source_part = ''; //来源部分
    $preview_images_part = ''; //图片预览部分
    $video_part = ''; //视频部分
    $download_part = ''; //下载部分

    //来源部分------------------------------------------------------------------------------------------

    //有来源地址
    if ($source_url)
    {
        //没有来源说明 就使用来源地址当做说明
        if (empty($source_text))
        {
            $source_text = $source_url;
        }
        $source_part = '<a href="' . $source_url . '"  target="_blank" rel="external nofollow">' . $source_text . '</a>';
    }
    //只有来源说明的情况
    else if ($source_text)
    {
        $source_part = $source_text;
    }

    //如果来源信息不是空的 添加前置词
    if ($source_part)
    {
        $source_part = '©来源:  ' . $source_part;
    }

    //预览图片部分------------------------------------------------------------------------------------------
    for ($i = 1; $i < count($images_src); $i++) //从第二张图开始 循环输出剩下的图片
    {
        $preview_images_part .= '<div class="preview-image m-1 ">
														<a href="' . $images_full_src[$i] . '" data-lightbox="images">
															<img class="preview img-fluid"  src="' . $images_src[$i] . '" alt="' . $post_title . '"  />
														</a>
												</div>';
    }
    //如果有预览图存在, 添加前置标题
    if ($preview_images_part)
    {
        $preview_images_part = '
		<h4>预览</h4>
		<div class=" py-2 py-md-0">'
            . $preview_images_part
            . '</div> ';
    }

    if ($bilibili_video)
    {
        $video_part = '
		<h4>在线播放</h4>
		<div class=" py-2 py-md-0">
		  <h2>
            <a class="btn btn-miku w-100 w-md-50" href="https://www.bilibili.com/video/' . $bilibili_video . '" target="_blank" rel="external nofollow"> 点击观看</a>
            </h2>
        </div>';
    }

    $download_part = '
	<h4>下载地址</h4>
		<div class=" py-2 py-md-0">
		    <h2>
		    	<a href="' . $post_link . '" target="_blank">
		    	点击查看下载地址
				</a>
            </h2>
        </div>';


    return <<<HTML

		<div class="first-image-part my-4">
		    <a href="{$images_full_src[0]}" data-lightbox="images">
                <img class="preview img-fluid"  src="{$images_src[0]}" alt="{$post_title}"  />
            </a>
		</div>
		<br/>
		<div class="source-part my-4">
			{$source_part}
		</div>
		<br/>
		<div class="content-part my-4">
			{$post_content_part}
		</div>
        <br/>
		<div class="preview-images-part my-4" id="preview-images-part">
			{$preview_images_part}
		</div>
        <br/>
        <div class="video-part my-4"">
			{$video_part}
		</div>
        <br/>
		<div  class="download-part my-4">
            {$download_part}
		</div>
        <br/>
		<div>
			<small>本帖内容来自于服务器的自动推送, 如果涉及侵权或禁转, 麻烦请通知我, 邮箱地址 hexie2109@gmail.com</small>
		</div>
		
HTML;
}


/**
 *通过B站API获取视频相关信息
 *
 * @param array $query_params
 * @param int $post_id
 *
 * @return array | WP_Error
 */
function get_bilibili_video_info($query_params, $post_id)
{


    //请求地址
    $url = 'https://api.bilibili.com/x/web-interface/view';

    $cache_key = POST_BILIBILI_VIDEO_INFO . '_' . $post_id;

    //尝试用缓存
    $result = get_cache_meta($cache_key, CACHE_GROUP_POST, EXPIRED_7_DAYS);
    if (empty($result))
    {

        //尝试从数据库查询
        $result = get_post_meta($post_id, POST_BILIBILI_VIDEO_INFO, true);

        //都没有 则重新远程请求
        if (empty($result))
        {

            //转换成url参数字符串
            $query_string = http_build_query($query_params);

            $response = wp_remote_get($url . '?' . $query_string);
            $body = wp_remote_retrieve_body($response);
            if (!$body)
            {
                return new WP_Error(500, __FUNCTION__ . ' : 请求失败');
            }

            $body = json_decode($body, true);
            //如果相关数据不存在
            if (!isset($body['data']) || !isset($body['data']['aid']) || !isset($body['data']['bvid']) || !isset($body['data']['pages']) || count($body['data']['pages']) == 0 || !isset($body['data']['pages'][0]['cid']) || !$body['data']['pages'][0]['cid'])
            {
                return new WP_Error(500, __FUNCTION__ . ' : 获取AID,BVID 和 CID失败');
            }

            $result = [
                'aid' => $body['data']['aid'],
                'bvid' => $body['data']['bvid'],
                'cid' => $body['data']['pages'][0]['cid'],
            ];

            //保存到数据库
            update_post_meta($post_id, POST_BILIBILI_VIDEO_INFO, $result);
        }

        //保存为内存缓存
        set_cache_meta($cache_key, CACHE_GROUP_POST, $result);
    }


    return $result;
}



/**
 * 通过API上传图片附件的时候触发
 *
 * @param WP_Post $attachment Inserted or updated attachment object.
 * @param WP_REST_Request $request Request object.
 */
function action_on_rest_after_insert_attachment($attachment, $request)
{

    //如果是上传更换新头像 的 动作
    if ($request->has_param(ACTION_UPDATE_AVATAR))
    {
        action_on_update_avatar($attachment->post_author, $attachment->ID);
    }
}

add_action('rest_after_insert_attachment', 'action_on_rest_after_insert_attachment', 10, 2);


/**
 * 获取随机顶部封面图地址
 * @return string
 */
function get_random_head_background_image()
{


    //	一年中的第几天 + 当前小时 然后 除余 图片数量 得出 随机数
    //$random_index = ( date( 'z' ) + date( 'G' ) ) % count( $array_image_link );
    //$random_index = rand(0 , count( $array_image_link ));

    //返回对应的图片地址
    //return $array_image_link[ $random_index ];

    $number = 79;
    //	一年中的第几天 + 当前小时 然后 除余 图片数量 得出 随机数
    $random_index = (date('z') + date('G')) % $number + 1;

    return 'https://' . CDN_MIKUCLUB_FUN . '/top/' . $random_index . '.webp';
}

/**
 * 输出分类单选框
 *
 * @return string 
 */
function print_categoria_radio_box()
{

    //获取当前分类id
    $current_cat = get_query_var('cat');
    //获取分类id列表
    $category_list = get_main_category_list();

    $category_options = [
        (object) [
            'term_id' => 0,
            'name' => '全部分区'
        ]
    ];


    foreach ($category_list as $category)
    {


        $main_name = $category->name;

        //更改主分类名称, 增加后缀
        //$category->name .= '-全部';

        //储存主分类
        $category_options[] = $category;


        //如果是成人分类 和 用户已登陆
        if ($category->term_id === ADULT_CATEGORY_MAIN_ID && is_user_logged_in())
        {
            //添加子分类数组
            foreach (get_main_category_children($category->term_id) as $sub_category)
            {
                //更改子分类名称, 增加上主分类的前缀
                $sub_category->name = $main_name . '-' . $sub_category->name;
                $category_options[] = $sub_category;
            }
        }
    }


    $category_output = '<div class="row g-3">';
    //输出分类
    foreach ($category_options as $category)
    {
        $id = $category->term_id;
        $name =  $category->name;
        $checked = (empty($id) || $id === $current_cat) ? 'checked' : '';


        $category_output .= <<<HTML
     <div class="col-auto">
            <input type="radio" class="cat btn-check" name="cat" id="cat-{$id}" autocomplete="off" value={$id} {$checked}>
            <label class="btn btn-outline-secondary" for="cat-{$id}">{$name}</label>
    </div>

HTML;
    }

    $category_output .= '</div>';

    return $category_output;
}





/**
 * 使用file1 替换默认图片域名
 *
 * @param array|string $image_src
 * @return array|string
 */
function fix_image_domain_with_file_1($image_src)
{
    $array_search = array_merge(ARRAY_SITE_DOMAIN, ARRAY_FILE_DOMAIN);

    $new_domain = FILE1_MIKUCLUB_FUN;

    $images_src = str_replace($array_search, $new_domain, $image_src);

    return $images_src;
}

/**
 * 使用file2 替换默认图片域名
 *
 * @param array|string $image_src
 * @return array|string
 */
function fix_image_domain_with_file_2($image_src)
{

    $array_search = array_merge(ARRAY_SITE_DOMAIN, ARRAY_FILE_DOMAIN);

    $new_domain = FILE2_MIKUCLUB_FUN;

    $images_src = str_replace($array_search, $new_domain, $image_src);

    return $images_src;
}

/**
 * 使用file3 替换默认图片域名
 *
 * @param array|string $image_src
 * @return array|string
 */
function fix_image_domain_with_file_3($image_src)
{

    $array_search = array_merge(ARRAY_SITE_DOMAIN, ARRAY_FILE_DOMAIN);

    $new_domain = FILE3_MIKUCLUB_FUN;

    $images_src = str_replace($array_search, $new_domain, $image_src);

    return $images_src;
}

/**
 * 使用file4 替换默认图片域名
 *
 * @param array|string $image_src
 * @return array|string
 */
function fix_image_domain_with_file_4($image_src)
{
    $array_search = array_merge(ARRAY_SITE_DOMAIN, ARRAY_FILE_DOMAIN);

    $new_domain = FILE4_MIKUCLUB_FUN;

    $images_src = str_replace($array_search, $new_domain, $image_src);

    return $images_src;
}

/**
 * 使用file5 替换默认图片域名
 *
 * @param array|string $image_src
 * @return array|string
 */
function fix_image_domain_with_file_5($image_src)
{
    $array_search = array_merge(ARRAY_SITE_DOMAIN, ARRAY_FILE_DOMAIN);

    $new_domain = FILE5_MIKUCLUB_FUN;

    $images_src = str_replace($array_search, $new_domain, $image_src);

    return $images_src;
}

/**
 * 使用file6 替换默认图片域名
 *
 * @param array|string $image_src
 * @return array|string
 */
function fix_image_domain_with_file_6($image_src)
{
    $array_search = array_merge(ARRAY_SITE_DOMAIN, ARRAY_FILE_DOMAIN);

    $new_domain = FILE5_MIKUCLUB_FUN;

    $images_src = str_replace($array_search, $new_domain, $image_src);

    return $images_src;
}

/**
 * 修正域名地址为主域名
 *
 * @param string $link
 * @return string
 */
function fix_site_domain_with_domain_main($link)
{
    $array_search = ARRAY_SITE_DOMAIN;

    $new_domain = SITE_DOMAIN_MAIN;

    $result = str_replace($array_search, $new_domain, $link);

    return $result;
}
