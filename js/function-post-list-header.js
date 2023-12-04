/// <reference path="common/base.js" />
/// <reference path="common/constant.js" /> 

let $post_list_orderby_element;
let $post_list_sub_orderby_element;
let $post_list_download_type_element;



$(function () {

    $post_list_orderby_element = $('.post_list_orderby');
    $post_list_sub_orderby_element = $('.post_list_sub_orderby');
    $post_list_download_type_element = $('.post_list_download_type');

    //绑定 排序组按钮的点击事件
    $post_list_orderby_element.find('.btn.orderby_group').on('click', function () {
        on_click_button_orderby_group($(this));
    })

    //绑定 子排序按钮的点击事件
    $post_list_sub_orderby_element.find('.btn.sub_orderby').on('click', function () {
        on_click_button_sub_orderby($(this));
    })

    //绑定 下载过滤按钮的点击事件
    $post_list_download_type_element.find('.btn.download_type').on('click', function () {
        on_click_button_download_type($(this));
    })

});

/**
 * 排序组按钮被点击时触发
 * @param {jQuery} $button 
 */
function on_click_button_orderby_group($button) {

    const orderby_group = $button.data('orderby-group');

    const active_class = 'btn-secondary active';
    const no_active_class = 'btn-light bg-gray-half';

    //切换所有排序组的按钮class类名, 移除选中状态
    $post_list_orderby_element.find('.btn.orderby_group').removeClass(active_class).addClass(no_active_class);
    //只给触发点击事件的 排序组按钮 添加激活类名
    $button.removeClass(no_active_class).addClass(active_class);


    //隐藏所有子排序容器
    $post_list_sub_orderby_element.find('.sub_orderby_container').hide();

    //只显示所属的子排序容器
    $post_list_sub_orderby_element.find('.sub_orderby_container.' + orderby_group).show();

    //选中子排序容器里的第一个按钮
    const $first_sub_orderby_button = $post_list_sub_orderby_element.find('.sub_orderby_container.' + orderby_group + ' .btn.sub_orderby').first();

    //触发子排序里第一个按钮的点击事件
    on_click_button_sub_orderby($first_sub_orderby_button);
}

/**
 * 子排序按钮被点击时触发
 * @param {*} $button 
 */
function on_click_button_sub_orderby($button) {


    const active_class = 'btn-secondary active';
    const no_active_class = 'btn-light bg-gray-half';

    //切换所有排序组的按钮class类名, 移除选中状态
    $post_list_sub_orderby_element.find('.btn.sub_orderby').removeClass(active_class).addClass(no_active_class);
    //只给触发点击事件的 排序组按钮 添加激活类名
    $button.removeClass(no_active_class).addClass(active_class);


    const local_data = $button.data('parameters');

    //更新列表的请求参数+重新请求列表
    const $post_list_container_component = $('.post-list-container');
    const data = $post_list_container_component.data('parameters');
    Object.assign(data, local_data);
    $post_list_container_component.data('parameters', data);
    get_post_list(true);

}

/**
 * 下载过滤按钮点击时触发
 * @param {*} $button 
 */
function on_click_button_download_type($button) {



    const active_class = 'btn-secondary active';
    const no_active_class = 'btn-light bg-gray-half';

    //切换所有排序组的按钮class类名, 移除选中状态
    $post_list_download_type_element.find('.btn.download_type').removeClass(active_class).addClass(no_active_class);
    //只给触发点击事件的 排序组按钮 添加激活类名
    $button.removeClass(no_active_class).addClass(active_class);


    const local_data = $button.data('parameters');

    //更新列表的请求参数+重新请求列表
    const $post_list_container_component = $('.post-list-container');
    const data = $post_list_container_component.data('parameters');
    Object.assign(data, local_data);
    $post_list_container_component.data('parameters', data);
    get_post_list(true);
}