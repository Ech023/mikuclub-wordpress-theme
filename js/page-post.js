/// <reference path="common/base.js" />
/// <reference path="function.js" />
/// <reference path="function-ajax.js" />


$(function () {

    /**
     * 如果在DOM中发现内页面类名
     */
    let $singlePage = $('body.single');
    if ($singlePage.length) {

        //绑定密码表单 点击事件
        $('.download-part input').on('click', function () {
            copy_to_clipboard($(this));
        });
        $('.download-part a.download').on('click', function () {
            find_access_password($(this));
        });

        //绑定功能按钮 点击事件
        $('.functional-part button.set-post-like').on('click', function () {
            set_post_like($(this));
        });
        $('.functional-part button.set-post-unlike').on('click', function () {
            set_post_unlike($(this));
        });
        $('.functional-part button.set-post-favorite').on('click', function () {
            set_post_favorite($(this));
        });
        $('.functional-part .post-share a.dropdown-item').on('click', function () {
            set_post_share();
        });
        $('.functional-part button.set-post-fail-times').on('click', function () {
            set_post_fail_time($(this));
        });

        //更改按钮状态
        init_single_functional_buttons();

        //初始化图片灯箱 自定义配置
        lightbox.option({
            'albumLabel': "第 %1 张 / 总共 %2 张",
            wrapAround: true,
            disableScrolling: true
        });

    }
});


/**
 * 根据本地储存的数据 变更功能按钮状态
 */
function init_single_functional_buttons() {

    const $functional_part = $('.functional-part');

    const $like_button = $functional_part.find('button.set-post-like');
    const $unlike_button = $functional_part.find('button.set-post-unlike');
    const $favorite_button = $functional_part.find('button.set-post-favorite');

    const $share_button = $functional_part.find('button.set-post-share');
    const $fail_time_button = $functional_part.find('button.set-post-fail-times');

    const post_id = $like_button.data('post-id');

    let postLike = getLocalStorage(LOCAL_STORAGE_KEY.postLike);
    let postUnLike = getLocalStorage(LOCAL_STORAGE_KEY.postUnLike);
    let postShares = getLocalStorage(LOCAL_STORAGE_KEY.postShares);
    let postFailTimes = getLocalStorage(LOCAL_STORAGE_KEY.postFailTimes);

    let postFavorites = MY_SITE.favorite_post;

    if (postLike && postLike.includes(post_id)) {
        update_single_functional_button($like_button, '已好评', 'btn-light-2', 'btn-dark-1', 0, true);
    }
    if (postUnLike && postUnLike.includes(post_id)) {
        update_single_functional_button($unlike_button, '已差评', 'btn-light-2', 'btn-dark-1', 0, true);
    }

    if (postFavorites && postFavorites.includes(post_id)) {
        update_single_functional_button($favorite_button, '已收藏', 'btn-light-2', 'btn-dark-1', 0, true);
    }

    if (postShares && postShares.includes(post_id)) {
        update_single_functional_button($share_button, '已分享', 'btn-light-2', 'btn-dark-1', 0, true);
    }

    if (postFailTimes && postFailTimes.includes(post_id)) {
        update_single_functional_button($fail_time_button, '已反馈', 'btn-light-2', 'btn-dark-1', 0, true);
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
 * @param {boolean} is_activated 是否激活

 */
function update_single_functional_button($button, text, oldClass, newClass, addCount, is_activated) {

    let count = parseInt($button.children('span.count').html()) + addCount;
    $button.children('span.text').html(text);
    $button.children('span.count').html(count);
    $button.removeClass(oldClass).addClass(newClass);

    //如果是已激活状态, 增加激活数据
    if (is_activated) {
        $button.data('activated', 1);
    }
    //否则移除数据
    else {
        $button.removeData('activated');
    }


}



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
 * @param {jQuery} $button
 */
function set_post_like($button) {

    const post_id = $button.data('post-id');
    const is_activated = $button.data('activated');

    const storage_key = LOCAL_STORAGE_KEY.postLike;

    const data = {
        post_id,
    };
    //如果是已激活状态
    if (is_activated) {
        data.cancel = 1;
    }


    //成功的情况
    const successCallback = (response) => {

        if (!is_activated) {
            addArrayElementToLocalStorage(storage_key, post_id);
            update_single_functional_button($button, '已好评', 'btn-light-2', 'btn-dark-1', 1, true);
            MyToast.show_success('好评成功');
        }
        else {
            deleteArrayElementFromLocalStorage(storage_key, post_id);
            update_single_functional_button($button, '好评', 'btn-dark-1', 'btn-light-2', -1, false);
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
            $button.toggleDisabled();
        },
        //成功后运行
        successCallback,
        //错误后运行
        defaultFailCallback,
        //请求解锁后运行
        () => {
            $button.toggleDisabled();
        }
    );

}

/**
 * 添加/取消差评
 * @param {jQuery} $button
 */
function set_post_unlike($button) {

    const post_id = $button.data('post-id');
    const is_activated = $button.data('activated');

    const storage_key = LOCAL_STORAGE_KEY.postUnLike;

    const data = {
        post_id
    };
    //如果是已激活状态
    if (is_activated) {
        data.cancel = 1;
    }

    //成功的情况
    const successCallback = (response) => {

        if (!is_activated) {
            addArrayElementToLocalStorage(storage_key, post_id);
            update_single_functional_button($button, '已差评', 'btn-light-2', 'btn-dark-1', 1, true);
            MyToast.show_success('差评成功');
        }
        else {
            deleteArrayElementFromLocalStorage(storage_key, post_id);
            update_single_functional_button($button, '差评', 'btn-dark-1', 'btn-light-2', -1, false);
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
            $button.toggleDisabled();
        },
        //成功后运行
        successCallback,
        //错误后运行
        defaultFailCallback,
        //请求解锁后运行
        () => {
            $button.toggleDisabled();
        }
    );

}

/**
 * 根据好评和差评数量更新文章评价等级
 */
function update_post_feedback_rank() {

    const $functional_part = $('.functional-part');

    let post_like_count = $functional_part.find('button.set-post-like .count').html();
    let post_unlike_count = $functional_part.find('button.set-post-unlike .count').html();

    const rank = POST_FEEDBACK_RANK.get_rank(post_like_count, post_unlike_count);

    //更新评价等级
    $functional_part.find('.post_feedback_rank').html(rank);
}

/**
 * 设置收藏
 * @param {jQuery} $button
 */
function set_post_favorite($button) {

    const post_id = $button.data('post-id');
    const is_activated = $button.data('activated');

    //let storage_key = LOCAL_STORAGE_KEY.postFavorites;

    const data = {
        post_id
    };

    //请求方式  如果是已激活状态 改成 delete 取消收藏, 或者 默认为 post 添加收藏,
    const requestMethod = is_activated ? HTTP_METHOD.delete : HTTP_METHOD.post;

    //注销按钮
    $button.toggleDisabled();

    //成功的情况
    const successCallback = () => {

        if (!is_activated) {
            //addArrayElementToLocalStorage(storage_key, post_id);
            update_single_functional_button($button, '已收藏', 'btn-light-2', 'btn-dark-1', 1, true);
            MyToast.show_success('收藏成功');
        }
        else {
            //deleteArrayElementFromLocalStorage(storage_key, post_id);
            update_single_functional_button($button, '收藏', 'btn-dark-1', 'btn-light-2', -1, false);
            MyToast.show_success('已取消收藏');
        }

    };

    const completeCallback = function () {
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
 */
function set_post_share() {

    //获取按钮
    const $button = $('.functional-part button.set-post-share');
    const post_id = $button.data('post-id');
    const is_activated = $button.data('activated');

    //如果是已激活状态
    if (is_activated) {
        //避免重复上报
        //退出
        return;
    }

    const storage_key = LOCAL_STORAGE_KEY.postShares;

    const data = {
        post_id
    };

    //成功的情况
    const successCallback = (response) => {
        addArrayElementToLocalStorage(storage_key, post_id);
        update_single_functional_button($button, '已分享', 'btn-light-2', 'btn-dark-1', 1, true);
    };

    send_get(
        URLS.postShare,
        data,
        () => {
            //注销按钮
            $button.toggleDisabled();
        },
        successCallback,
        defaultFailCallback,
        () => {
            //激活按钮
            $button.toggleDisabled();
        }
    );

}


/**
 * 设置失效次数
 * @param {jQuery} $button
 */
function set_post_fail_time($button) {

    const post_id = $button.data('post-id');
    const is_activated = $button.data('activated');

    //如果是已激活状态
    if (is_activated) {
        //避免重复上报
        //退出
        return;
    }

    const storage_key = LOCAL_STORAGE_KEY.postFailTimes;

    const data = {
        post_id
    };


    open_confirm_modal('确认要反馈下载地址失效吗?', '管理员会根据总体的反馈次数, 退回稿件并通知UP主下载已失效', () => {

        //成功的情况
        const successCallback = (response) => {

            addArrayElementToLocalStorage(storage_key, post_id);
            update_single_functional_button($button, '已反馈', 'btn-light-2', 'btn-dark-1', 1, true);
            MyToast.show_success('反馈成功');

        };

        send_get(
            URLS.failDown,
            data,
            () => {
                //注销按钮
                $button.toggleDisabled();
            },
            successCallback,
            defaultFailCallback,
            () => {
                //激活按钮
                $button.toggleDisabled();
            }
        );

    });
}



/**
 * 增加文章点击数
 * @param post_id
 */
function add_post_views(post_id) {

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
