<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Admin_Page;
use mikuclub\constant\Config;
use mikuclub\constant\User_Capability;


/**
 * 主题设置页的 相关的函数
 */

//待删除的元数据数组
$options = [

    //'d_ajaxpager_b',
    //'d_related_count',
    //'d_post_views_b',
    //'d_post_author_b',
    //'d_post_comment_b',
    //'d_post_time_b',
    //'d_post_like_b',
    //'d_singleMenu_b',
    //'d_sticky_count',
    //'d_autosave_b',

    //'Mobiled_home_footer',
    //'Mobiled_home_footer_b',
    //'d_spamComments_b',
    //'d_cache_system',
    //'d_cache_system_home_time',
    //'d_transient_cache_system',

    //'d_adcategory_01_b',
    //'d_adcategory_01',

    // 'd_adpost_04_b',
    // 'd_adpost_04',
    // 'd_adpost_05_b',
    // 'd_adpost_05',

    //'Mobiled_adindex_02_b',
    //'Mobiled_adindex_02',

    //'Mobiled_adcategory_01',
    //'Mobiled_adcategory_01_b',

    // 'Mobiled_adpost_00_b',
    // 'Mobiled_adpost_00',

    // 'Mobiled_adpost_04_b',
    // 'Mobiled_adpost_04',
    // 'Mobiled_adpost_05_b',
    // 'Mobiled_adpost_05',



];


/**
 * 在后台主菜单里添加主题管理页面的链接
 * 处理保存请求
 * 重定向页面
 * @return void
 */
function add_theme_config_page()
{
    //如果当前请求参数里包含主题设置页 和 更新参数
    if (isset($_REQUEST['page']) && $_REQUEST['page'] === Admin_Page::PAGE_PATH_NAME && isset($_REQUEST['save']))
    {
        //更新主题设置
        update_theme_config();

        //删除缓存文件
        delete_theme_cache();

        //创建更新成功后的跳转地址
        $url = 'admin.php?' . http_build_query([
            'page' => Admin_Page::PAGE_PATH_NAME,
            'saved' => true,
        ]);

        //重定向到新地址
        // header("Location: " . $url);
        wp_redirect($url);
        exit;
    }
    //否则 正常加载主题管理页
    else
    {
        //添加主题页到后台面板里
        add_theme_page(Admin_Page::PAGE_TITLE, Admin_Page::PAGE_TITLE, User_capability::EDIT_THEMES, Admin_Page::PAGE_PATH_NAME, 'mikuclub\print_theme_config_page');
    }
}

/**
 * 根据请求参数删除对应缓存文件
 *
 * @return void
 */
function delete_theme_cache()
{

    $clean_cache_key = 'clean_cache_';
    //缓存类型
    $array_type_cache = [
        '',
        File_Cache::DIR_COMPONENTS,
        File_Cache::DIR_USER,
        File_Cache::DIR_POST,
        File_Cache::DIR_POSTS,
        File_Cache::DIR_COMMENTS,
        File_Cache::DIR_CATEGORY,
        File_Cache::DIR_FORUM,
    ];
    //遍历每个缓存类型
    foreach ($array_type_cache as $type)
    {
        //如果有要求删除
        if (isset($_REQUEST[$clean_cache_key . $type]))
        {
            //清空对应缓存
            File_Cache::delete_directory($type);
        }
    }
}

/**
 * 更新主题设置
 * 并且重新进行跳转
 *
 * @return void
 */
function update_theme_config()
{
    //遍历所有设置key
    foreach (Admin_Meta::get_to_array() as $option)
    {
        //如果不存在对应的请求参数 就重置为空字符串
        $value = $_REQUEST[$option] ?? '';

        //更新option数据
        update_option($option, $value);
    }
}

/**
 * 获取主题相关的元数据 (如果数值是字符串 额外进行反引用处理)
 *
 * @param string $option_name 键名
 * @return string|bool 键值, 如果未找到则返回false
 */
function get_theme_option($option_name)
{

    $result = get_option($option_name);
    //如果键值 是 字符串 进行额外反引用处理
    if (is_string($result))
    {
        $result = stripslashes($result);
    }

    return $result;
}

/**
 * 在后台页面添加自定义CSS
 *
 * @return void
 */
function admin_custom_style()
{
    //如果当前请求参数里包含 主题设置页
    if (isset($_REQUEST['page']) && $_REQUEST['page'] === Admin_Page::PAGE_PATH_NAME)
    {
        //添加bootstrap CSS
        wp_enqueue_style('theme-bootstrap', 'https://cdn.staticfile.org/bootstrap/5.1.3/css/bootstrap.min.css');
    }
}



/**
 * 添加主题可用的菜单
 * 
 * @return void
 */
function add_theme_nav_menus()
{

    //注册网站的菜单
    if (function_exists('register_nav_menus'))
    {
        register_nav_menus([
            'nav'           => __('网站导航'),
            'pagemenu'      => __('页面导航'),
            'top_left_menu' => __('顶部左菜单'),
            'bottom_menu'   => __('底部菜单'),
        ]);
    }
}






/**
 *  后台自定义CSS和JS
 * @return void
 */
function custom_admin_script()
{
    wp_enqueue_style('custom-admin-css', get_template_directory_uri() . '/css/style-admin.css', [], Config::CSS_JS_VERSION);
}



/**
 * 在仪表盘主页添加自定义部件
 * 
 * @return void
 */
function custom_dashboard_widgets()
{

    //必须是管理员 才能看到
    if (User_Capability::is_admin())
    {
        add_meta_box('pending_posts_dashboard_widget', '待审文章', 'mikuclub\pending_posts_dashboard_widget_component', 'dashboard', 'normal', 'core');
        add_meta_box('fail_down_posts_dashboard_widget', '失效文章', 'mikuclub\fail_down_posts_dashboard_widget_component', 'dashboard', 'normal', 'core');
    }
}



