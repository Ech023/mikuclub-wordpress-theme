<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Admin_Page;
use mikuclub\constant\Config;
use mikuclub\constant\Site_Menu;
use mikuclub\User_Capability;


/**
 * 主题设置页的 相关的函数
 */




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
        File_Cache::DIR_WP_REST_POSTS,
        File_Cache::DIR_WP_REST_COMMENTS,
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
        wp_enqueue_style('twitter-bootstrap-css', 'https://cdn.staticfile.org/twitter-bootstrap/5.3.2/css/bootstrap.min.css');
        // wp_enqueue_style('twitter-bootstrap-css', 'https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/5.3.1/css/bootstrap.min.css', [], false);
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
            Site_Menu::MAIN_MENU => __(Site_Menu::get_description(Site_Menu::MAIN_MENU)),
            Site_Menu::LEFT_SIDE_MENU => __(Site_Menu::get_description(Site_Menu::LEFT_SIDE_MENU)),
            // Site_Menu::PAGE_MENU => __(Site_Menu::get_description(Site_Menu::PAGE_MENU)),
            Site_Menu::TOP_LEFT_MENU => __(Site_Menu::get_description(Site_Menu::TOP_LEFT_MENU)),
            Site_Menu::BOTTOM_MENU => __(Site_Menu::get_description(Site_Menu::BOTTOM_MENU)),
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

/**
 * 自定义用户个人资料信息
 *
 * @param string[] $contact_methods
 * @return string[]
 */
function user_custom_contact_fields($contact_methods)
{
    //取消不必要的用户
    unset($contact_methods['yim']);
    unset($contact_methods['aim']);
    unset($contact_methods['jabber']);
    unset($contact_methods['twitter']);

    $contact_methods['qq']         = 'QQ';
    $contact_methods['sina_weibo'] = '新浪微博';

    return $contact_methods;
}


/**
 * 移除普通用户在后台的菜单选项
 * 
 * @return void
 */
function remove_menus()
{

    if (!User_Capability::is_admin())
    {
        remove_menu_page('index.php'); //仪表盘
        remove_menu_page('upload.php'); //多媒体
        remove_menu_page('edit.php'); //文章
        remove_menu_page('post-new.php'); //新建文章
        remove_menu_page('media-new.php'); //新建多媒体
        remove_menu_page('edit-comments.php'); //评论
        remove_menu_page('tools.php'); //工具
    }
}


/**
 * 禁止普通用户进入后台特定页面
 * 
 * @return void
 */
function prevent_user_access_wp_admin()
{
    //检测用户是否有管理权限
    if (!User_Capability::is_admin())
    {
        $address = get_home_url();
        wp_redirect($address);
        exit;
    }
}
