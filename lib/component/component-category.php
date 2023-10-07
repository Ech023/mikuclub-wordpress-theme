<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Category;
use mikuclub\constant\Expired;
use mikuclub\Post_Query;
use WP_Term;

/**
 * 在页面上输出分类相关的HTML组件
 */

// /**
//  * 输出分类和子分类 组件
//  *
//  * @return string 
//  */
// function print_category_and_sub_category_component()
// {
//     $category_and_sub_category = print_category_button_component();
//     $category_and_sub_category .=  print_sub_category_button_component();

//     $output = <<<HTML
//         <div>
//             {$category_and_sub_category}
//         </div>
// HTML;

//     return $output;
// }

/**
 * 输出分类按钮 组件
 *
 * @return string 
 */
function print_category_button_component()
{

    //从WP query中 获取当前分类id
    $current_cat_id = intval(get_query_var(Post_Query::CUSTOM_MAIN_CAT, 0));
    $current_sub_cat_id = intval(get_query_var(Post_Query::CUSTOM_SUB_CAT, 0));

    $parent_cat_id = get_parent_category_id($current_sub_cat_id);

    //获取分类id列表
    $array_category = get_main_category_list();


    //创建伪分类
    $total_category = new My_Category_Model();
    $total_category->term_id = -1;
    $total_category->name = '全部分区';

    array_unshift($array_category, $total_category);

    $array_category_html = '';


    //遍历分类
    foreach ($array_category as $category)
    {
        //如果当前分类已被选中
        $btn_class = 'btn-outline-secondary';
        //如果存在主分类ID
        if ($category->term_id === $current_cat_id)
        {
            $btn_class = 'selected btn-secondary';
        }
        //否则如果子分类的父分类ID存在
        else if ($category->term_id === $parent_cat_id)
        {
            $btn_class = 'btn-secondary';
        }
        //如果没有任何参数
        else if ($category->term_id === -1 && $current_cat_id === 0 && $parent_cat_id === 0)
        {
            $btn_class = 'btn-secondary';
        }

        $array_category_html .= <<<HTML

            <div class="col-auto">
                <button class="category main_cat btn {$btn_class}" data-main_cat={$category->term_id}>
                    {$category->name}
                </button>
            </div>
HTML;
    }


    $output = <<<HTML
        <div class="array_cat_button_group row g-3 my-0">
            $array_category_html
        </div>

HTML;

    return $output;
}

/**
 * 输出子分类按钮 组件
 *
 * @return string 
 */
function print_sub_category_button_component()
{

    $output = '';

    //从WP query中 获取当前分类id
    $current_cat_id = intval(get_query_var(Post_Query::CUSTOM_MAIN_CAT, 0));
    $current_sub_cat_id = intval(get_query_var(Post_Query::CUSTOM_SUB_CAT, 0));

    //如果不存在 就读取子分类的父类ID
    if (empty($current_cat_id))
    {
        $current_cat_id = get_parent_category_id($current_sub_cat_id);
    }


    //获取分类id列表
    $array_category = get_sub_category_list($current_cat_id);

    if (count($array_category) > 0)
    {

        //创建伪分类
        $total_category = new My_Category_Model();
        $total_category->term_id = -1;
        $total_category->name = '全部子分区';

        array_unshift($array_category, $total_category);

        $array_category_html = '';

        //遍历分类
        foreach ($array_category as $category)
        {
            //如果当前分类已被选中
            $btn_class = 'btn-outline-secondary';
            if ($category->term_id === $current_sub_cat_id)
            {
                $btn_class = 'selected btn-secondary';
            }
            //如果没有任何参数
            else if ($category->term_id === -1 && $current_sub_cat_id === 0)
            {
                $btn_class = 'btn-secondary';
            }


            $array_category_html .= <<<HTML

           <div class="col-auto">
               <button class="category sub_cat btn {$btn_class}" data-sub_cat={$category->term_id}>
                   {$category->name}
               </button>
           </div>
HTML;
        }

        $output = <<<HTML

            <div class="array_sub_cat_group row my-0 g-3">
                $array_category_html
            </div>

HTML;
    }


    return $output;
}
