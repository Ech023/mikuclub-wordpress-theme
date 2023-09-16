<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Admin_Page;
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

        //如果有清空文件缓存的参数
        if (isset($_REQUEST['d_cache_system_delete']))
        {
            //清空缓存删除文件夹里的内容
            File_Cache::clean_all();
        }

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
 * 输出设置页面的内容
 *
 * @return void
 */
function print_theme_config_page()
{


    $alert = '';
    //如果存在saved变量 显示提示栏
    if (isset($_REQUEST['saved']))
    {
        $alert = '<div class="alert alert-success my-2">修改已保存</div>';
    }

    $page_title = Admin_Page::PAGE_TITLE;
    $tag_hr =  '<hr/>';


    $components = '';

    $components .= create_component('网站描述', 'text', Admin_Meta::SITE_DESCRIPTION);
    $components .= create_component('网站关键字', 'text', Admin_Meta::SITE_KEYWORDS);
    $components .= create_component('网页版顶部公告', 'textarea', Admin_Meta::SITE_ANNOUNCEMENT_TOP);
    $components .= create_component('网页版顶部公告下方折叠区域(QQ群)', 'textarea', Admin_Meta::SITE_ANNOUNCEMENT_TOP_COLLAPSE);
    $components .= create_component('网页版底部公告', 'textarea', Admin_Meta::SITE_ANNOUNCEMENT_BOTTOM);
    $components .= create_component('安卓客户端公告', 'textarea', Admin_Meta::APP_ANNOUNCEMENT);

    $components .= $tag_hr;

    $components .= create_component('', 'submit', '');

    $components .= $tag_hr;

    //添加删除文件缓存的选项
    $components .= create_check_box_component(
        '删除文件缓存系统',
        [
            'd_cache_system_delete'
        ],
        [
            '删除缓存'
        ]
    );

    $components .= $tag_hr;

    $components .= create_code_component('页面头部公共代码', Admin_Meta::SITE_TOP_CODE_ENABLE, Admin_Meta::SITE_TOP_CODE);

    $components .= create_code_component('页面底部公共代码', Admin_Meta::SITE_BOTTOM_CODE_ENABLE, Admin_Meta::SITE_BOTTOM_CODE);

    $components .= create_code_component('流量统计代码', Admin_Meta::SITE_BOTTOM_TRACK_CODE_ENABLE, Admin_Meta::SITE_BOTTOM_TRACK_CODE);

    $components .= $tag_hr;

    $components .= '<div class="col-12"><h2>广告</h2></div>';

    $components .= create_code_component('全站-主菜单下方广告位', Admin_Meta::SITE_TOP_ADSENSE_PC_ENABLE, Admin_Meta::SITE_TOP_ADSENSE_PC);
    $components .= create_code_component('手机版全站-主菜单下方广告位', Admin_Meta::SITE_TOP_ADSENSE_PHONE_ENABLE, Admin_Meta::SITE_TOP_ADSENSE_PHONE);

    $components .= $tag_hr;

    $components .= create_code_component('最新发布页+分类页+标签页-热门排行下方广告位 (PC+手机端)', Admin_Meta::CATEGORY_TOP_ADSENSE_ENABLE, Admin_Meta::CATEGORY_TOP_ADSENSE);

    $components .= $tag_hr;

    $components .= create_code_component('首页-主菜单下方广告位', Admin_Meta::HOME_TOP_ADSENSE_PC_ENABLE, Admin_Meta::HOME_TOP_ADSENSE_PC);
    $components .= create_code_component('手机版首页-主菜单下方广告位', Admin_Meta::HOME_TOP_ADSENSE_PHONE_ENABLE, Admin_Meta::HOME_TOP_ADSENSE_PHONE);

    $components .= $tag_hr;

    $components .= create_code_component('首页-幻灯片下方广告位', Admin_Meta::HOME_SLIDE_BOTTOM_ADSENSE_PC_ENABLE, Admin_Meta::HOME_SLIDE_BOTTOM_ADSENSE_PC);
    $components .= create_code_component('手机版首页-幻灯片下方广告位', Admin_Meta::HOME_SLIDE_BOTTOM_ADSENSE_PHONE_ENABLE, Admin_Meta::HOME_SLIDE_BOTTOM_ADSENSE_PHONE);

    $components .= $tag_hr;

    $components .= create_code_component('首页-最新发布上方广告位', Admin_Meta::HOME_RECENTLY_LIST_TOP_ADSENSE_PC_ENABLE, Admin_Meta::HOME_RECENTLY_LIST_TOP_ADSENSE_PC);
    $components .= create_code_component('手机版首页-最新发布上方广告位', Admin_Meta::HOME_RECENTLY_LIST_TOP_ADSENSE_PHONE_ENABLE, Admin_Meta::HOME_RECENTLY_LIST_TOP_ADSENSE_PHONE);

    $components .= $tag_hr;

    $components .= create_code_component('文章页-标题下方广告位', Admin_Meta::POST_TITLE_BOTTOM_ADSENSE_PC_ENABLE, Admin_Meta::POST_TITLE_BOTTOM_ADSENSE_PC);
    $components .= create_code_component('手机版文章页-标题下方广告位', Admin_Meta::POST_TITLE_BOTTOM_ADSENSE_PHONE_ENABLE, Admin_Meta::POST_TITLE_BOTTOM_ADSENSE_PHONE);

    $components .= $tag_hr;

    $components .= create_code_component('文章页-正文中间广告位', Admin_Meta::POST_CONTENT_ADSENSE_PC_ENABLE, Admin_Meta::POST_CONTENT_ADSENSE_PC);
    $components .= create_code_component('手机版文章页-正文中间广告位', Admin_Meta::POST_CONTENT_ADSENSE_PHONE_ENABLE, Admin_Meta::POST_CONTENT_ADSENSE_PHONE);

    $components .= $tag_hr;

    $components .= create_code_component('文章页-评论区上方广告位', Admin_Meta::POST_COMMENT_ADSENSE_PC_ENABLE, Admin_Meta::POST_COMMENT_ADSENSE_PC);
    $components .= create_code_component('手机版文章页-评论区上方广告位', Admin_Meta::POST_COMMENT_ADSENSE_PHONE_ENABLE, Admin_Meta::POST_COMMENT_ADSENSE_PHONE);

    $components .= $tag_hr;

    $components .= create_code_component('安卓客户端首页-幻灯片下方内容', Admin_Meta::APP_ADSENSE_ENABLE, Admin_Meta::APP_ADSENSE_TEXT);
    $components .= create_code_component('安卓客户端首页-幻灯片下方链接', Admin_Meta::APP_ADSENSE_ENABLE, Admin_Meta::APP_ADSENSE_LINK);

    $components .= $tag_hr;

    $components .= create_component('', 'submit', '');




    $output = <<<HTML

        <div class="container-fluid m-3 pe-5">

            {$alert}

            <h2 class="my-3">
                {$page_title}
            </h2>

            <form class="row gy-3" method="post">

                <input type="hidden" name="save" value="true" />

                {$components}

            </form>

        </div>

HTML;

    echo $output;
}



/**
 * 创建input text组件
 *
 * @param string $description
 * @param string $type 'text','number','textarea', 'submit' 
 * @param string $option
 * @return string
 */
function create_component($description, $type, $option)
{


    $value = get_theme_option($option);


    $input = '';
    if ($type === 'text')
    {

        $input = <<<HTML
            <input class="form-control" type="text" id="{$option}" name="{$option}" value="{$value}">
HTML;
    }
    else if ($type === 'number')
    {

        $input = <<<HTML
            <input class="form-control" type="number" id="{$option}" name="{$option}" value="{$value}">
HTML;
    }
    else if ($type === "textarea")
    {
        //打开输出缓存 来保存 wp editor输出的内容
        ob_start();
        wp_editor($value, $option, ['textarea_rows' => 5, 'media_buttons' => false, 'default_editor' => 'TinyMCE']);
        //关闭输出缓存
        $input = ob_get_clean();
    }
    else if ($type === "submit")
    {
        $input = <<<HTML
            <input class="btn btn-primary w-50" type="submit" value="保存设置">
HTML;
    }

    $output = <<<HTML

        <div class="col-3">
            {$description}
        </div>

        <div class="col-9">
            {$input}
        </div>

        <div class="m-0 w-100">
        </div>

HTML;

    return $output;
}


/**
 * 创建check box组件
 *
 * @param string $description
 * @param string[] $array_option
 * @param string[] $array_option_description
 * @return string
 */
function create_check_box_component($description, $array_option, $array_option_description)
{



    $input = '';
    for ($i = 0; $i < count($array_option); $i++)
    {


        $checked = get_theme_option($array_option[$i]) ? 'checked' : '';

        $input .= <<<HTML

        <div class="form-check form-check-inline">
            <input type="checkbox" class="form-check-input mt-1" id="{$array_option[$i]}" name="{$array_option[$i]}" {$checked}/>
            <label class="form-check-label" for="{$array_option[$i]}">{$array_option_description[$i]}</label>
        </div>

HTML;
    }

    $output = <<<HTML

        <div class="col-3">
            {$description}
        </div>

        <div class="col-9">
            {$input}
        </div>

        <div class="m-0 w-100">
        </div>

HTML;

    return $output;
}


/**
 * 创建代码组件
 *
 * @param string $description
 * @param string $check_option 开关的键名
 * @param string $option  数据的键名
 * @return string
 */
function create_code_component($description, $check_option, $option)
{

    //获取对应的开关状态
    $checked = get_theme_option($check_option) ? 'checked' : '';
    //获取内容
    $value = get_theme_option($option);


    $input = <<<HTML

        <div class="form-check">
            <input type="checkbox" class="form-check-input mt-1" id="{$check_option}" name="{$check_option}" {$checked}/>
            <label class="form-check-label" for="{$check_option}">开启</label>
        </div>
        <textarea class="form-control my-2" id="{$option}"  name="{$option}" type="textarea" rows="5">{$value}</textarea>

HTML;


    $output = <<<HTML

        <div class="col-3">
            {$description}
        </div>

        <div class="col-9">
            {$input}
        </div>

        <div class="m-0 w-100">
        </div>

HTML;

    return $output;
}
