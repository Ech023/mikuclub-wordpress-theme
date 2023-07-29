$(function () {

    //暗夜模式切换按钮的点击事件
    $('#dark_mode_button').on('click', on_click_button_darkmode);

});


function init_dark_mode() {


    const now = new Date();
    const storage_key = 'dark_mode2';

    /*init_dark_mode.darkmode = new Darkmode({
        mixColor: '#e7e7e7',
        //time: '0.5s', // default: '0.3s'
        saveInCookies: false, // default: true,   
        autoMatchOsTheme: true, // default: true
    });*/
    init_dark_mode.is_dark_mode_activated = false;


    //初始化 暗夜模式设置 ( 0 = 自动, 1 = 关闭, 2 = 启动)
    init_dark_mode.dark_mode_setting = 0;

    //获取设置记录
    let dark_mode = getLocalStorageForDarkMode(storage_key);
    if (dark_mode && dark_mode.expiry > now.getTime()) {
        //更新 暗夜模式的用户自定义设置
        init_dark_mode.dark_mode_setting = dark_mode.value;
    }


    update_dark_mode(init_dark_mode.dark_mode_setting);



}

/**
 * 根据配置,开启/关闭暗夜模式
 * 
 * @param {int} mode ( 0 = 自动, 1 = 关闭, 2 = 启动)
 */
function update_dark_mode(mode) {

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
    }
    //如果设置记录 为 开
    if (mode === 2) {

        //添加夜间模式css类
        $('body').addClass('darkmode');
    }
    //如果没有设置为自动模式 + 时间为 夜间 范围
    else if (mode === 0) {

        if(hour >= start_hour || hour <= end_hour){
            //添加夜间模式css类
            $('body').addClass('darkmode');
        }
        else{
            //移除夜间模式css类
            $('body').removeClass('darkmode');
        }

       

    }

    //当页面加载完成后再运行
    $(function () {

        let $dark_mode_button = $('#fixed-sidebar-menu #dark_mode_button');

        //如果暗夜模式已激活
        $dark_mode_button.children('span').hide();


        if (mode === 1) {
            $dark_mode_button.find('.sun').show();
            $dark_mode_button.attr('title', '点击开启夜间模式');
        }
        else if (mode === 2) {
            $dark_mode_button.find('.moon').show();
            $dark_mode_button.attr('title', '点击开启自动模式');
        }
        else if (mode === 0) {

            $dark_mode_button.find('.cloud-sun').show();
            $dark_mode_button.attr('title', '点击开启白天模式');
        }

    });



}

/**
 * 暗夜模式按钮的点击事件
 */
function on_click_button_darkmode() {

    const now = new Date();
    const expiry_time = 1000 * 60 * 60 * 24 * 7 // 7天 的 毫秒数
    const storage_key = 'dark_mode2'

    let new_setting;
    switch (init_dark_mode.dark_mode_setting) {
        case 0: new_setting = 1; break;
        case 1: new_setting = 2; break;
        case 2: new_setting = 0; break;
    }

    //设置新的暗夜模式配置
    init_dark_mode.dark_mode_setting = new_setting;

    //保存为一天后过期的本地储存
    setLocalStorageForDarkMode(storage_key, {
        value: init_dark_mode.dark_mode_setting,
        expiry: now.getTime() + expiry_time,
    });

    update_dark_mode(init_dark_mode.dark_mode_setting);
}



/**
 *从本地存储获取数据 暗夜模式专用
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
 *设置数据到本地储存 暗夜模式专用
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