/// <reference path="common/base.js" />
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




        //绑定密码表单 点击事件
        $('.download-part input').on('click', function () {
            copy_to_clipboard($(this));
        });
        $('.download-part a.download').on('click', () => {
            find_access_password($(this));
        });

        //绑定功能按钮 点击事件
        $('.functional-part button.set-post-like').on('click', '', '', setPostLike);
        $('.functional-part button.set-post-unlike').on('click', '', '', setPostUnlike);
        $('.functional-part button.set-post-favorite').on('click', '', '', setPostFavorite);
        $('.functional-part .post-share a.dropdown-item').on('click', '', '', setPostShare);
        $('.functional-part button.set-post-fail-times').on('click', '', '', setPostFailTime);


        //绑定打开百度盘按钮
        $('a.baidupan-home-link, textarea.baidu-fast-link').on('click', copyBaiduFastLink);


        //初始化图片灯箱 自定义配置
        lightbox.option({
            'albumLabel': "第 %1 张 / 总共 %2 张",
            wrapAround: true,
            disableScrolling: true
        });

    }
});


/**
 * 根据下载按钮找到对应的访问密码
 * @param {jQuery} $button
 */
function find_access_password($button) {

    const $input_access_password = $button.closest('.download_container').find('input.access_password');
    //复制对应的密码栏到剪切板
    copy_to_clipboard($input_access_password);

}


/**
 * 复制INPUT的数值到剪切板
 * @param {JQuery} $input
 */
function copy_to_clipboard($input) {

    // $element.trigger('select');
    const value = $input.val();
    //使用新版方法复制到剪切板
    if (navigator.clipboard) {
        navigator.clipboard.writeText(value);
    }
    //否则继续用旧版复制方法
    else {
        $input.trigger('select');
        //复制到剪切板
        document.execCommand('copy');
    }

    //提示框
    MyToast.show_success('已自动复制密码: ' + value + ' 到剪切板');
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
            MyToast.show_success('好评成功');
        }
        else {
            deleteArrayElementFromLocalStorage(storageKey, post_id);
            updateButton($button, '好评', 'btn-outline-secondary', 'btn-secondary', -1, false);
            MyToast.show_success('已取消好评');
        }

        //更新文章评价等级
        update_post_feedback_rank();
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
            MyToast.show_success('差评成功');
        }
        else {
            deleteArrayElementFromLocalStorage(storageKey, post_id);
            updateButton($button, '差评', 'btn-outline-secondary', 'btn-secondary', -1, false);
            MyToast.show_success('已取消差评');
        }

        //更新文章评价等级
        update_post_feedback_rank();
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
function update_post_feedback_rank() {

    const $functional_part = $('.functional-part');

    let post_like_count = $functional_part.find('button.set-post-like .count').html();
    let post_unlike_count = $functional_part.find('button.set-post-unlike .count').html();

    console.log('更新评价' + post_like_count + ' ' + post_unlike_count);

    let rank = POST_FEEDBACK_RANK.get_rank(post_like_count, post_unlike_count);

    //更新评价等级
    $functional_part.find('.post_feedback_rank').html(rank);
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
            MyToast.show_success('收藏成功');
        }
        else {
            //deleteArrayElementFromLocalStorage(storageKey, postId);
            updateButton($button, '收藏', 'btn-outline-secondary', 'btn-secondary', -1, false);
            MyToast.show_success('已取消收藏');
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

    open_confirm_modal('确认要反馈下载地址失效吗?', '管理员会根据总体的反馈次数, 退回稿件并通知UP主下载已失效', () => {


        let storageKey = LOCAL_STORAGE_KEY.postFailTimes;

        let data = { post_id: postId };


        //注销按钮
        $button.toggleDisabled();

        //成功的情况
        let successCallback = function (response) {

            addArrayElementToLocalStorage(storageKey, postId);
            updateButton($button, '已反馈', 'btn-secondary', 'btn-outline-secondary', 1, true);
            MyToast.show_success('反馈成功');

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

    });
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
 * 增加文章点击数
 * @param post_id
 */
function addPostViews(post_id) {

    //如果无ID参数
    if (!post_id) {
        return;
    }

    const view_number = 1;
    $.get(URLS.postViewCount, { post_id, view_number }, null);

    // //降低请求的发送几率 , 降低数据库负载
    // let index = Math.floor(Math.random() * 5) + 1;

    // if (index === 3) {

    //     let view_number = (Math.floor(Math.random() * 10) + 1);

    //     //发送请求
    //     $.get(URLS.postViewCount, { post_id, view_number }, null);
    // }




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
        MyToast.show_success('已复制秒传链接到剪切板');

    }





}