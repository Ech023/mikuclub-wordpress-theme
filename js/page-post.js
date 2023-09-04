/// <reference path="base.js" />
/// <reference path="function.js" />
/// <reference path="function-ajax.js" />


$(function () {

    /**
     * 如果在DOM中发现内页面类名
     */
    let $singlePage = $('body.single');
    if ($singlePage.length) {

        //更改按钮状态
        checkButtonStatus();

        //根据文章内容数据 隐藏相关菜单选项
        hideSidebarMenuItem();

        //绑定在线播放按钮 点击事件
        $('.video-part .play-button').on('click', '', '', openPlayModal);
        //绑定密码表单 点击事件
        $('.password-part input').on('click', function () {
            selectAllAndCopy($(this));
        });
        $('.download-part a.download').on('click', '', '', findDownPassword);

        //绑定功能按钮 点击事件
        $('.functional-part button.set-post-like').on('click', '', '', setPostLike);
        $('.functional-part button.set-post-unlike').on('click', '', '', setPostUnlike);
        $('.functional-part button.set-post-favorite').on('click', '', '', setPostFavorite);
        $('.functional-part .post-share a.dropdown-item').on('click', '', '', setPostShare);
        $('.functional-part button.set-post-fail-times').on('click', '', '', setPostFailTime);

        $('.functional-part button.open-post-report').on('click', '', '', openReportModal);

        $singlePage.on('click', '.modal.report-modal button.send-report', '', sendReport);


        //绑定打开百度盘按钮
        $('a.baidupan-home-link, textarea.baidu-fast-link').on('click', copyBaiduFastLink);

    }
});


/**
 * 打开视频播放窗口
 * @param {Event}event
 */
function openPlayModal(event) {


    let $playButton = $('body.single .video-part .play-button');
    let type = $playButton.data('video-type');
    let value = $playButton.val();

    //如果不是BILIBILI视频, 直接打开模态窗
    if (type !== PLAY_TYPE.bilibili) {

        //解义url字符串
        value = decodeURIComponent(value.replace(/\+/g, ' '));

        //创建打开模态窗
        new MyVideoModal(value).show();

    }
    //如果是b站视频, 需要先获取CID号
    else {

        let postId = $playButton.data('post-id');

        //切换按钮激活状态
        //切换文字和加载进度条显示
        $playButton.toggleDisabled();
        $playButton.children().toggle();

        let successCallback = function (response) {


            let url = 'https://player.bilibili.com/player.html?' + $.param({
                aid: response.aid,
                bvid: response.bvid,
                cid: response.cid,
                page: 1,
                danmaku: 1,
                autoplay: 1,
                //high_quality : 1,
            });
            let iframeCode = '<iframe src="' + url + '" allowfullscreen></iframe>';

            // iframeCode = '<iframe src="//player.bilibili.com/player.html?aid=' + response.aid + '&bvid=' + response.bvid + '&cid=' + response.cid + '&page=1&high_quality=1&danmaku=1" allowfullscreen="true"></iframe>';
            //创建打开模态窗
            new MyVideoModal(iframeCode).show();

        };


        let completeCallback = function () {
            //切换按钮激活状态
            //切换文字和加载进度条显示
            $playButton.toggleDisabled();
            $playButton.children().toggle();
        };

        //发送请求
        getBilibiliVIdeoData(value, postId, successCallback, defaultFailCallback, completeCallback);

    }


}


/**
 * 获取B站视频播放地址
 * @param {string} videoId
 * @param {int} postId
 * @param {Function} successCallback
 * @param {Function} failCallback
 * @param {Function} completeCallback
 */
function getBilibiliVIdeoData(videoId, postId, successCallback, failCallback, completeCallback) {

    //请求参数
    let data = {
        post_id: postId,
    };

    //如果是旧AV号
    if (videoId.includes('av')) {
        data.aid = videoId.slice(2);
    }
    else {
        data.bvid = videoId;
    }

    $.getJSON(URLS.bilibili, data).done(successCallback).fail(failCallback).always(completeCallback);


}


/**
 * 选择表单全部内容并复制
 * @param {JQuery}$element
 */
function selectAllAndCopy($element) {

    $element.trigger('select');
    //复制到剪切板
    copySelectedText();

}

/**
 * 复制选中内容到剪切板
 */
function copySelectedText() {
    //复制到剪切板
    document.execCommand('copy');
    //提示框
    TOAST_SYSTEM.add('已复制密码到剪切板', TOAST_TYPE.success);
}

/**
 * 根据下载按钮找到对应的访问密码
 * @param event
 */
function findDownPassword(event) {

    const $button = $(this);
    let passwordId = $button.data('password-id');
    //选中复制对应的密码栏
    selectAllAndCopy($('.password-part input.' + passwordId));

}

/**
 * 添加/取消好评
 * @param {Event} event
 */
function setPostLike(event) {

    //获取按钮
    const $button = $(this);

    let post_id = $button.data('post-id');
    let isActivated = $button.data('activated');

    const storageKey = LOCAL_STORAGE_KEY.postLike;

    const data = {
        post_id
    };
    //如果是已激活状态
    if (isActivated) {
        data.cancel = 1;
    }



    //成功的情况
    const successCallback = function (response) {

        if (!isActivated) {
            addArrayElementToLocalStorage(storageKey, post_id);
            updateButton($button, '已好评', 'btn-secondary', 'btn-outline-secondary', 1, true);
            TOAST_SYSTEM.add('好评成功', TOAST_TYPE.success);
        }
        else {
            deleteArrayElementFromLocalStorage(storageKey, post_id);
            updateButton($button, '好评', 'btn-outline-secondary', 'btn-secondary', -1, false);
            TOAST_SYSTEM.add('已取消好评', TOAST_TYPE.success);
        }

        //更新文章评价等级
        update_Post_Feedback_Rank();
    };



    send_post(
        URLS.postLike,
        data,
        //请求前运行
        () => {
            //注销按钮
            $button.toggleDisabled();
        },
        //成功后运行
        successCallback,
        //错误后运行
        defaultFailCallback,
        //请求解锁后运行
        () => {
            //激活按钮
            $button.toggleDisabled();
        }
    );

}

/**
 * 添加/取消差评
 * @param {Event} event
 */
function setPostUnlike(event) {

    //获取按钮
    const $button = $(this);

    let post_id = $button.data('post-id');
    let isActivated = $button.data('activated');

    const storageKey = LOCAL_STORAGE_KEY.postUnLike;

    const data = {
        post_id
    };
    //如果是已激活状态
    if (isActivated) {
        data.cancel = 1;
    }

    //成功的情况
    const successCallback = function (response) {

        if (!isActivated) {
            addArrayElementToLocalStorage(storageKey, post_id);
            updateButton($button, '已差评', 'btn-secondary', 'btn-outline-secondary', 1, true);
            TOAST_SYSTEM.add('差评成功', TOAST_TYPE.success);
        }
        else {
            deleteArrayElementFromLocalStorage(storageKey, post_id);
            updateButton($button, '差评', 'btn-outline-secondary', 'btn-secondary', -1, false);
            TOAST_SYSTEM.add('已取消差评', TOAST_TYPE.success);
        }

        //更新文章评价等级
        update_Post_Feedback_Rank();
    };


    send_post(
        URLS.postUnlike,
        data,
        //请求前运行
        () => {
            //注销按钮
            $button.toggleDisabled();
        },
        //成功后运行
        successCallback,
        //错误后运行
        defaultFailCallback,
        //请求解锁后运行
        () => {
            //激活按钮
            $button.toggleDisabled();
        }
    );

}

/**
 * 根据评价数量更新文章评价等级
 */
function update_Post_Feedback_Rank() {

    const $functional_part = $('.functional-part');

    let post_like_count = $functional_part.find('button.set-post-like .count').html();
    let post_unlike_count = $functional_part.find('button.set-post-unlike .count').html();

    console.log('更新评价' + post_like_count + ' ' + post_unlike_count);

    let rank = Post_Feedback_Rank.get_rank(post_like_count, post_unlike_count);

    //更新评价等级
    $functional_part.find('.Post_Feedback_Rank').html(rank);
}

/**
 * 设置收藏
 * @param {Event} event
 */
function setPostFavorite(event) {

    //获取按钮
    const $button = $(this);


    let postId = $button.data('post-id');
    let isActivated = $button.data('activated');

    //let storageKey = LOCAL_STORAGE_KEY.postFavorites;

    let data = { post_id: postId };
    //请求方式 默认为 post 添加收藏
    let requestMethod = HTTP_METHOD.post;
    //如果是已激活状态
    if (isActivated) {
        //请求方式 改成 delete 取消收藏
        requestMethod = HTTP_METHOD.delete;
    }
    //注销按钮
    $button.toggleDisabled();

    //成功的情况
    let successCallback = function (response) {


        if (!isActivated) {
            //addArrayElementToLocalStorage(storageKey, postId);
            updateButton($button, '已收藏', 'btn-secondary', 'btn-outline-secondary', 1, true);
            TOAST_SYSTEM.add('收藏成功', TOAST_TYPE.success);
        }
        else {
            //deleteArrayElementFromLocalStorage(storageKey, postId);
            updateButton($button, '收藏', 'btn-outline-secondary', 'btn-secondary', -1, false);
            TOAST_SYSTEM.add('已取消收藏', TOAST_TYPE.success);
        }

    };


    let completeCallback = function () {
        //激活按钮
        $button.toggleDisabled();
    };


    $.ajax({
        url: URLS.favorite,
        data,
        type: requestMethod,
        headers: createAjaxHeader()
    }).done(successCallback).fail(defaultFailCallback).always(completeCallback);
}

/**
 * 设置分享
 * @param {Event} event
 */
function setPostShare(event) {

    //获取按钮
    let $button = $(this).parent().siblings('button');


    let postId = $button.data('post-id');
    let isActivated = $button.data('activated');

    let storageKey = LOCAL_STORAGE_KEY.postShares;

    let data = { post_id: postId };
    //注销按钮
    $button.toggleDisabled();

    //成功的情况
    let successCallback = function (response) {


        addArrayElementToLocalStorage(storageKey, postId);
        updateButton($button, '已分享', 'btn-secondary', 'btn-outline-secondary', 1, true);


    };

    let completeCallback = function () {
        //激活按钮
        $button.toggleDisabled();
    };



    $.ajax({
        url: URLS.postShare,
        data,
        type: HTTP_METHOD.get,
        headers: createAjaxHeader()
    }).done(successCallback).fail(defaultFailCallback).always(completeCallback);
}


/**
 * 设置失效次数
 * @param {Event} event
 */
function setPostFailTime(event) {

    //获取按钮
    let $button = $(this);

    let postId = $button.data('post-id');
    let isActivated = $button.data('activated');

    //如果是已激活状态
    if (isActivated) {
        //避免重复上报
        //退出
        return;
    }

    if (!confirm('确认下载地址已失效了吗? (管理员会根据用户反馈次数, 退回稿件并通知UP补档)')) {
        //退出
        return;
    }

    let storageKey = LOCAL_STORAGE_KEY.postFailTimes;

    let data = { post_id: postId };


    //注销按钮
    $button.toggleDisabled();

    //成功的情况
    let successCallback = function (response) {

        addArrayElementToLocalStorage(storageKey, postId);
        updateButton($button, '已反馈', 'btn-secondary', 'btn-outline-secondary', 1, true);
        TOAST_SYSTEM.add('反馈成功', TOAST_TYPE.success);

    };


    let completeCallback = function () {
        //激活按钮
        $button.toggleDisabled();
    };



    $.ajax({
        url: URLS.failDown,
        data,
        type: HTTP_METHOD.get,
        headers: createAjaxHeader()
    }).done(successCallback).fail(defaultFailCallback).always(completeCallback);
}

/**
 * 根据本地储存的数据 变更功能按钮状态
 */
function checkButtonStatus() {

    const $functional_part = $('.functional-part');

    const $like_button = $functional_part.find('button.set-post-like');
    const $unlike_button = $functional_part.find('button.set-post-unlike');
    const $favorite_button = $functional_part.find('button.set-post-favorite');

    const $share_button = $functional_part.find('button.set-post-share');
    const $fail_time_button = $functional_part.find('button.set-post-fail-times');

    let postId = $like_button.data('post-id');

    let postLike = getLocalStorage(LOCAL_STORAGE_KEY.postLike);
    let postUnLike = getLocalStorage(LOCAL_STORAGE_KEY.postUnLike);
    let postShares = getLocalStorage(LOCAL_STORAGE_KEY.postShares);
    let postFailTimes = getLocalStorage(LOCAL_STORAGE_KEY.postFailTimes);

    let postFavorites = MY_SITE.favorite_post;

    if (postLike && postLike.includes(postId)) {
        updateButton($like_button, '已好评', 'btn-secondary', 'btn-outline-secondary', 0, true);
    }
    if (postUnLike && postUnLike.includes(postId)) {
        updateButton($unlike_button, '已差评', 'btn-secondary', 'btn-outline-secondary', 0, true);
    }

    if (postFavorites && postFavorites.includes(String(postId))) {
        updateButton($favorite_button, '已收藏', 'btn-secondary', 'btn-outline-secondary', 0, true);
    }

    if (postShares && postShares.includes(postId)) {
        updateButton($share_button, '已分享', 'btn-secondary', 'btn-outline-secondary', 0, true);
    }

    if (postFailTimes && postFailTimes.includes(postId)) {
        updateButton($fail_time_button, '已反馈', 'btn-secondary', 'btn-outline-secondary', 0, true);
        $fail_time_button.addDisabled();

    }


}

/**
 * 更新按钮内容
 * @param {JQuery} $button
 * @param {string} text
 * @param {string} oldClass
 * @param {string} newClass
 * @param {number} addCount
 * @param {boolean} isActivated 是否激活

 */
function updateButton($button, text, oldClass, newClass, addCount, isActivated) {

    let count = parseInt($button.children('span.count').html()) + addCount;
    $button.children('span.text').html(text);
    $button.children('span.count').html(count);
    $button.removeClass(oldClass).addClass(newClass);

    //如果是已激活状态, 增加激活数据
    if (isActivated) {
        $button.data('activated', 1);
    }
    //否则移除数据
    else {
        $button.removeData('activated');
    }


}



/**
 * 根据当前文章数据 隐藏相关浮动菜单的选项
 */
function hideSidebarMenuItem() {

    //获取悬浮菜单
    let $sidebarMenu = $('#fixed-sidebar-menu');

    //如果无下载 内容 隐藏对应的菜单选项
    if (!$('.article-content .download-part').children().length) {

        $sidebarMenu.children('a[data-bs-target-id="password-part"]').hide();
    }
    //无在线播放
    if (!$('.article-content .video-part').children().length) {
        $sidebarMenu.children('a[data-bs-target-id="video-part"]').hide();
    }
    //无预览图片
    if (!$('.article-content .preview-images-part').children().length) {
        $sidebarMenu.children('a[data-bs-target-id="preview-images-part"]').hide();
    }


}


/**
 * 开启投诉模态窗
 * @param {Event} event
 */
function openReportModal(event) {

    //获取按钮
    const $button = $(this);

    let postId = $button.data('post-id');
    let recipientId = $button.data('recipient-id');
    let senderId = $button.data('sender-id');

    new MyReportModal(postId, recipientId, senderId).show();

}

/**
 * 发送投诉
 * @param {Event} event
 */
function sendReport(event) {


    //获取按钮
    const $button = $(this);

    let $modalElement = $button.parents('.report-modal');
    let $reportTypeRadio = $modalElement.find('input[name="report_type"]:checked');
    //如果未选中任何投诉类型
    if (!$reportTypeRadio.length) {
        TOAST_SYSTEM.add('请先选择投诉类型', TOAST_TYPE.error);
        return;
    }

    //获取表单内容
    let report_type = $reportTypeRadio.val();
    let report_description = $modalElement.find('textarea[name="report_description"]').val().trim();
    //如果未填写描述
    if (!report_description.length) {
        TOAST_SYSTEM.add('请描述具体问题', TOAST_TYPE.error);
        return;
    }

    let report_contact = $modalElement.find('input[name="report_contact"]').val().trim();
    let post_id = $modalElement.find('input[name="post_id"]').val();

    //查询参数
    let data = {
        report_type,
        post_id,
    };
    if (report_description) {
        data.report_description = report_description;
    }
    if (report_contact) {
        data.report_contact = report_contact;
    }

    //切换按钮的显示
    $button.toggleDisabled();
    $button.children().toggle();

    //成功的情况
    let successCallback = function (response) {

        TOAST_SYSTEM.add('提交成功, 管理员一般会在48小时内处理', TOAST_TYPE.success);
        //关闭模态窗
        $modalElement.modal('hide');

    };


    let completeCallback = function () {
        //激活按钮
        $button.toggleDisabled();
        $button.children().toggle();
    };


    $.ajax({
        url: URLS.messageReport,
        data,
        type: HTTP_METHOD.post,
        headers: createAjaxHeader()
    }).done(successCallback).fail(defaultFailCallback).always(completeCallback);



}

/**
 * 增加文章点击数
 * @param post_id
 */
function addPostViews(post_id) {

    //如果无ID参数
    if (!post_id) {
        return;
    }

    //降低请求的发送几率 , 降低数据库负载
    let index = Math.floor(Math.random() * 5) + 1;

    if (index === 3) {

        let view_number = (Math.floor(Math.random() * 10) + 1);

        //发送请求
        $.get(URLS.postViewCount, { post_id, view_number }, null);
    }




}


/**
 * 复制百度秒传链接到剪切板
 */
function copyBaiduFastLink() {

    let $baiduFastLinkElement = $('.baidu-fast-link');

    if ($baiduFastLinkElement.length) {

        //选中
        $baiduFastLinkElement.trigger('select');
        //复制到剪切板
        document.execCommand('copy');
        //提示框
        TOAST_SYSTEM.add('已复制秒传链接到剪切板', TOAST_TYPE.success);

    }





}