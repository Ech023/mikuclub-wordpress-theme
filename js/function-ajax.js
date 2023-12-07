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
 * JS函数文件
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
    let data = {
        url: link,
    };

    //如果是阿里云
    if (link.includes('aliyun')) {

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
    let successCallback = function (response) {

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



/**
 * 加黑名单
 * @param {number} target_user_id
 */
function add_user_black_list(target_user_id) {

    open_confirm_modal('确认要将该用户添加到黑名单里吗?', '添加后对方将无法在你的投稿里评论/无法发私信给你/对方的投稿将会被遮盖, 在个人的用户信息页里可以管理黑名单',  () => {

        const data = {
            target_user_id,
        }

        //成功的情况
        let successCallback = function (response) {
            MyToast.show_success('添加黑名单成功');
        };

        //错误的情况
        let failCallback = function () {
            MyToast.show_error('添加黑名单失败');
        };

        let completeCallback = function () {

        };

        $.ajax({
            url: URLS.userBlackList,
            data,
            type: HTTP_METHOD.post,
            headers: createAjaxHeader()
        }).done(successCallback).fail(failCallback).always(completeCallback);

    });

}

/**
 * 移除黑名单
 * @param {number} target_user_id
 */
function delete_user_black_list(target_user_id) {

    open_confirm_modal('确认要将该用户从黑名单里移除吗?', '', () => {

        const data = {
            target_user_id,
        }

        //成功的情况
        let successCallback = function (response) {
            MyToast.show_success('移除黑名单成功');
        };

        //错误的情况
        let failCallback = function () {
            MyToast.show_error('移除黑名单失败');
        };

        let completeCallback = function () {

        };


        $.ajax({
            url: URLS.userBlackList,
            data,
            type: HTTP_METHOD.delete,
            headers: createAjaxHeader()
        }).done(successCallback).fail(failCallback).always(completeCallback);

    });


}




/**
 * 添加关注
 */
function add_user_follow_list() {

    //当前按钮
    const $button = $(this);
    //取消关注按钮
    const $delete_follow_button = $(this).siblings('button.delete-user-follow-list');
    //取消关注按钮的关注数子元素
    const $user_fans_count_element = $delete_follow_button.children('.user-fans-count');
    //获取当前关注数
    const $button_container_element = $(this).parent();
    const user_fans_count = $button_container_element.data('user-fans-count') + 1;
    $button_container_element.data('user-fans-count', user_fans_count);

    //获取要关注的用户ID
    const target_user_id = $(this).data('target-user-id');


    //添加为请求数据
    const data = {
        target_user_id,
    }


    //禁用按钮
    $button.toggleDisabled();


    //成功的情况
    const successCallback = function (response) {

        //创建通知弹窗
        MyToast.show_success('已添加关注');

        //隐藏当前按钮
        $button.hide();
        //显示取消关注按钮
        $delete_follow_button.show();
        //更新取消关注按钮的关注数
        $user_fans_count_element.html(user_fans_count);


    };

    //错误的情况
    const failCallback = function () {
        MyToast.show_error('添加关注失败');
    };

    const completeCallback = function () {

        //激活按钮
        $button.toggleDisabled();
    };


    $.ajax({
        url: URLS.userFollowed,
        data,
        type: HTTP_METHOD.post,
        headers: createAjaxHeader()
    }).done(successCallback).fail(failCallback).always(completeCallback);

}

/**
 * 移除关注
 */
function delete_user_follow_list() {

    //当前按钮
    const $button = $(this);
    //添加关注按钮
    const $add_follow_button = $(this).siblings('button.add-user-follow-list');
    //添加关注按钮的关注数子元素
    const $user_fans_count_element = $add_follow_button.children('.user-fans-count');
    //获取当前关注数
    const $button_container_element = $(this).parent();
    const user_fans_count = $button_container_element.data('user-fans-count') - 1;
    $button_container_element.data('user-fans-count', user_fans_count);

    //获取要关注的用户ID
    const target_user_id = $(this).data('target-user-id');


    //添加为请求数据
    const data = {
        target_user_id,
    }


    //禁用按钮
    $button.toggleDisabled();


    //成功的情况
    const successCallback = function (response) {

        //创建通知弹窗
        MyToast.show_success('已取消关注');

        //隐藏当前按钮
        $button.hide();
        //显示关注按钮
        $add_follow_button.show();
        //更新关注按钮的关注数
        $user_fans_count_element.html(user_fans_count);


    };

    //错误的情况
    const failCallback = function () {
        MyToast.show_error('取消关注失败');
    };

    const completeCallback = function () {

        //激活按钮
        $button.toggleDisabled();
    };


    $.ajax({
        url: URLS.userFollowed,
        data,
        type: HTTP_METHOD.delete,
        headers: createAjaxHeader()
    }).done(successCallback).fail(failCallback).always(completeCallback);

}