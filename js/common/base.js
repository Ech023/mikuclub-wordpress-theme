/// <reference path="constant.js" />
/// <reference path="../function.js" />
/// <reference path="../function-ajax.js" />

/**.
 * 基础JS函数
 */


/*常量和全局变量*/



//消息类型
const MESSAGE_TYPE = {
    privateMessage: 'private_message',
    privateMessageWithOneSender: 'private_message_with_one_sender',
    commentReply: 'comment_reply',
    forumReply: 'forum_reply',
};

//文章类型
const POST_TEMPLATE = {
    default: 'default',
    favoritePost: 'favorite_post',
    historyPost: 'history_post',
    managePost: 'manage_post',
    failDownloadPost: 'fail_download_post',

};

//在线播放类型
const VIDEO_TYPE = {
    video: 'video',
    music: 'music',
    bilibili: 'bilibili',
    youtube: 'youtube',
};

//本地存储键名
const LOCAL_STORAGE_KEY = {
    postLike: 'count_like',
    postUnLike: 'count_unlike',
    //postFavorites: 'count_favorite',
    postFailTimes: 'fail_time',
    postShares: 'count_sharing',
    postHistory: 'post_history',
    commentLikes: 'comment_likes',
    commentDislikes: 'comment_dislikes',

    enableBackupImageDomain: 'enable_backup_image_domain',
};



//API地址列表
const UTILS_PATH = 'utils/v2/';
const WP_PATH = 'wp/v2/';
const URLS = {
    posts: MY_SITE.apiRoot + WP_PATH + 'posts',
    media: MY_SITE.apiRoot + WP_PATH + 'media',
    userSelf: MY_SITE.apiRoot + WP_PATH + 'users/me',

    postList: MY_SITE.apiRoot + UTILS_PATH + 'post_list',
    privateMessage: MY_SITE.apiRoot + UTILS_PATH + 'message',
    comments: MY_SITE.apiRoot + UTILS_PATH + 'comments',
    //bbpressReply: MY_SITE.apiRoot + UTILS_PATH + 'bbpress',
    qiandaoImg: MY_SITE.apiRoot + UTILS_PATH + 'qiandao_img',
    checkBaiduPan: MY_SITE.apiRoot + UTILS_PATH + 'check_baidu_pan_link',
    checkAliyunPan: MY_SITE.apiRoot + UTILS_PATH + 'check_aliyun_pan_link',
    favoritePostList: MY_SITE.apiRoot + UTILS_PATH + 'favorite_post_list',
    postLike: MY_SITE.apiRoot + UTILS_PATH + 'post_like_count',
    postUnlike: MY_SITE.apiRoot + UTILS_PATH + 'post_unlike_count',
    favorite: MY_SITE.apiRoot + UTILS_PATH + 'favorite',
    failDown: MY_SITE.apiRoot + UTILS_PATH + 'fail_down',
    postShare: MY_SITE.apiRoot + UTILS_PATH + 'post_sharing_count',
    rejectPost: MY_SITE.apiRoot + UTILS_PATH + 'reject_post',
    bilibili: MY_SITE.apiRoot + UTILS_PATH + 'get_bilibili_video_info',
    commentList: MY_SITE.apiRoot + UTILS_PATH + 'comment_list',
    messageReport: MY_SITE.apiRoot + UTILS_PATH + 'message_report',
    stickyPosts: MY_SITE.apiRoot + UTILS_PATH + 'sticky_posts',
    updatePostDate: MY_SITE.apiRoot + UTILS_PATH + 'update_post_date',
    userFollowed: MY_SITE.apiRoot + UTILS_PATH + 'user_followed',
    postViewCount: MY_SITE.apiRoot + UTILS_PATH + 'post_view_count',

    commentLikes: MY_SITE.apiRoot + UTILS_PATH + 'comment_likes',

    draftPost: MY_SITE.apiRoot + UTILS_PATH + 'draft_post',

    userBlackList: MY_SITE.apiRoot + UTILS_PATH + 'user_black_list',

    addStickyComment: MY_SITE.apiRoot + UTILS_PATH + 'add_sticky_comment',
    deleteStickyComment: MY_SITE.apiRoot + UTILS_PATH + 'delete_sticky_comment',

    forumReplyList: MY_SITE.apiRoot + UTILS_PATH + 'forum_reply_list',
    deleteUserSelf: MY_SITE.apiRoot + UTILS_PATH + 'delete_user_self',
};

const HTTP_METHOD = {
    post: 'POST',
    get: 'GET',
    put: 'PUT',
    delete: 'DELETE'
};

const BACKUP_IMAGE_DOMAIN = 'file.mikuclub.fun';

const SITE_DOMAIN = {
    www_mikuclub_online: 'www.mikuclub.online',
    www_mikuclub_cc: 'www.mikuclub.cc',
    www_mikuclub_win: 'www.mikuclub.win',
    www_mikuclub_eu: 'www.mikuclub.eu',
    www_mikuclub_uk: 'www.mikuclub.uk',

    /**
     * 获取所有用过的域名
     * @returns {string[]}
     */
    get_array_site_domain() {
        return [
            this.www_mikuclub_online,
            this.www_mikuclub_cc,
            this.www_mikuclub_win,
            this.www_mikuclub_eu,
            this.www_mikuclub_uk,
        ]
    },

    /**
     * 获取已经失效的域名 (需要跳转到主域名)
     * @returns {string[]}
     */
    get_array_site_domain_disabled() {
        return [
            this.www_mikuclub_online,
            this.www_mikuclub_cc,
        ];
    },

    /**
     * 设置当前在使用的主域名
    * @returns {string}
    */
    get_main_domain() {
        return this.www_mikuclub_win;
    },

    /**
     * 获取当前访问的域名
     * @returns {string}
     */
    get_current_domain() {
        return window.location.hostname;
    }
}


/**
 * 检测是否是个有内容的数组
 * @param object
 * @returns {boolean}
 */
function isNotEmptyArray(object) {
    let isNotEmptyArray = false;
    if (Array.isArray(object) && object.length) {
        isNotEmptyArray = true;
    }

    return isNotEmptyArray;

}

/**
 * 判断是否为函数
 * @param {*} variable 
 * @returns {boolean}
 */
function isFunction(variable) {
    return typeof variable === 'function';
}
/**
 * 自定义时间格式化函数
 * @param fmt
 * @returns {*}
 */
Date.prototype.format = function (fmt) {
    var o = {
        "M+": this.getMonth() + 1,                 //月份
        "d+": this.getDate(),                    //日
        "h+": this.getHours(),                   //小时
        "m+": this.getMinutes(),                 //分
        "s+": this.getSeconds(),                 //秒
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        "S": this.getMilliseconds()             //毫秒
    };
    if (/(y+)/.test(fmt)) {
        fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    }
    for (var k in o) {
        if (new RegExp("(" + k + ")").test(fmt)) {
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length === 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
        }
    }
    return fmt;
};


/**
 * 提取url中的query参数 转换成对象
 * 注意 如果参数不伴随=等号 则会发生错误
 */
function getQueryParameters() {

    let queryObject;

    //只有请求参数存在的时候
    if (location.search) {
        let queryString = location.search.substring(1);

        queryObject = JSON.parse(`{"${queryString.replace(/&/g, '","').replace(/=/g, '":"')}"}`, (key, value) => {
            //对url数值进行解码
            return key === "" ? value : decodeURIComponent(value);
        });
    }
    else {
        //创建空对象
        queryObject = {};
    }
    return queryObject;

}

//增加自定义JQUERY函数 切换 元素disabled状态
$.fn.extend({
    toggleDisabled: function () {
        if ($(this).attr('disabled')) {
            $(this).removeAttr('disabled');
        }
        else {
            $(this).attr('disabled', true);
        }
    },
    addDisabled: function () {
        $(this).attr('disabled', true);
    },
    removeDisabled: function () {
        $(this).removeAttr('disabled');
    }
});



/**
 * 从错误回复JqXHR里提取 WpError 对象
 * @param {Object} jqXHR
 * @return {Object|null} 如果WpError对象存在的话 , null 如果不是 WpError对象
 */
function getWpErrorByJqXHR(jqXHR) {

    let wpError = null;

    //如果存在数据
    if (jqXHR.responseJSON) {

        const object = jqXHR.responseJSON;

        //如果存在WpError对象
        if (typeof object == 'object' && object.hasOwnProperty('code') && object.hasOwnProperty('message') && object.hasOwnProperty('data')) {

            object.code
            wpError = object;
        }

    }

    return wpError;


}


/**
 * 检测当前元素是否在屏幕上可见 (部分)
 * @param {JQuery}$element
 * @return {boolean}
 */
function isVisibleOnScreen($element) {
    let winTop = $(window).scrollTop();
    let winBottom = winTop + $(window).height();

    let elementTop = $element.offset().top;
    let elementBottom = elementTop + $element.outerHeight();

    return ((elementBottom > winTop) && (elementTop < winBottom));
}


/**
 * 检测当前元素是否在屏幕上可见 (完全)
 * @param {JQuery}$element
 * @return {boolean}
 */
function isCompleteVisibleOnScreen($element) {
    let winTop = $(window).scrollTop();
    let winBottom = winTop + $(window).height();
    let elementTop = $element.offset().top;
    let elementBottom = elementTop + $element.height();
    return ((elementBottom <= winBottom) && (elementTop >= winTop));
}


/**
 * 获取cookie键值
 * @param {string} key
 * @returns {string}
 */
function getCookie(key) {
    "use strict";

    let value = '';
    //检测cookie 对象中是否存有 cookie
    if (document.cookie.length > 0) {

        let c_start = document.cookie.indexOf(key + "=");
        //检查我们指定 签到cookie 是否已存在
        if (c_start !== -1) {
            //移动start浮标, 跳过cookie键名和等号, 捕捉内容开始和结束的位置
            c_start = c_start + key.length + 1;
            let c_end = document.cookie.indexOf(';', c_start);
            if (c_end === -1) {
                c_end = document.cookie.length;
            }
            //读取cookie的实际内容, 进行16进制解码
            value = decodeURIComponent(document.cookie.substring(c_start, c_end));
            //再进行一遍json解义
            value = JSON.parse(value);
        }
    }

    return value;
}

/**
 * 设置cookie
 * @param {string} key
 * @param {string} value
 * @param {number} expiredays
 */
function setCookie(key, value, expiredays) {
    "use strict";

    //获取系统时间
    let exdate = new Date();

    //计算和设置过期时间
    exdate.setDate(exdate.getDate() + expiredays);
    exdate.setHours(23, 59, 59);
    //把键值转换成json字符串
    value = JSON.stringify(value);
    //然后再转换成16进制
    value = encodeURIComponent(value);
    //插入/更新 cookie, 如果过期时间为空, 则不设置过期时间
    document.cookie = key + "=" + value + ";expires=" + exdate.toGMTString();

}

/**
 *从本地存储获取数据
 * @param {string} key
 * @returns {any}
 */
function getLocalStorage(key) {

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
 *设置数据到本地储存
 * 错误的情况返回false
 * @param {string} key
 * @param {any} value
 * @return {boolean}
 **/
function setLocalStorage(key, value) {

    let result = false;
    //只有在支持localStorage的情况 并且键名和键值不是空
    if (window.localStorage && key && (value !== undefined && value !== null)) {
        //键值转换成json格式
        value = JSON.stringify(value);
        //设置本地储存
        window.localStorage.setItem(key, value);
        result = true;
    }

    return result;

}

/**
 * 检测邮箱有效性
 * @param {string}email
 * @return {boolean}
 */
function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

/**
 * 检测链接有效性
 * @param url
 * @return {boolean}
 */
function validateURL(url) {
    try {
        new URL(url);
    } catch (_) {
        return false;
    }

    return true;
}


/**
 * 默认错误回调
 * @param jqXHR
 */
function defaultFailCallback(jqXHR) {

    let errorText = '请求失败';
    //如果存在wpError对象
    const wpError = getWpErrorByJqXHR(jqXHR);
    if (wpError && wpError.message) {
        errorText = wpError.message;
    }

    MyToast.show_error(errorText);

}


/**
 * 滚动到指定位置
 */
function scroll_to_element($container = null, $target) {

    if (!$container) {
        $container = $(window);
    }

    $container.scrollTo($target);

    //滚动
    // $('html, body').animate({
    //     scrollTop: $("#" + element_id).offset().top
    // }, 1000);
}



/**
 * 创建异步请求头部
 * @return {Object} 如果用户未登陆 返回空对象
 */
function createAjaxHeader() {

    //只有在用户有登陆的情况 才设置求头部对象
    let headers = {};
    //如果用户有登陆
    if (MY_SITE.user_id > 0) {
        headers['X-WP-Nonce'] = MY_SITE.nonce;
    }
    return headers;

}


/**
 * 储存数组元素 到 本地储存
 * @param {string} storageKey
 * @param {any} value
 */
function addArrayElementToLocalStorage(storageKey, value) {

    //读取数组, 如果不存在 就创建空数组
    let list = getLocalStorage(storageKey) || [];

    //如果元素不存在
    if (!list.includes(value)) {
        //添加元素到数组
        list.push(value);
        //更新储存
        setLocalStorage(storageKey, list);
    }

}

/**
 *
 * @param {string} storageKey
 * @param {any} value
 */
function deleteArrayElementFromLocalStorage(storageKey, value) {

    //读取数组
    let list = getLocalStorage(storageKey);
    //如果存在数组 并且 包含需要删除的元素
    if (Array.isArray(list) && list.length && list.indexOf(value) !== -1) {
        //从数组中移除
        list.splice(list.indexOf(value), 1);
        //更新储存
        setLocalStorage(storageKey, list);
    }

}

/**
 * 发送GET请求
 * @param {string} url 
 * @param {object} data 
 * @param {function|null} pre_callback 
 * @param {function|null} done_callback 
 * @param {function|null} fail_callback 
 * @param {function|null} always_callback 
 */
function send_get(url, data, pre_callback = null, done_callback = null, fail_callback = defaultFailCallback, always_callback = null) {
    send_request(
        HTTP_METHOD.get,
        url,
        data,
        pre_callback,
        done_callback,
        fail_callback,
        always_callback
    );
}

/**
 * 发送POST请求
 * @param {string} url 
 * @param {object} data 
 * @param {function|null} pre_callback 
 * @param {function|null} done_callback 
 * @param {function|null} fail_callback 
 * @param {function|null} always_callback 
 */
function send_post(url, data, pre_callback = null, done_callback = null, fail_callback = defaultFailCallback, always_callback = null) {
    send_request(
        HTTP_METHOD.post,
        url,
        data,
        pre_callback,
        done_callback,
        fail_callback,
        always_callback
    );
}


/**
 * 发送DELETE请求
 * @param {string} url 
 * @param {object} data 
 * @param {function|null} pre_callback 
 * @param {function|null} done_callback 
 * @param {function|null} fail_callback 
 * @param {function|null} always_callback 
 */
function send_delete(url, data, pre_callback = null, done_callback = null, fail_callback = defaultFailCallback, always_callback = null) {
    send_request(
        HTTP_METHOD.delete,
        url,
        data,
        pre_callback,
        done_callback,
        fail_callback,
        always_callback
    );
}

/**
 * 通过 $.ajax 发送请求
 * @param {string} method 
 * @param {string} url 
 * @param {object} data 
 * @param {function|null} pre_callback 
 * @param {function|null} done_callback 
 * @param {function|null} fail_callback 
 * @param {function|null} always_callback 
 */
function send_request(method, url, data, pre_callback = null, done_callback = null, fail_callback = defaultFailCallback, always_callback = null) {

    //如果存在前置回调
    if (isFunction(pre_callback)) {
        pre_callback();
    }

    $.ajax({
        url,
        data,
        method,
        dataType: 'json',
        headers: createAjaxHeader(),
    }).done(done_callback).fail(fail_callback).always(always_callback);

}


/**
 * 发送上传文件请求
 * @param {string} url 
 * @param {FormData} data 
 * @param {function|null} pre_callback 
 * @param {function|null} done_callback 
 * @param {function|null} fail_callback 
 * @param {function|null} always_callback 
 */
function send_file(url, data, pre_callback = null, done_callback = null, fail_callback = defaultFailCallback, always_callback = null) {

    //如果存在前置回调
    if (isFunction(pre_callback)) {
        pre_callback();
    }

    $.ajax({
        url,
        data,
        method: HTTP_METHOD.post,
        mimeType: "multipart/form-data",
        contentType: false,
        processData: false,
        dataType: 'json',
        headers: createAjaxHeader(),
    }).done(done_callback).fail(fail_callback).always(always_callback);

}

