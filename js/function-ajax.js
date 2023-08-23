/***
 * JS函数文件
 */



/**
 *获取下一页文章
 * @param {Event} event
 */
function getNextPage(event) {

    //获取按钮
    const $button = $(this);

    let currentPaged;
    //遍历收集所有查询参数input的表单
    let queryVars = {};
    $button.nextAll('input[type="hidden"]').each((index, input) => {

        let key = $(input).attr('name');
        let value = $(input).val();

        //如果键名 是用户输入的搜索词 需要解义
        if (key === 's') {
            value = decodeURIComponent(value);

            //在有搜索的情况下关闭缓存
            queryVars['no_cache'] = true;
        }
        //如果是页码, 自动+1
        if (key === 'paged') {
            value = parseInt(value) + 1;
            currentPaged = value;
        }

        queryVars[key] = value;
    });


    //请求参数
    let data = queryVars;


    //切换按钮激活状态, 加载进度条和文字的显示
    $button.toggleDisabled();
    $button.children().toggle();

    //回调函数
    let successCallback = function (response) {

        //不是空的
        if (isNotEmptyArray(response)) {


            //创建自定义文章列表
            let newPostList = new MyPostSlimList(POST_TYPE.post);
            //转换成自定义文章格式
            newPostList.add(response);
            //输出成html加入到页面里
            $('.post-list').append(newPostList.toHTML());
        }
        //无内容
        else {
            notMoreCallback();
        }

        //更新当前页数
        $button.nextAll('input[name="paged"]').val(currentPaged);


    };

    /**
     * 错误情况: 没有更多文章
     */
    let notMoreCallback = function () {

        TOAST_SYSTEM.add('没有更多内容了', TOAST_TYPE.error);

        //注销按钮
        $button.toggleDisabled();
        $button.html('已经到最后一页了');
    };



    let completeCallback = function () {

        //切换按钮激活状态, 加载进度条和文字的显示
        $button.toggleDisabled();
        $button.children().toggle();

    };


    $.ajax({
        url: URLS.postList,
        data,
        type: HTTP_METHOD.get,
        headers: createAjaxHeader()
    }).done(successCallback).fail(defaultFailCallback).always(completeCallback);


}

/**
 * 发送私信
 * @param {Event}event
 */
function sendPrivateMessage(event) {

    //获取按钮
    const $button = $(this);

    let recipient_id = $button.siblings('input[name="recipient_id"]').val();
    let message_content = $('.modal textarea.message-content').val().trim();




    let data = {
        content: message_content,
        recipient_id,
    };

    //如果内容为空
    if (!message_content) {

        TOAST_SYSTEM.add('消息内容不能为空', TOAST_TYPE.error);

    } else {


        //切换按钮激活状态 和 按钮显示内容
        $button.toggleDisabled();
        $button.children().toggle();

        //回调函数
        let successCallback = function (response) {

            //创建通知弹窗
            TOAST_SYSTEM.add('消息已发送', TOAST_TYPE.success);
            //关闭模态窗
            $('.modal').modal('hide');


        };

        /**
         * 错误情况
         */
        let failCallback = function (jqXHR) {


            let errorText = '发送错误 请重试';

            //如果存在wpError对象
            const wpError = getWpErrorByJqXHR(jqXHR);
            if (wpError && wpError.data) {
                errorText = wpError.data;
            }

            //创建通知弹窗
            TOAST_SYSTEM.add(errorText, TOAST_TYPE.error);
            //切换按钮激活状态 和 按钮显示内容
            $button.toggleDisabled();
            $button.children().toggle();
        };


        $.ajax({
            url: URLS.privateMessage,
            data,
            type: HTTP_METHOD.post,
            headers: createAjaxHeader()
        }).done(successCallback).fail(failCallback);

    }

}

/**
 *设置关注
 * @param {Event}event
 */
function setUserFollowed(event) {

    //获取按钮
    const $button = $(this);

    //关注数元素
    let $userFansCount = $button.children('.user-fans-count');

    //是否是关注操作
    //TRUE 是添加关注, FALSE 是取消关注
    let isAddFollow = event.data;

    //根据操作类型 设置不同的请求方式
    let requestMethod;
    if (isAddFollow) {
        requestMethod = HTTP_METHOD.post;
    }
    else {
        requestMethod = HTTP_METHOD.delete;
    }

    //获取要关注的用户ID
    let user_id = $button.data('user-id');
    //添加为请求数据
    let data = {
        user_id
    };

    //切换按钮激活状态 和 按钮显示内容
    $button.toggleDisabled();

    //回调函数
    let successCallback = function (response) {

        //切换按钮的类名
        $button.toggleClass('follow unfollow btn-miku btn-secondary');
        //切换按钮文本显示
        $button.children('.text').toggle();

        let message;
        if (isAddFollow) {
            message = '已添加关注';
            //关注数+1
            $userFansCount.html((+$userFansCount.html()) + 1);
        }
        else {
            message = '已取消关注';
            //关注数-1
            $userFansCount.html((+$userFansCount.html()) - 1);
        }

        //创建通知弹窗
        TOAST_SYSTEM.add(message, TOAST_TYPE.success);

    };

    /**
     * 错误情况
     */
    let failCallback = function () {

        //创建通知弹窗
        TOAST_SYSTEM.add('关注失败 请重试', TOAST_TYPE.error);
    };

    let completeCallback = function () {

        //切换按钮激活状态, 加载进度条和文字的显示
        $button.toggleDisabled();

    };


    $.ajax({
        url: URLS.userFollowed,
        data,
        type: requestMethod,
        headers: createAjaxHeader()
    }).done(successCallback).fail(failCallback).always(completeCallback);


}



/**
 * 检测百度分享链接有效性
 * @param {string} link
 * @param {function} isValidCallback
 * @param {function} isInvalidCallback
 * @param {function} errorCallback
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


    if (!confirm('确认要将该用户添加到黑名单里吗? (添加后对方将无法在你的投稿里评论/无法发私信给你/对方的投稿将会被遮盖, 后续可以在个人中心里移除黑名单)')) {
        return;
    }

    const data = {
        target_user_id,
    }

    //成功的情况
    let successCallback = function (response) {
        TOAST_SYSTEM.add('添加黑名单成功', TOAST_TYPE.success);
    };

    //错误的情况
    let failCallback = function () {
        TOAST_SYSTEM.add('添加黑名单失败', TOAST_TYPE.error);
    };

    let completeCallback = function () {

    };


    $.ajax({
        url: URLS.userBlackList,
        data,
        type: HTTP_METHOD.post,
        headers: createAjaxHeader()
    }).done(successCallback).fail(failCallback).always(completeCallback);

}

/**
 * 移除黑名单
 * @param {number} target_user_id
 */
function delete_user_black_list(target_user_id) {


    if (!confirm('确认要将该用户从黑名单里移除吗?')) {
        return;
    }

    const data = {
        target_user_id,
    }

    //成功的情况
    let successCallback = function (response) {
        TOAST_SYSTEM.add('移除黑名单成功', TOAST_TYPE.success);
    };

    //错误的情况
    let failCallback = function () {
        TOAST_SYSTEM.add('移除黑名单失败', TOAST_TYPE.error);
    };

    let completeCallback = function () {

    };


    $.ajax({
        url: URLS.userBlackList,
        data,
        type: HTTP_METHOD.delete,
        headers: createAjaxHeader()
    }).done(successCallback).fail(failCallback).always(completeCallback);

}