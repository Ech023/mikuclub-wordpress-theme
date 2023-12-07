/// <reference path="common/base.js" />
/// <reference path="class/class-comment.js" />
/// <reference path="class/class-message.js" />
/// <reference path="class/class-modal.js" />
/// <reference path="class/class-post.js" />
/// <reference path="class/class-toast.js" />
/// <reference path="class/class-ua-parser.js" />
/// <reference path="class/class-user.js" />
/// <reference path="function.js" />
/// <reference path="function-ajax.js" />

/***
 * jquery 监听器文件
 */

$(function () {

    const $body = $('body');

    //监听顶部菜单 搜索栏的提交事件
    $('form.top_menu_bar_search_form').on('submit', function (event) {
        event.preventDefault();
        open_search_page($(this));
    });




    //监听搜索表单 的提交事件
    $('form.site-search-form').on('submit', function (event) {
        event.preventDefault();
        sendSearch($(this));
    });
    //监听搜索表单里的分类按钮check点击事件
    $('form.site-search-form button.category').on('click', function (event) {

        //更新分类和子分类按钮组件的识别类名
        update_category_button_group_class($(this));

        //触发表单的提交事件
        $(this).closest('form.site-search-form').trigger('submit');
    });











    //监听所有 关注按钮点击事件
    $body.on('click', 'button.add-user-follow-list', '', add_user_follow_list);

    //监听所有 取消关注按钮点击事件
    $body.on('click', 'button.delete-user-follow-list', '', delete_user_follow_list);


    //监听所有 添加黑名单按钮点击事件
    $body.on('click', 'a.add-user-black-list', '', function () {
        //添加黑名单
        const target_user_id = $(this).data('target-user-id');
        add_user_black_list(target_user_id);

    });

    //监听所有 移除黑名单按钮点击事件
    $body.on('click', 'a.delete-user-black-list', '', function () {
        //移除黑名单
        const target_user_id = $(this).data('target-user-id');
        delete_user_black_list(target_user_id);

    });



    //监听APP唤醒链接的点击事件
    $("a.app-link").on('click', '', '', invokeAppLink);

    //如果随机显示的元素存在
    if ($(".random-display").length) {
        randomDisplayElement();
    }

    //监听屏幕大小变化
    // $(window).on('resize', '', '', actionOnBrowserSize);
    // 根据窗口大小触发的动作
    // actionOnBrowserSize();

    //简体谷歌广告事件
    //隐藏空白广告
    setTimeout(hideEmptyAdSense, 6000);

    /**
     * 论坛页面显示 未读消息通知窗口
     */

    show_wpforo_notification_alert();




    /**
     * 如果当前域名为EU
     * 替换所有A标签的href属性为副域名
     */
    update_a_href_to_secondary_domain();

    /**
     * 在页面加载完成后 更新页面中 img标签里的src地址 到 备用图片域名
     */
    update_image_src_of_element_to_backup_image_domain();





});


/**
 * 把失效的域名跳转到当前的主域名
 */
redirect_site_domain_deactivated();




