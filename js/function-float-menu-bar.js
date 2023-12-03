/// <reference path="common/base.js" />
/// <reference path="common/constant.js" />
/// <reference path="function-ajax.js" />
/// <reference path="class/class-comment.js" />
/// <reference path="class/class-message.js" />
/// <reference path="class/class-modal.js" />
/// <reference path="class/class-post.js" />
/// <reference path="class/class-toast.js" />
/// <reference path="class/class-ua-parser.js" />
/// <reference path="class/class-user.js" />


const DARK_THEME_KEY = 'dark_theme';

/**
 * 和浮动菜单相关的函数
 * 
 */

$(function () {

    //滑动到顶部按钮
    const $float_bottom_menu_bar = $('.float_bottom_menu_bar');
    $float_bottom_menu_bar.find('.go_top').on('click', function () {
        scroll_to_element(null, $('#header'));
    })

    //滑动到下载按钮
    $float_bottom_menu_bar.find('.go_download_port').on('click', function () {
        scroll_to_element(null, $('#download-part'));
    })

    //滑动到评论区按钮
    $float_bottom_menu_bar.find('.go_comments_part').on('click', function () {
        scroll_to_element(null, $('#comments-part'));
    })

    //暗夜模式切换按钮的点击事件
    $float_bottom_menu_bar.find('.enable_dark_theme').on('click', function () {
        on_click_button_darkmode();
    });

    //如果没有下载地址 隐藏对应的浮动按钮
    if (!$('.article-content .download-part').children().length) {
        $float_bottom_menu_bar.find('.go_download_port').hide();
    }


    //切换图床按钮的点击事件
    $float_bottom_menu_bar.find('.enable_backup_image_domain').on('click', function () {
        on_click_backup_image_domain_button();
    });


    //更新外观按钮样式
    update_button_dark_theme();


    /**
     * 根据local storage数值初始化 备用图床切换 按钮状态
     */
    update_backup_image_domain_button();
});



/**
 * 初始化暗夜模式配置
 */
function init_dark_theme() {


    const now = new Date();


    /*init_dark_theme.darkmode = new Darkmode({
        mixColor: '#e7e7e7',
        //time: '0.5s', // default: '0.3s'
        saveInCookies: false, // default: true,   
        autoMatchOsTheme: true, // default: true
    });*/
    init_dark_theme.is_dark_mode_activated = false;

    //初始化 暗夜模式设置 ( 0 = 自动, 1 = 关闭, 2 = 启动)
    init_dark_theme.dark_mode_setting = 0;

    //获取设置记录
    let dark_mode = getLocalStorageForDarkMode(DARK_THEME_KEY);
    if (dark_mode && dark_mode.expiry > now.getTime()) {
        //更新 暗夜模式的用户自定义设置
        init_dark_theme.dark_mode_setting = dark_mode.value;
    }

    update_dark_theme();


}

/**
 * 更新网页主体的颜色
 */
function update_dark_theme() {

    const mode = init_dark_theme.dark_mode_setting || 0;

    const now = new Date();
    const hour = now.getHours();

    //暗夜模式开启时间
    const start_hour = 18;
    //暗夜模式结束时间 
    const end_hour = 6;


    //如果设置记录 为 关
    if (mode === 1) {

        //移除夜间模式css类
        $('body').removeClass('darkmode');
        //移除bootstrap暗夜属性
        $('html').removeAttr('data-bs-theme');
    }
    //如果设置记录 为 开
    if (mode === 2) {

        //添加夜间模式css类
        $('body').addClass('darkmode');
        //添加bootstrap暗夜属性
        $('html').attr('data-bs-theme', 'dark');
    }
    //如果没有设置为自动模式 + 时间为 夜间 范围
    else if (mode === 0) {

        if (hour >= start_hour || hour <= end_hour) {

            //添加夜间模式css类
            $('body').addClass('darkmode');
            //添加bootstrap暗夜属性
            $('html').attr('data-bs-theme', 'dark');

        }
        else {
            //移除夜间模式css类
            $('body').removeClass('darkmode');
            //移除bootstrap暗夜属性
            $('html').removeAttr('data-bs-theme');
        }

    }


}

/**
 * 更新 浮动菜单栏里 日夜主题按钮
 */
function update_button_dark_theme() {

    const $float_bottom_menu_bar = $('.float_bottom_menu_bar');
    const mode = init_dark_theme.dark_mode_setting || 0;

    const $no_theme_button = $float_bottom_menu_bar.find('.enable_dark_theme.no_theme');
    const $light_theme_button = $float_bottom_menu_bar.find('.enable_dark_theme.light_theme');
    const $dark_theme_button = $float_bottom_menu_bar.find('.enable_dark_theme.dark_theme');

    if (mode === 0) {

        $no_theme_button.show();
        $light_theme_button.hide();
        $dark_theme_button.hide();
    }
    else if (mode === 1) {
        $no_theme_button.hide();
        $light_theme_button.show();
        $dark_theme_button.hide();
    }
    else if (mode === 2) {
        $no_theme_button.hide();
        $light_theme_button.hide();
        $dark_theme_button.show();
    }
}

/**
 * 切换主题按钮点击事件
 * 保存和切换主题
 */
function on_click_button_darkmode() {

    const now = new Date();
    const expiry_time = 1000 * 60 * 60 * 24 * 7 // 7天 的 毫秒数

    let new_setting;
    let confirm_text;
    switch (init_dark_theme.dark_mode_setting) {
        case 0:
            new_setting = 1;
            confirm_text = '是否要开启白色主题模式?';
            break;
        case 1:
            new_setting = 2;
            confirm_text = '是否要开启夜间主题模式?';
            break;
        case 2:
            new_setting = 0;
            confirm_text = '是否要开启自动模式 (将会根据时间自动切换白色和夜间主题)?';
            break;
    }

    open_confirm_modal(
        confirm_text,
        //确认
        () => {

            //设置新的暗夜模式配置
            init_dark_theme.dark_mode_setting = new_setting;

            //保存为一天后过期的本地储存
            setLocalStorageForDarkMode(DARK_THEME_KEY, {
                value: init_dark_theme.dark_mode_setting,
                expiry: now.getTime() + expiry_time,
            });

            update_dark_theme();
            update_button_dark_theme();

            //创建通知弹窗
            MyToast.show_success('主题更改成功');


        }
    );


}



/**
 *从本地存储获取数据 暗夜模式专用 (因为需要在页面加载完毕前运行)
 * @param {string} key
 * @returns {any}
 */
function getLocalStorageForDarkMode(key) {

    let value = '';
    //只有在支持localStorage的情况
    if (window.localStorage) {
        //获取本地储存
        value = window.localStorage.getItem(key);
        //确保有内容
        if (value) {
            //从json格式解义
            value = JSON.parse(value);
        }
    }

    return value;


}

/**
 *设置数据到本地储存 暗夜模式专用 (因为需要在页面加载完毕前运行)
 * 错误的情况返回false
 * @param {string} key
 * @param {any} value
 * @return {boolean}
 **/
function setLocalStorageForDarkMode(key, value) {

    let result = false;
    //只有在支持localStorage的情况 并且键名和键值不是空
    if (window.localStorage && key && value) {
        //键值转换成json格式
        value = JSON.stringify(value);
        //设置本地储存
        window.localStorage.setItem(key, value);
        result = true;
    }

    return result;

}



/**
 * 根据local storage数值初始化 切换 备用图床域名 按钮状态
 */
function update_backup_image_domain_button() {

    const $float_bottom_menu_bar = $('.float_bottom_menu_bar');

    const value = getLocalStorage(LOCAL_STORAGE_KEY.enableBackupImageDomain);

    //如果数值存在 而且是 true
    if (value === true) {

        const $backup_image_domain_button = $float_bottom_menu_bar.find('.enable_backup_image_domain');
        $backup_image_domain_button.addClass('text-miku');

        //勾选按钮
        // $('input#enable_backup_image_domain').prop('checked', true);
        //隐藏按钮部分说明
        //$('span.enable_backup_image_domain_title').hide();
    }

}

/**
 * 检查 备用图床域名 是否开启
 * @returns {boolean}
 */
function is_enable_backup_image_domain() {
    const value = getLocalStorage(LOCAL_STORAGE_KEY.enableBackupImageDomain);
    return value ? true : false;
}

/**
 * 在页面加载完成后 监听切换 备用图床域名 按钮的change事件
 */
function on_click_backup_image_domain_button() {

    // const $float_bottom_menu_bar = $('.float_bottom_menu_bar');
    // const $backup_image_domain_button = $float_bottom_menu_bar.find('.enable_backup_image_domain');

    //逆转当前的按钮数值
    const value = getLocalStorage(LOCAL_STORAGE_KEY.enableBackupImageDomain);
    const new_value = !value;

    let confirm_text;

    //如果是要开启
    if (new_value === true) {
        confirm_text = '是否要启动备用图床? 如果无法正常加载站内图片, 可以尝试启动备用图床';
    }
    //如果是关闭
    else {
        confirm_text = '是否要关闭备用图床?';
    }

    open_confirm_modal(
        confirm_text,
        //确认
        () => {
            //更新数值
            setLocalStorage(LOCAL_STORAGE_KEY.enableBackupImageDomain, new_value);
            //创建通知弹窗
            MyToast.show_success('切换中, 请等待页面刷新');
            //刷新页面
            location.reload();
        }
    );

}
