<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Admin_Page;
use mikuclub\constant\Post_Meta;
use mikuclub\constant\Post_Status;
use mikuclub\constant\Post_Type;
use mikuclub\File_Cache;
use WP_Term;


/**
 * 在页面上输出和后台相关的HTML组件
 */



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

    $clean_cache_key = 'clean_cache_';

    //添加删除文件缓存的选项
    $components .= create_check_box_component(
        '缓存管理',
        [
            [
                'name' => $clean_cache_key,
                'text' => '删除所有缓存'
            ],
            [
                'name' => $clean_cache_key . File_Cache::DIR_COMPONENTS,
                'text' => '删除组件缓存',
            ],
            [
                'name' => $clean_cache_key . File_Cache::DIR_USER,
                'text' => '删除用户缓存',
            ],
            [
                'name' => $clean_cache_key . File_Cache::DIR_POST,
                'text' => '删除文章缓存',
            ],
            [
                'name' => $clean_cache_key . File_Cache::DIR_POSTS,
                'text' => '删除文章列表缓存',
            ],
            [
                'name' => $clean_cache_key . File_Cache::DIR_COMMENTS,
                'text' => '删除评论列表缓存',
            ],
            [
                'name' => $clean_cache_key . File_Cache::DIR_CATEGORY,
                'text' => '删除分类缓存',
            ],
            [
                'name' => $clean_cache_key . File_Cache::DIR_FORUM,
                'text' => '删除论坛缓存',
            ],
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
 * @param array<int, array<string, mixed>> $array_option
 * [
 *  'name' => string,
 *  'text' => string,
 * ]
 * @return string
 */
function create_check_box_component($description, $array_option)
{


    $input = '';

    foreach ($array_option as $option)
    {

        $name = $option['name'];
        $text = $option['text'];
        //$value = $option['value'] ? 'value=' . $option['value'] : '';

        $checked = get_theme_option($name) ? 'checked' : '';

        $input .= <<<HTML

        <div class="form-check form-check-inline">
            <input type="checkbox" class="form-check-input mt-1" id="{$name}" name="{$name}"  {$checked}/>
            <label class="form-check-label" for="{$name}">{$text}</label>
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



/**
 * 待审文章列表
 * @return void
 */
function pending_posts_dashboard_widget_component()
{

    $args = [
        'posts_per_page' => 20,
        'post_type'      => Post_Type::POST,
        'post_status'    => Post_Status::PENDING,
    ];

    $post_list = get_posts($args);

    //判断是否有待审文章
    if ($post_list)
    {

        $post_list_html = '';

        //储存文章编辑地址 数组
        $array_post_edit_link = [];

        foreach ($post_list as $post)
        {

            $my_post = new My_Post_Slim($post);
            //获取文章编辑地址
            //如果是普通文章
            if ($post->post_type == Post_Type::POST)
            {
                $edit_url = get_home_url() . '/edit?pid=' . $my_post->id;
            }
            //如果是其他类型  论坛主题, 回帖等
            else
            {
                $edit_url = $my_post->post_href;
            }
            //获取文章所属分类数组
            $array_category     = get_the_category($my_post->id);
            //把分类转换成 分类名称数组
            $array_cat_name = array_map(function (WP_Term $cat)
            {
                return $cat->name;
            }, $array_category);
            $cat_name_concatenated = implode(',', $array_cat_name);

            $post_list_html .= <<<HTML

				<div style="border: 1px solid #c3c4c7; padding: 5px; margin-top: 5px">
					<h4>
						<a href="{$edit_url}" target="_blank" style="text-decoration:none;">{$my_post->post_title}</a>
					</h4>
					<div>
						作者: <a href="{$my_post->post_author->user_href}" target="_blank" style="text-decoration:none;">{$my_post->post_author->display_name}</a> 分类:  {$cat_name_concatenated}
					</div>
					<div style="margin-top: 5px;">
						<span>投稿时间: {$my_post->post_date}</span>
						<span>更新时间: {$my_post->post_modified_date}</span>
					</div>
				</div>

HTML;
            //储存文章id
            $array_post_edit_link[] = $edit_url;
        }

        //转换为json格式
        $array_post_edit_link_json = json_encode($array_post_edit_link);

        $output = <<<HTML
            <div>
               
                <div style="text-align: right; margin-bottom: 15px;">
                    <a href="javascript:void(0)" onclick="open_all_post()">打开所有</a>
                </div>
                
                <div>{$post_list_html}</div>
                <script>
                function open_all_post(){
                    let array_post_edit_link = {$array_post_edit_link_json};
                    array_post_edit_link.forEach(function(edit_link){
                            setTimeout(function(){
                                window.open(edit_link,'_blank');
                            }, 500);
                    });

                }
                </script>
            </div>
HTML;
    }
    else
    {
        $output = '<div>目前没有待审文章</div>';
    }

    echo $output;
}

/**
 * 下载失效文章列表
 * @return void
 */
function fail_down_posts_dashboard_widget_component()
{

    $args = [
        'posts_per_page' => 10,
        'orderby'        => 'meta_value_num',
        'meta_key'       => Post_Meta::POST_FAIL_TIME
    ];

    $post_list = get_posts($args);

    //判断是否有失效文章
    if ($post_list)
    {

        $post_list_html = '';

        foreach ($post_list as $post)
        {

            $my_post = new My_Post_Slim($post);

            //获取文章所属分类数组
            $array_category     = get_the_category($my_post->id);
            //把分类转换成 分类名称数组
            $array_cat_name = array_map(function (WP_Term $cat)
            {
                return $cat->name;
            }, $array_category);
            $cat_name_concatenated = implode(',', $array_cat_name);

            //失效次数
            $fail_time = get_post_fail_times($post->ID);

            $post_list_html .= <<<HTML

                <div style="border: 1px solid #c3c4c7; padding: 5px; margin-top: 5px">
					<h4>
						<a href="{$my_post->post_href}" target="_blank" style="text-decoration:none;">
							{$my_post->post_title} <span class="badge bg-danger">{$fail_time}</span>
						</a>
					</h4>
					<div>
						作者: <a href="{$my_post->post_author->user_href}" target="_blank" style="text-decoration:none;">{$my_post->post_author->display_name}</a> 分类:  {$cat_name_concatenated}
					</div>
					<div>
						<span>投稿时间: {$my_post->post_date}</span>
						<span>更新时间: {$my_post->post_modified_date}</span>
					</div>
                </div>

HTML;
        }

        $fail_down_page_link = get_home_url() . '/fail_down_list';

        $output = <<<HTML

            <div>
                <h2 class="text-center">
                    <a href="{$fail_down_page_link}" target="_blank">进入失效管理页面</a>
                </h2>
                <div>{$post_list_html}</div>
            </div>

HTML;
    }
    else
    {

        $output = '<div>目前没有失效文章</div>';
    }

    echo $output;
}
