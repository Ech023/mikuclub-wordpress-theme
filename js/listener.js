/// <reference path="base.js" />
/// <reference path="class-comment.js" />
/// <reference path="class-message.js" />
/// <reference path="class-modal.js" />
/// <reference path="class-post.js" />
/// <reference path="class-toast.js" />
/// <reference path="class-ua-parser.js" />
/// <reference path="class-user.js" />
/// <reference path="function.js" />
/// <reference path="function-ajax.js" />

/***
 * jquery 监听器文件
 */

$(function () {

    const $body = $('body');


    //监听搜索表单 的提交事件
    $('form.site-search-form').on('submit', function (event) {
        event.preventDefault();
        sendSearch($(event.target));
    });
    //监听搜索表单里的分类按钮check点击事件
    $('form.site-search-form button.category').on('click', function (event) {

        //更新分类和子分类按钮组件的识别类名
        update_category_button_group_class($(this));

        //触发表单的提交事件
        $(this).closest('form.site-search-form').trigger('submit');
    });


    /**
     * 分页导航下一页按钮点击事件
     */
    $('.content .pagination-nav .btn.get-next-page').on('click', '', '', getNextPage);

    /**
     * 分页导航跳转按钮点击事件
     * 跳转页面
     */
    $('.change-page .change-page-button').on('click', '', '', changePagingPage);

    /**
     * 分页导航 页码表单变动事件
     *  限制码表单输入大小, 并根据内容激活或注销表单
     */
    $('.change-page .change-page-value').on('change', '', '', respectInputValueRange);


    /**
     * 文章列表自定义排序变更事件
     * 跳转页面更新排序
     */
    $('.post-list-order select').on('change', '', '', postListCustomOrder);




 

    /**
     * 私信模态窗中 发送按钮 点击事件
     * 发送私信
     */
    // $body.on('click', '.modal.private-message-modal button.send-private-message', '', sendPrivateMessage);


    /**
     * 弹窗消失时触发
     * 把弹窗从DOM中移除
     */
    $body.on('hidden.bs.toast', '.my-toast-system .toast', function () {
        $(this).remove();
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
    $(window).on('resize', '', '', actionOnBrowserSize);
    // 根据窗口大小触发的动作
    actionOnBrowserSize();

    //简体谷歌广告事件
    //隐藏空白广告
    setTimeout(hideEmptyAdSense, 6000);

    /**
     * 论坛页面显示 未读消息通知窗口
     */
    let $alertIconElement = $("#wpforo #wpforo-wrap .wpf-bar-right .wpf-alerts");
    if ($($alertIconElement).length) {
        let query = getQueryParameters();
        if (query.hasOwnProperty('show_notification')) {
            setTimeout(function () {
                $alertIconElement.trigger('click');
            }, 500);


        }

    }

    //滚动的时候 显示浮动菜单 并延时隐藏
    $(document).on('scroll', showSidebarMenuOnScroll);


    /**
     * 如果当前域名为EU
     * 替换所有A标签的href属性为副域名
     */
    update_a_href_to_secondary_domain();

    /**
     * 在页面加载完成后 更新页面中 img标签里的src地址 到 备用图片域名
     */
    update_image_src_of_element_to_backup_image_domain();

    /**
     * 根据local storage数值初始化 备用图床切换 按钮状态
     */
    init_backup_image_domain_button();

    /**
     * 监听 切换备用按钮的change事件
     */
    on_change_backup_image_domain_button();


});


/**
 * 把失效的域名跳转到当前的主域名
 */
redirect_site_domain_deactivated();




/*
 //如果当前域名为 cc
 if (location.host === SITE_DOMAIN.www_mikuclub_cc) {
    //重定向到online
    let url = location.href.replace(SITE_DOMAIN.www_mikuclub_cc, SITE_DOMAIN.www_mikuclub_online);
    location.replace(url);
}*/

/*
//如果当前域名为 online 或者 cc
if (location.host === SITE_DOMAIN.www_mikuclub_online) {
    //重定向到win
    let url = location.href.replace(SITE_DOMAIN.www_mikuclub_online, SITE_DOMAIN.www_mikuclub_win);
    location.replace(url);
}

if (location.host === SITE_DOMAIN.www_mikuclub_cc) {

    //重定向到win
    let url = location.href.replace(SITE_DOMAIN.www_mikuclub_cc, SITE_DOMAIN.www_mikuclub_win);
    location.replace(url);

    /*
    //一天内只判断一次
   const flag = getCookie('redirect_flag');

   if(!flag){

    //如果是0 就跳转
    const rand = Math.floor(Math.random() * 2);

    if(rand < 1){

        //重定向到win
        let url = location.href.replace(SITE_DOMAIN.www_mikuclub_cc, SITE_DOMAIN.www_mikuclub_win);
        location.replace(url);

         //设置cookie避免重复判断
         //setCookie('redirect_flag', 0, 1);
    }
    else{

        //设置cookie避免重复判断
        setCookie('redirect_flag', 1, 1);

    }
    
    

   }*/

/*
}*/

