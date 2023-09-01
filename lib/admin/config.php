<?php

$theme_config_page_name = '初音社主题设置';
//选项数组
$options = [
    'd_description',
    'd_keywords',
    'd_tui',
    'd_tui_qq_collapse',
    'd_tui_bottom',
    "d_tui_android",
    'd_sticky_count',
    "d_autosave_b",
    "d_track_b",
    "d_track",
    "d_headcode_b",
    "d_headcode",
    "d_footcode_b",
    "d_footcode",
    "d_adsite_01_b",
    "d_adsite_01",
    "d_adindex_00_b",
    "d_adindex_00",
    "d_adindex_01_b",
    "d_adindex_01",
    "d_adindex_02_b",
    "d_adindex_02",
    "d_adindex_03_b",
    "d_adindex_03",
    "d_adpost_01_b",
    "d_adpost_01",
    "d_adpost_02_b",
    "d_adpost_02",
    "d_adpost_03_b",
    "d_adpost_03",
    "d_adpost_04_b",
    "d_adpost_04",
    "d_adpost_05_b",
    "d_adpost_05",
    "d_adcategory_01_b",
    "d_adcategory_01",
    "d_ajaxpager_b",
    "d_related_count",
    "d_post_views_b",
    "d_post_author_b",
    "d_post_comment_b",
    "d_post_time_b",
    "d_post_like_b",
    'd_singleMenu_b',
    "Mobiled_adindex_00_b",
    "Mobiled_adindex_00",
    "Mobiled_adindex_01_b",
    "Mobiled_adindex_01",
    "Mobiled_adindex_02_b",
    "Mobiled_adindex_02",
    "Mobiled_adindex_03_b",
    "Mobiled_adindex_03",
    'Mobiled_adpost_00_b',
    "Mobiled_adpost_00",
    'Mobiled_adpost_01_b',
    "Mobiled_adpost_01",
    "Mobiled_adpost_02_b",
    "Mobiled_adpost_02",
    "Mobiled_adpost_03_b",
    "Mobiled_adpost_03",
    "Mobiled_adpost_04_b",
    "Mobiled_adpost_04",
    "Mobiled_adpost_05_b",
    "Mobiled_adpost_05",
    "Mobiled_adcategory_01",
    "Mobiled_adcategory_01_b",
    "Mobiled_home_footer",
    "Mobiled_home_footer_b",
    "d_spamComments_b",
    'd_cache_system',
    'd_cache_system_home_time',
    'd_transient_cache_system',
    'app_adindex_01_b',
    'app_adindex_01_text',
    'app_adindex_01_link',
];

/**
 * 在后台主菜单里添加主题管理页面的链接
 * 处理保存请求
 * 重定向页面
 */
function add_theme_config_page()
{
    global $theme_config_page_name, $options;

    //当前文件名称
    $page_file_name = basename(__FILE__);

    //添加主题设置链接到主菜单里
    add_theme_page($theme_config_page_name, $theme_config_page_name, 'edit_themes', $page_file_name, 'print_theme_config_page');

    //如果当前页面是主题配置页
    if (isset($_REQUEST['page']) && $_REQUEST['page'] === $page_file_name)
    {
        //如果有保存变量
        if (isset($_REQUEST['save']))
        {
            //遍历所有设置key
            foreach ($options as $option)
            {
                //如果设置key存在于请求变量中
                if (array_key_exists($option, $_REQUEST))
                {
                    //更新对应设置
                    update_option($option, $_REQUEST[$option]);
                }
                //如果不存在, 重置为空
                else{
                    //更新对应设置
                    update_option($option, '');
                }
                
            }

            //如果有要求清空文件缓存
            if (isset($_REQUEST['d_cache_system_delete']))
            {
                //清空缓存删除文件夹里的内容
                delete_dir(CACHE_DIRECTORY);
            }

            //创建更新成功后的地址
            $url = 'admin.php?' . http_build_query([
                'page' => $page_file_name,
                'saved' => true,
            ]);

            //重定向到新地址
            header("Location: " . $url);
            exit;
        }
    }
}

add_action('admin_menu', 'add_theme_config_page');

/**
 * 在后台页面添加自定义CSS
 *
 * @return void
 */
function admin_custom_style()
{
    //当前文件名称
    $page_file_name = basename(__FILE__);

    //如果当前页面是主题配置页
    if (isset($_REQUEST['page']) && $_REQUEST['page'] === $page_file_name)
    {
        //添加bootstrap CSS
        wp_enqueue_style('theme-bootstrap', 'https://cdn.staticfile.org/bootstrap/5.1.3/css/bootstrap.min.css');
    }
}
add_action('admin_enqueue_scripts', 'admin_custom_style');


//清空文件夹函数和清空文件夹后删除空文件夹函数的处理
/**
 * 递归删除文件
 * @param $path
 */
function delete_dir($path)
{

    //如果结尾没有加分隔符
    if (substr($path, -1) != DIRECTORY_SEPARATOR)
    {
        $path .= DIRECTORY_SEPARATOR;
    }

    //如果是目录则继续
    if (is_dir($path))
    {

        //扫描一个文件夹内的所有文件夹和文件并返回数组
        $p = scandir($path);
        foreach ($p as $val)
        {
            //排除目录中的.和..
            if ($val != "." && $val != "..")
            {
                //如果是目录则递归子目录，继续操作
                if (is_dir($path . $val))
                {
                    //子目录中操作删除文件夹和文件
                    delete_dir($path . $val . '/');
                    //目录清空后删除空文件夹
                    rmdir($path . $val . '/');
                }
                else
                {
                    //如果是文件直接删除
                    unlink($path . $val);
                }
            }
        }
    }
}






/**
 * 输出设置页面的内容
 *
 * @return void
 */
function print_theme_config_page()
{
    global $theme_config_page_name;


    $alert = '';
    //如果存在saved变量 显示提示栏
    if (isset($_REQUEST['saved']))
    {
        $alert = '<div class="alert alert-success my-2">修改已保存</div>';
    }


    $components = '';

    $components .= create_component('网站描述', 'text', 'd_description');
    $components .= create_component('网站关键字', 'text', 'd_keywords');
    $components .= create_component('网页版顶部公告', 'textarea', 'd_tui');
    $components .= create_component('网页版顶部公告下方折叠区域(QQ群)', 'textarea', 'd_tui_qq_collapse');
    $components .= create_component('网页版底部公告', 'textarea', 'd_tui_bottom');
    $components .= create_component('安卓客户端公告', 'textarea', 'd_tui_android');

    $components .= '<hr/>';

    $components .= create_component('', 'submit', '');

    $components .= '<hr/>';

    $components .= create_check_box_component(
        '文件缓存系统',
        [
            'd_cache_system',
            'd_cache_system_delete'
        ],
        [
            '开启缓存',
            '删除缓存'
        ]
    );

    $components .= create_component('主页文件缓存有效时间 (默认10分钟)', 'number', 'd_cache_system_home_time');

    $components .= create_check_box_component(
        '内存缓存系统',
        [
            'd_transient_cache_system',
        ],
        [
            '开启缓存',
        ]
    );

    $components .= '<hr/>';

    $components .= create_component('幻灯片文章数量 (默认4个)', 'number', 'd_sticky_count');


    $components .= create_check_box_component(
        '使用Ajax加载下一页',
        [
            'd_ajaxpager_b',
        ],
        [
            '开启',
        ]
    );

    $components .= create_check_box_component(
        '文章页顶面包屑导航',
        [
            'd_singleMenu_b',
        ],
        [
            '开启',
        ]
    );

    $components .= create_check_box_component(
        '列表里的文章可选信息',
        [
            'd_post_views_b',
            'd_post_author_b',
            'd_post_comment_b',
            'd_post_time_b',
            'd_post_like_b',
        ],
        [
            '显示点击数',
            '显示作者',
            '显示评论数',
            '显示时间',
            '显示点赞',
        ]
    );

    $components .= create_component('文章页底部-相关文章数量 (默认8个)', 'number', 'd_related_count');

    $components .= create_check_box_component(
        '禁止纯日文和纯英文评论',
        [
            'd_spamComments_b',
        ],
        [
            '开启',
        ]
    );

    $components .= create_check_box_component(
        '移除文章自动保存和修订版本',
        [
            'd_autosave_b',
        ],
        [
            '开启',
        ]
    );

    $components .= '<hr/>';

    $components .= create_code_component('页面头部公共代码', 'd_headcode_b', 'd_headcode');

    $components .= create_code_component('页面底部公共代码', 'd_footcode_b', 'd_footcode');

    $components .= create_code_component('流量统计代码', 'd_track_b', 'd_track');

    $components .= '<hr/>';

    $components .= '<div class="col-12"><h2>广告</h2></div>';

    $components .= create_code_component('全站-主菜单下方广告位', 'd_adsite_01_b', 'd_adsite_01');
    $components .= create_code_component('最新发布页+分类页+标签页-排行榜下方广告位 (PC+手机端)', 'd_adindex_02_b', 'd_adindex_02');
    $components .= create_code_component('首页-主菜单下方广告位', 'd_adindex_00_b', 'd_adindex_00');
    $components .= create_code_component('首页-幻灯片下方广告位', 'd_adindex_01_b', 'd_adindex_01');
    $components .= create_code_component('首页-最新发布上方广告位', 'd_adindex_03_b', 'd_adindex_03');
    $components .= create_code_component('文章页-主菜单下方广告位', 'd_adpost_05_b', 'd_adpost_05');
    $components .= create_code_component('文章页-标题下方广告位', 'd_adpost_01_b', 'd_adpost_01');
    $components .= create_code_component('文章页-正文中间广告位', 'd_adpost_02_b', 'd_adpost_02');
    $components .= create_code_component('文章页-正文下方广告位', 'd_adpost_03_b', 'd_adpost_03');
    $components .= create_code_component('文章页-评论区上方广告位', 'd_adpost_04_b', 'd_adpost_04');
    $components .= create_code_component('分类页-主菜单下方广告位', 'd_adcategory_01_b', 'd_adcategory_01');
    $components .= create_code_component('手机版全站-主菜单下方广告位', 'Mobiled_adindex_01_b', 'Mobiled_adindex_01');
    $components .= create_code_component('手机版首页-最新发布上方广告位', 'Mobiled_adindex_03_b', 'Mobiled_adindex_03');
    $components .= create_code_component('手机版首页-主菜单下方广告位', 'Mobiled_adindex_00_b', 'Mobiled_adindex_00');
    $components .= create_code_component('手机版首页-幻灯片下方广告位', 'Mobiled_adindex_02_b', 'Mobiled_adindex_02');
    $components .= create_code_component('手机版文章页-主菜单下方广告位', 'Mobiled_adpost_05_b', 'Mobiled_adpost_05');
    $components .= create_code_component('手机版文章页-标题上方广告位', 'Mobiled_adpost_00_b', 'Mobiled_adpost_00');
    $components .= create_code_component('手机版文章页-标题下方广告位', 'Mobiled_adpost_01_b', 'Mobiled_adpost_01');
    $components .= create_code_component('手机版文章页-正文中间广告位', 'Mobiled_adpost_02_b', 'Mobiled_adpost_02');
    $components .= create_code_component('手机版文章页-正文下方广告位', 'Mobiled_adpost_03_b', 'Mobiled_adpost_03');
    $components .= create_code_component('手机版文章页-评论区上方广告位', 'Mobiled_adpost_04_b', 'Mobiled_adpost_04');
    $components .= create_code_component('手机版分类页-主菜单下方广告位', 'Mobiled_adcategory_01_b', 'Mobiled_adcategory_01');
    $components .= create_code_component('手机版首页-底部广告位', 'Mobiled_home_footer_b', 'Mobiled_home_footer');

    $components .= create_code_component('安卓客户端首页-幻灯片下方内容', 'app_adindex_01_b', 'app_adindex_01_text');
    $components .= create_code_component('安卓客户端首页-幻灯片下方链接', 'app_adindex_01_b', 'app_adindex_01_link');

    $components .= '<hr/>';

    $components .= create_component('', 'submit', '');


    $output = <<<HTML

<div class="container-fluid m-3 pe-5">

    {$alert}

    <h2 class="my-3">
        {$theme_config_page_name}
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


    $value = dopt($option);


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
        //使用缓存区来保存 wp editor输出的内容
        ob_start();
        wp_editor($value, $option, ['textarea_rows' => 5, 'media_buttons' => false, 'default_editor' => 'TinyMCE']);
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


        $checked = dopt($array_option[$i]) ? 'checked' : '';

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
    $checked = dopt($check_option) ? 'checked' : '';
    //获取内容
    $value = dopt($option);


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
