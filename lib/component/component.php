<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Category;
use mikuclub\constant\Expired;
use WP_Term;

/**
 * 在页面上输出HTML组件
 */


/**
 * 输出META描述
 * @return void
 */
function print_site_meta_description()
{

    global $s, $post;

    $description = '';

    if (is_single())
    {
        //获取缓存
        $sub_directory = File_Cache::DIR_POST . DIRECTORY_SEPARATOR . $post->ID;
        $description = File_Cache::get_cache_meta(File_Cache::POST_META_DESCRIPTION, $sub_directory, Expired::EXP_10_DAYS);

        if ($description === null)
        {
            //解义html实体
            //$post_content = html_entity_decode($post->post_content);
            $post_content = $post->post_content;

            //移除所有HTML标签
            $post_content = trim(strip_tags($post_content));

            //移除所有无关字符
            $description = preg_replace('/[^\x{4e00}-\x{9fa5}a-zA-Z0-9,.;:?！？，。；：\/]+/u', '', $post_content);
            //限制为150个字符
            $description = mb_substr($description, 0, 150, 'utf-8');

            File_Cache::set_cache_meta(File_Cache::POST_META_DESCRIPTION, $sub_directory, $description);
        }
    }
    else if (is_home())
    {
        $description = get_theme_option(Admin_Meta::SITE_DESCRIPTION);
    }
    else if (is_category())
    {
        $description = strip_tags(category_description());
    }
    else if (is_tag())
    {
        $description = single_tag_title('', false);
    }
    else if (is_author())
    {
        $description = get_the_author_meta('user_description');
    }
    else if (is_search())
    {
        $description = esc_html($s) . '的搜索結果';
    }
    else
    {
        $description = trim(wp_title('', false));
    }


    if ($description)
    {
        echo '<meta name="description" content="' . $description . '">';
    }
}

/**
 * 输出META关键词
 *
 * @return void
 */
function print_site_meta_keywords()
{
    global $s, $post;
    $keywords = '';

    if (is_single())
    {
        //如果不是魔法区 并且是登陆用户
        if (!is_adult_category())
        {
            //获取标签数组
            $post_tags = get_the_tags($post->ID);
            if (!is_array($post_tags))
            {
                $post_tags = [];
            }
            //获取分类数组
            $post_categories = get_the_category($post->ID);
            //提取 标签和分类的描述
            $array_term_name = array_map(function (WP_Term $term)
            {
                return $term->name;
            }, array_merge($post_tags, $post_categories));
            //转换成字符串
            $keywords = implode(',', $array_term_name);
        }
    }
    else if (is_home())
    {
        $keywords = get_theme_option(Admin_Meta::SITE_KEYWORDS);
    }
    else if (is_tag())
    {
        $keywords = single_tag_title('', false);
    }
    else if (is_category())
    {
        $keywords = single_cat_title('', false);
    }
    else if (is_search())
    {
        $keywords = esc_html($s);
    }
    else
    {
        $keywords = trim(wp_title('', false));
    }

    if ($keywords)
    {
        echo "<meta name=\"keywords\" content=\"$keywords\">\n";
    }
}


/**
 * 输出页面的编辑链接
 * @return string
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

