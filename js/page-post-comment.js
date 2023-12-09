/// <reference path="common/base.js" />
/// <reference path="common/constant.js" /> 
/// <reference path="class/class-comment.js" /> 


let $single_page_comments_part;


$(function () {


    /**
     * 如果在DOM中发现内页面类名
     */
    $single_page_comments_part = $('body.single .comments-part, body.page .comments-part');

    if ($single_page_comments_part.length) {

        //弹出框的表情图片点击事件
        // 插入对应图片代码到评论框
        $('body').on('click', '.popover .emoji_container img', '', function () {
            const emoji_value = $(this).attr('alt');
            insert_emoji(emoji_value);
        });

        //屏蔽默认按钮提交事件
        $single_page_comments_part.on('submit', 'form.comment_form', '', function (event) {
            event.preventDefault();
            insert_comment($(this));
        });

        //窗口滚动事件
        //动态加载评论列表
        $(document).on('scroll', '', '', get_comment_list_when_visible);


        //回复按钮点击事件
        //移动评论框
        $single_page_comments_part.on('click', '.respond-button', '', function () {
            create_response_form($(this));
        });

        //取消回复按钮点击事件
        //重置评论框状态
        $single_page_comments_part.on('click', '.reset_respond', '', function () {
            delete_response_form($(this));
        });

        //删除按钮点击事件
        //删除评论
        $single_page_comments_part.on('click', '.delete-button', '', function () {
            delete_comment($(this));
        });

        //点赞评论
        $single_page_comments_part.on('click', '.add-comment-likes', '', function () {
            add_comment_likes($(this));
        });

        //踩评论
        $single_page_comments_part.on('click', '.delete-comment-likes', '', function () {
            delete_comment_likes($(this));
        });


        //给按键绑定创建表情弹出框
        create_emoji_popover($single_page_comments_part.find('.open_emoji_popover'));


        //如果页面是直达评论列表的, 立即加载一次
        get_comment_list_when_visible();

    }

});

/**
 * 在弹出框按钮里增加弹出框内容
 * @param {jQuery}
 */
function create_emoji_popover($emoji_button) {

    if ($emoji_button.length) {

        const emoji_path = MY_SITE.home + '/wp-content/themes/miku/img/smilies/';

        let content = '<div class="emoji_container">';
        for (const key in COMMENT_SMILES) {
            content += `
                <img class="emoji m-1 p-1 cursor_pointer" src="${emoji_path}${COMMENT_SMILES[key]}" alt="${key}" skip_lazyload />`;
        }
        content += '</div>';

        //激活弹出框
        new bootstrap.Popover($emoji_button, {
            container: 'body',
            trigger: 'focus',
            placement: 'bottom',
            html: true,
            content
        });

    }
}

/**
 * 插入表情代码到评论框内
 *
 * @param {String} emoji_value
 */
function insert_emoji(emoji_value) {

    const $textarea = $single_page_comments_part.find('form.comment_form textarea');
    const text = $textarea.val();

    $textarea.val(`${text} ${emoji_value} `);

}



/**
 * 检测评论列表底部是否可见
 * 如果可见 就获取下一页评论
 */
function get_comment_list_when_visible() {
    let $getNextPageElement = $single_page_comments_part.find('.comment-list-end');
    if (isVisibleOnScreen($getNextPageElement)) {
        get_comment_list();
    }


}


/**
 * 获取评论
 */
function get_comment_list() {

    //存储自己
    let self = this;

    //如果正在加载 或者 没有更多
    if (get_comment_list.is_loading || get_comment_list.is_end) {
        return;
    }

    //开启信号标
    get_comment_list.is_loading = true;


    const $comment_list_element = $single_page_comments_part.find('.comment-list');

    const post_id = $comment_list_element.data('post-id');
    const offset = $comment_list_element.data('offset');

    //请求参数
    let data = {
        post_id,
        offset
    };

    //成功的情况
    const success_callback = (response) => {

        //确保不是空内容
        if (isNotEmptyArray(response)) {

            //创建评论列表类
            const commentList = new MyCommentList();
            //根据类型名称 转换成对应的评论类型
            commentList.add(response);

            //插入到页面中
            $comment_list_element.append(commentList.toHTML());

            //更新下次请求的offset
            $comment_list_element.data('offset', parseInt(offset) + response.length);


        }
        else {
            MyToast.show_success('无更多评论');
            //设置end属性, 避免继续尝试
            get_comment_list.is_end = true;

            //如果列表原本就是空的
            if ($comment_list_element.children().length === 0) {
                //显示无结果
                show_not_found_row($comment_list_element);
            }
        }

    };

    send_get(
        URLS.commentList,
        data,
        () => {
            show_loading_row($single_page_comments_part);
        },
        success_callback,
        defaultFailCallback,
        () => {
            hide_loading_row();
            //关闭loading属性
            get_comment_list.is_loading = false;
        }

    );

}


/**
 * 发送评论
 * @param {jQuery} $form
 */
function insert_comment($form) {

    const $comment_list_element = $single_page_comments_part.find('.comment-list');

    //获取下一页请求参数
    const offset = $comment_list_element.data('offset');

    const $textarea = $form.find('textarea[name="comment_content"]');
    const comment_content = $textarea.val().trim();
    const comment_post_ID = parseInt($form.data('comment_post_id'));
    const comment_parent = parseInt($form.data('comment_parent'));

    if (!comment_content) {
        MyToast.show_error("评论内容不能为空");
        return;
    }
    if (comment_content.length < 2) {
        MyToast.show_error("评论内容不能少于2个字符");
        return;
    }

    //请求参数
    const data = {
        comment_content,
        comment_post_ID,
        comment_parent,
    };

    //检测通知作者选项 是否被勾选了
    const $notify_author_check_box = $form.find('input[name="notify_author"]');
    if ($notify_author_check_box.is(":checked")) {
        data.notify_author = 1;
    }


    //成功的情况
    const success_callback = function (response) {
        //检测是否错误
        if (response instanceof Object) {

            let depth;
            let $parentElement;

            //如果是在回复 (子评论)
            if (comment_parent > 0) {
                depth = true;
                $parentElement = $(`.comments-part .comment-list .comment-item-${comment_parent} > .children`);
            }
            else {
                depth = false;
                $parentElement = $comment_list_element;
            }

            //创建评论实例
            const new_comment_model = new MyComment(response, depth, true);

            //插入到页面中
            $parentElement.prepend(new_comment_model.toHTML());

            //清空评论框内容
            $textarea.val('');

            //更新下次请求的offset
            $comment_list_element.data('offset', parseInt(offset) + 1);

            MyToast.show_success('评论发送成功');

            //如果是在回复其他人
            if (comment_parent > 0) {
                //删除对应的回复评论框
                delete_response_form($form);
            }


        }
        else {
            failCallback();
        }
    };

    //错误的情况
    const fail_callback = function (jqXHR) {

        let errorText = '评论发送错误';

        //如果存在wpError对象
        const wpError = getWpErrorByJqXHR(jqXHR);
        if (wpError) {

            if (wpError.code === 'comment_duplicate') {
                errorText = '检测到重复评论，你似乎已经提交过这条评论了!';
            }
            else if (wpError.code === 'comment_flood') {
                errorText = '发送评论的速度太快了，请稍后再发表评论!';
            }
            else if (wpError.code === 'comment_custom_span_filter') {
                errorText = '评论需要包含中文!';
            }
            else if (wpError.data) {
                errorText = wpError.data;
            }
        }

        MyToast.show_error(errorText);

    };



    send_post(
        URLS.commentList,
        data,
        () => {
            show_loading_modal();
        },
        success_callback,
        fail_callback,
        () => {

            hide_loading_modal();

            // //恢复按钮状态和显示内容
            // $submitButton.children().toggle('fast');
            // //设置延时3秒恢复按钮, 避免评论刷屏
            // setTimeout(function () {
            //     $submitButton.toggleDisabled();
            // }, 3000);

        }
    );


}

/**
 *根据回复位置 创建回复评论框
 * @param {jQuery} $button
 */
function create_response_form($button) {

    //删除其他所有的回复评论框
    $single_page_comments_part.find('.comment-item .child-form-container').remove();

    const respond = $button.data('respond');
    const respond_name = $button.data('respond-name');

    //获取评论框容器
    const $form_container = $single_page_comments_part.find('.comment-form-container.main-form-container');
    const $new_form_container = $form_container.clone();

    //设置内嵌边距
    $new_form_container.addClass('child-form-container ms-sm-5').removeClass('main-form-container');

    //修改表单里的回复对象数据
    $new_form_container.find('form.comment_form').data('comment_parent', respond);
    //隐藏通知UP主的选项容器
    $new_form_container.find('.notify_author_check_container').hide();

    //修改评论框的默认占位符
    $new_form_container.find('textarea').attr('placeholder', `回复 ${respond_name} : `);

    //给新的表情按钮创建弹出框
    create_emoji_popover($new_form_container.find('.open_emoji_popover'));

    //显示取消回复按钮
    $new_form_container.find('.reset_respond_container').addClass('d-sm-block').show();

    //移动到对应位置下
    $button.closest(`.comment-item-${respond}`).children('.children').after($new_form_container);

    //评论框获取焦点
    $new_form_container.find('textarea').trigger('focus');


}

/**
 * 删除回复评论框
 * @param {jQuery} $button
 */
function delete_response_form($button) {

    const $form_container = $button.closest('.comment-form-container');
    $form_container.remove();

}

/**
 * @deprecated
 * 重置评论表单状态和位置
 */
function reset_comment_form_position() {


    //获取评论框容器
    const $form_container = $single_page_comments_part.find('.comment-form-container');
    //设置内嵌边距
    $form_container.removeClass('ms-5');

    //重置表单里的回复对象
    $form_container.find('form.comment_form').data('comment_parent', 0);
    //显示通知UP主的选项
    $form_container.find('.notify_author_check_container').show();
    //修改发送按钮文字
    $form_container.find('textarea').attr('placeholder', `输入评论`);

    //隐藏取消回复按钮
    $form_container.find('.reset_respond_container').hide();

    //重置位置
    $single_page_comments_part.find(`.comments-part-title`).after($form_container);

    //评论框获取焦点
    // $form_container.find('textarea').trigger('focus');


}




/**
 * 删除评论
 * @param {jQuery} $button
 */
function delete_comment($button) {

    const $comment_list_element = $single_page_comments_part.find('.comment-list');

    //获取下一页请求参数
    const offset = $comment_list_element.data('offset');

    //获取所属表单
    const comment_id = $button.data('comment-id');

    open_confirm_modal('确认要删除该评论吗?', '', () => {

        send_delete(
            URLS.comments + '/' + comment_id,
            {},
            () => {
                show_loading_modal();
            },
            (response) => {
                //删除对应评论
                $button.closest(`.comment-item-${comment_id}`).hide();
                MyToast.show_success('删除评论成功');

                //更新下次请求的offset
                $comment_list_element.data('offset', parseInt(offset) > 0 ? parseInt(offset) - 1 : 0);
            },
            () => {
                MyToast.show_error('删除评论失败');
            },
            () => {
                hide_loading_modal();
            },
        )

    });

}

/**
 * 点赞评论
 * @param {jQuery} $button
 */
function add_comment_likes($button) {

    //获取评论ID
    const comment_id = $button.closest('.comment-item').data('comment_id');
    //注销按钮
    $button.removeClass('add-comment-likes').addClass('text-miku disabled');
    //存储评论ID到已点赞
    addArrayElementToLocalStorage(LOCAL_STORAGE_KEY.commentLikes, comment_id);

    //更新点赞次数
    const $countElement = $button.siblings('.comment-likes-count');
    const count = parseInt($countElement.html()) + 1;
    $countElement.html(count);

    send_post(
        URLS.commentLikes,
        {
            comment_id,
        },
    );


}

/**
 * 踩评论 (减少点赞)
 * @param {jQuery} $button
 */
function delete_comment_likes($button) {


    //获取评论ID
    const comment_id = $button.closest('.comment-item').data('comment_id');
    //注销按钮
    $button.removeClass('delete-comment-likes').addClass('text-miku disabled');
    //存储评论ID到已踩
    addArrayElementToLocalStorage(LOCAL_STORAGE_KEY.commentDislikes, comment_id);

    //更新点赞次数
    const $countElement = $button.siblings('.comment-likes-count');
    const count = parseInt($countElement.html()) - 1;
    $countElement.html(count);

    send_delete(
        URLS.commentLikes,
        {
            comment_id,
        },
    );

}


