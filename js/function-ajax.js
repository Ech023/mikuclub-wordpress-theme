/// <reference path="common/base.js" />
/// <reference path="common/constant.js" />
/// <reference path="class/class-comment.js" />
/// <reference path="class/class-message.js" />
/// <reference path="class/class-modal.js" />
/// <reference path="class/class-post.js" />
/// <reference path="class/class-toast.js" />
/// <reference path="class/class-ua-parser.js" />
/// <reference path="class/class-user.js" />
/// <reference path="function-modal.js" />



/***
 * 未分类的 包含AJAX请求的函数
 */

/**
 * 检测百度分享链接有效性
 * @param {string} link
 * @param {Function} isValidCallback
 * @param {Function} isInvalidCallback
 * @param {Function} errorCallback
 */
function checkBaiduPanValidity(link, isValidCallback, isInvalidCallback, errorCallback) {

    let is_aliyun = false;
    const data = {
        url: link,
    };

    //如果是阿里云 旧版地址 或者 新版地址
    if (link.includes('aliyun') || link.includes('alipan')) {

        is_aliyun = true;

        //如果链接最后一位是 斜杠
        if (link[link.length - 1] === '/') {
            //移除斜杠
            link = link.substring(0, link.length - 1);
        }

        //设置阿里云分享ID
        data.share_id = link.split('/').pop();
    }


    //成功请求的情况
    const successCallback = function (response) {

        //如果是阿里云
        if (is_aliyun) {

            //如果回复包含code, 说明已失效
            if (response.code) {
                isInvalidCallback();
            }
            else {
                isValidCallback();
            }

        }
        //如果是百度
        else {

            //如果链接已失效
            if (response.includes("已过期") || response.includes("无法访问") || response.includes("删除了") || response.includes("已经被取消") || response.includes("不存在") || response.includes("错误")) {

                isInvalidCallback();
            } else {
                isValidCallback();
            }
        }

    };

    let url = URLS.checkBaiduPan;
    if (is_aliyun) {
        url = URLS.checkAliyunPan;
    }


    $.get(url, data).done(successCallback).fail(errorCallback);

}


