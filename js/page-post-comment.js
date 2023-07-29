const SMILES = {
    ':neutral:': 'icon_neutral.gif',
    ':???:': 'icon_confused.gif',
    ':mrgreen:': 'icon_mrgreen.gif',
    ':twisted:': 'icon_twisted.gif',
    ':arrow:': 'icon_arrow.gif',
    ':shock:': 'icon_eek.gif',
    ':smile:': 'icon_smile.gif',
    ':cool:': 'icon_cool.gif',
    ':evil:': 'icon_evil.gif',
    ':grin:': 'icon_biggrin.gif',
    ':idea:': 'icon_idea.gif',
    ':oops:': 'icon_redface.gif',
    ':razz:': 'icon_razz.gif',
    ':roll:': 'icon_rolleyes.gif',
    ':wink:': 'icon_wink.gif',
    ':cry:': 'icon_cry.gif',
    ':eek:': 'icon_surprised.gif',
    ':lol:': 'icon_lol.gif',
    ':mad:': 'icon_mad.gif',
    ':sad:': 'icon_sad.gif',
    '8-)': 'icon_01.gif',
    '8-O': 'icon_02.gif',
    ':-(': 'icon_03.gif',
    ':-)': 'icon_04.gif',
    ':-?': 'icon_05.gif',
    ':-D': 'icon_06.gif',
    ':-P': 'icon_07.gif',
    ':-o': 'icon_08.gif',
    ':-x': 'icon_09.gif',
    ':-|': 'icon_10.gif',
    ';-)': 'icon_11.gif',
    '8O': 'icon_eek.gif',
    ':(': 'icon_sad.gif',
    ':)': 'icon_smile.gif',
    ':?': 'icon_confused.gif',
    ':D': 'icon_biggrin.gif',
    ':P': 'icon_razz.gif',
    ':o': 'icon_surprised.gif',
    ':x': 'icon_mad.gif',
    ':|': 'icon_neutral.gif',
    ';)': 'icon_wink.gif',
    ':!:': 'icon_exclaim.gif',
    ':?:': 'icon_question.gif',
};


$(function () {


    /**
     * 如果在DOM中发现内页面类名
     */
    let $singlePage = $('body.single, body.page');

    if ($singlePage.length) {

        //输出评论图片到弹出框
        printEmoji($('.comments-part button.btn.emoji'));

        //屏蔽默认按钮提交事件
        $singlePage.find('.comments-part form.main-comment').on('submit', '', '', function (event) {
            event.preventDefault();
        });

        //表单发送按钮点击事件
        $singlePage.find('.comments-part form.main-comment button[type="submit"]').on('click', '', '', sendComment);


        //弹出框的表情图片点击事件
        // 插入对应图片代码到评论框
        $singlePage.on('click', '.popover img.smile', '', insertEmoji);

        //如果评论列表存在
        if ($singlePage.find('.comments-part  .get-next-page').length) {
            //窗口滚动事件
            //动态加载评论列表
            $(document).on('scroll', '', '', checkPreGetComment);

            //如果页面是直达评论列表的, 立即加载一次
            checkPreGetComment();
        }


        //回复按钮点击事件
        //移动评论框
        $singlePage.on('click', '.comments-part .respond-button', '', moveCommentForm);

        //取消回复按钮点击事件
        //重置评论框状态
        $singlePage.on('click', '.comments-part .reset-respond', '', resetCommentForm);

        //删除按钮点击事件
        //删除评论
        $singlePage.on('click', '.comments-part .delete-button', '', deleteComment);

        //点赞评论
        $singlePage.on('click', '.comments-part a.add-comment-likes', '', addCommentLikes);

        //踩评论
        $singlePage.on('click', '.comments-part a.delete-comment-likes', '', deleteCommentLikes);
    }

});

/**
 * 在弹出框按钮里增加弹出框内容
 * @param {JQuery} $button
 */
function printEmoji($button) {

    if ($button.length) {
        let content = '';
        for (let key in SMILES) {
            content += `<img class="m-1 p-1 smile "  src="${MY_SITE.home}/wp-content/themes/miku/img/smilies/${SMILES[key]}"  alt="${key}" skip_lazyload=""/>`;
        }
        

        //$button.attr('data-bs-content', content);

        //激活弹出框
        new bootstrap.Popover($button, {
            container : 'body',
            trigger : 'focus',
            placement: 'bottom',
            html: true,
            content
        });


    }
}

/**
 * 插入图片代码到评论框内
 *
 * @param {Event} event
 */
function insertEmoji(event) {

    let value = $(event.target).attr('alt');
    let $textarea = $('.comments-part form.main-comment textarea');

    $textarea.val(`${$textarea.val()} ${value} `);

}

/**
 *根据回复位置 移动评论框
 * @param {Event} event
 */
function moveCommentForm(event) {

    let $respondButton = $(event.target);
    let respond = $respondButton.data('respond');
    let respondName = $respondButton.data('respond-name');

    //获取评论框容器
    let $formContainer = $('.comments-part .comment-form-container');
    //设置内嵌边距
    $formContainer.addClass('ms-5');
    //修改表单里的回复对象
    $formContainer.find('input[name="comment_parent"]').val(respond);
    //隐藏通知UP主的表单选项
    $formContainer.find('input[name="notify_author"]').parent().hide();

    //修改发送按钮文字
    $formContainer.find('textarea').attr('placeholder', `回复 ${respondName} : `);

    //显示取消回复按钮
    $formContainer.find('button.reset-respond').show();

    //移动到对应位置下
    $respondButton.parents(`.comment-item-${respond}`).children('.children').after($formContainer);

    //评论框获取焦点
    $formContainer.find('textarea').trigger('focus');


}

/**
 * 重置评论表单状态和位置
 */
function resetCommentForm() {


    //获取评论框容器
    let $formContainer = $('.comments-part .comment-form-container');
    //设置内嵌边距
    $formContainer.removeClass('ms-5');
    //重置表单里的回复对象
    $formContainer.find('input[name="comment_parent"]').val(0);
    //显示通知UP主的表单选项
    $formContainer.find('input[name="notify_author"]').parent().show();
    //修改发送按钮文字
    $formContainer.find('textarea').attr('placeholder', `评价一下吧`);

    //隐藏取消回复按钮
    $formContainer.find('button.reset-respond').hide();


    //重置位置
    $(`.comments-part .comments-part-title`).after($formContainer);


    //评论框获取焦点
    $formContainer.find('textarea').trigger('focus');


}

/**
 * 发送评论
 * @param {Event}event
 */
function sendComment(event) {

    //获取按钮
    let $submitButton = $(event.target);
    //如果点击到的对象是按钮的子元素 切换回按钮
    if (!$submitButton.is('button')) {
        $submitButton = $submitButton.parent();
    }

    //获取下一页请求参数
    let $getNextPageElement = $('.comments-part .get-next-page');
    let offset = $getNextPageElement.data('offset');

    //获取所属表单
    let $form = $submitButton.parents('form.main-comment');
    let $textarea = $form.find('textarea[name="comment_content"]');
    let comment_content = $textarea.val().trim();
    let comment_post_ID = $form.find('input[name="comment_post_ID"]').val();
    let comment_parent = $form.find('input[name="comment_parent"]').val();

    if (!comment_content) {
        TOAST_SYSTEM.add("评论内容不能为空", TOAST_TYPE.error);
        return;
    }
    if (comment_content.length < 2) {
        TOAST_SYSTEM.add("评论内容太短了", TOAST_TYPE.error);
        return;
    }

    //请求参数
    let data = {
        comment_content,
        comment_post_ID,
        comment_parent,
    };

    //检测通知作者选项 是否被勾选了
    let notifyAuthorCheckBox = $form.find('input[name="notify_author"]');
    if (notifyAuthorCheckBox.is(":checked")) {
        data.notify_author = 1;
    }


    let $parentElement = $('.comments-part .comment-list');


    //切换按钮状态和显示内容
    $submitButton.children().toggle('fast');
    $submitButton.toggleDisabled();


    //成功的情况
    let successCallback = function (response) {
        //检测是否错误
        if (response instanceof Object) {


            //如果是在回复 (子评论)
            let depth;
            let $parentElement;
            if (comment_parent > 0) {
                depth = true;
                $parentElement = $(`.comments-part .comment-list .comment-item-${comment_parent} > .children`);
            }
            else {
                depth = false;
                $parentElement = $(`.comments-part .comment-list`);
            }


            //创建评论实例
            let newComment = new MyComment(response, depth, true);

            //插入到页面中
            $parentElement.prepend(newComment.toHTML());

            //清空评论框内容
            $textarea.val('');

            //更新下次请求的offset
            offset = parseInt(offset) + 1;
            $getNextPageElement.data('offset', offset);


        }
        else {
            failCallback();
        }
    };

    //错误的情况
    let failCallback = function (jqXHR) {

        let errorText = '发送错误';
        if (jqXHR.hasOwnProperty('responseJSON') && isWpError(jqXHR.responseJSON)) {
            if (jqXHR.responseJSON.code === 'comment_duplicate') {
                errorText = '检测到重复评论，您似乎已经提交过这条评论了!';
            }
            else if (jqXHR.responseJSON.code === 'comment_flood') {
                errorText = '您提交评论的速度太快了，请稍后再发表评论!';
            }

        }

        TOAST_SYSTEM.add(errorText, TOAST_TYPE.error);

    };

    let completeCallback = function () {

        //恢复按钮状态和显示内容
        $submitButton.children().toggle('fast');
        //设置延时3秒恢复按钮, 避免评论刷屏
        setTimeout(function () {
            $submitButton.toggleDisabled();
        }, 3000);


    };


    $.ajax({
        url: URLS.commentList,
        data,
        type: HTTP_METHOD.post,
        headers: createAjaxHeader()
    }).done(successCallback).fail(failCallback).always(completeCallback);


}


/**
 * 检测评论列表底部是否可见
 * 如果可见 就获取下一页评论
 */
function checkPreGetComment() {

    let $getNextPageElement = $('.comments-part .get-next-page');
    if (isVisibleOnScreen($getNextPageElement)) {
        getComment();
    }


}


/**
 * 获取评论
 */
function getComment() {
    //存储自己
    let self = this;

    //如果正在加载 或者 没有更多
    if (self.loading || self.notMore) {
        return;
    }

    let $commentListElement = $('.comments-part .comment-list');


    let $getNextPageElement = $('.comments-part .get-next-page');
    //显示进度条
    $getNextPageElement.children().fadeToggle('fast');
    //开启信号标
    self.loading = true;


    let postId = $getNextPageElement.data('post-id');
    let offset = $getNextPageElement.data('offset');
    //请求的数量
    let number = 20;

    //请求参数
    let data = { post_id: postId, offset, number };

    //成功的情况
    let successCallback = function (response) {

        //确保不是空内容
        if (isNotEmptyArray(response)) {
            //创建评论列表类
            let commentList = new MyCommentList();
            //根据类型名称 转换成对应的评论类型
            commentList.add(response);

            //插入到页面中
            $commentListElement.append(commentList.toHTML());

            //更新下次请求的offset
            offset = parseInt(offset) + response.length;
            $getNextPageElement.data('offset', offset);

        }
        else {
            notMoreCallback();
        }

    };

    //没有更多内容的情况
    let notMoreCallback = function () {
        //设置错误信号标
        self.notMore = true;
    };


    let completeCallback = function () {
        //隐藏进度条
        $getNextPageElement.children().fadeToggle('fast');
        //关闭信号标
        self.loading = false;
    };

    $.ajax({
        url: URLS.commentList,
        data,
        type: HTTP_METHOD.get,
        headers: createAjaxHeader()
    }).done(successCallback).fail(defaultFailCallback).always(completeCallback);


}


/**
 * 删除评论
 * @param {Event} event
 */
function deleteComment(event) {


    //获取删除事件按钮
    let $deleteButton = $(event.target);

    //获取下一页请求参数
    let $getNextPageElement = $('.comments-part .get-next-page');
    let offset = $getNextPageElement.data('offset');

    //获取所属表单
    let commentId = $deleteButton.data('comment-id');
    let loading = $deleteButton.data('loading');


    //如果正在删除
    if (loading) {
        return;
    }

    if (!confirm('确认要删除吗?')) {
        return;
    }

    $deleteButton.data('loading', true);
    $deleteButton.html('删除中...');

    //成功的情况
    let successCallback = function (response) {

        $deleteButton.parents(`.comment-item-${commentId}`).hide();
        TOAST_SYSTEM.add('删除成功', TOAST_TYPE.success);

        //更新下次请求的offset
        offset = parseInt(offset) > 0 ? parseInt(offset) - 1 : 0;
        $getNextPageElement.data('offset', offset);

    };

    //错误的情况
    let failCallback = function () {
        TOAST_SYSTEM.add('删除失败', TOAST_TYPE.error);
    };

    let completeCallback = function () {

        $deleteButton.removeData('loading');
        $deleteButton.html('删除');

    };


    $.ajax({
        url: URLS.comments + '/' + commentId,
        type: HTTP_METHOD.delete,
        headers: createAjaxHeader()
    }).done(successCallback).fail(failCallback).always(completeCallback);

}

/**
 * 点赞评论
 */
function addCommentLikes() {

    //获取触发事件的按钮
    let $button = $(this);
    //获取评论ID
    let comment_id = $button.closest('.comment-item').data('comment_id');
    //注销按钮
    $button.removeClass('add-comment-likes').addClass('text-miku disabled');
    //存储评论ID到已点赞
    addArrayElementToLocalStorage(LOCAL_STORAGE_KEY.commentLikes, comment_id);

    //更新点赞次数
    let $countElement = $button.siblings('.comment-likes-count');
    let count = parseInt($countElement.html()) + 1;
    $countElement.html(count);

    //不需要等待回复
    $.ajax({
        url: URLS.commentLikes,
        data: {
            comment_id,
        },
        type: HTTP_METHOD.post,
        headers: createAjaxHeader()
    });

}

/**
 * 踩评论 (减少点赞)
 */
function deleteCommentLikes() {

    //获取触发事件的按钮
    let $button = $(this);
    //获取评论ID
    let comment_id = $button.closest('.comment-item').data('comment_id');
    //注销按钮
    $button.removeClass('delete-comment-likes').addClass('text-miku disabled');
    //存储评论ID到已踩
    addArrayElementToLocalStorage(LOCAL_STORAGE_KEY.commentDislikes, comment_id);

    //更新点赞次数
    let $countElement = $button.siblings('.comment-likes-count');
    let count = parseInt($countElement.html()) - 1;
    $countElement.html(count);

    //不需要等待回复
    $.ajax({
        url: URLS.commentLikes,
        data: {
            comment_id,
        },
        type: HTTP_METHOD.delete,
        headers: createAjaxHeader()
    });

}


