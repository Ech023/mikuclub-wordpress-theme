/// <reference path="common/base.js" />
/// <reference path="common/constant.js" /> 


/**
 * 文章相关的JS函数
 */


$(function () {

    $('body.single, body.page').on('click', 'button.draft_post', '', function () {
        const post_id = $(this).data('post-id');
        draft_post(post_id);
    });

    //在文章详情页内删除文章
    $('body.single').on('click', 'button.delete_post', '', function () {

        const post_id = $(this).data('post-id');
        delete_post(post_id, () => {
            //跳转回投稿管理页面
            // location.href = `${MY_SITE.home}/up_home_page`;
        });
    });

    //在投稿编辑页面删除文章
    $('body.page .page-tougao').on('click', 'button.delete_post', '', function () {

        const post_id = $(this).data('post-id');
        delete_post(post_id, () => {
            //跳转回投稿管理页面
            location.href = `${MY_SITE.home}/up_home_page`;
        });

    });

    //在投稿管理页面删除文章
    $('body.page .page-uploader').on('click', 'button.delete_post', '', function () {

        const post_id = $(this).data('post-id');
        delete_post(post_id, () => {

            const $grandparentElement = $(this).parent().parent();
            //隐藏文章容器
            $grandparentElement.fadeOut(300);

        });

    });

    //在下载失效页面删除文章
    $('body.page .page-fail-down-list').on('click', 'button.delete_post', '', function () {

        const post_id = $(this).data('post-id');
        delete_post(post_id, () => {

            const $parentItem = $(this).closest('.list-item');
            //更新元素的背景颜色
            $parentItem.css('background-color', '#ccc');

        });

    });


    //在文章详情页 和 投稿编辑页面退回文章
    $('body.single, body.page .page-tougao').on('click', 'button.reject_post', '', function () {

        const post_id = $(this).data('post-id');
        reject_post(post_id);

    });

    //在文章详情页 和 投稿编辑页面退回文章
    $('body.page .page-fail-down-list').on('click', 'button.reject_post', '', function () {

        const post_id = $(this).data('post-id');
        reject_post(post_id, '下载地址失效', () => {

            const $parentItem = $(this).closest('.list-item');
            //设置背景颜色
            $parentItem.css('background-color', '#ccc');

        });

    });


});





/**
 * 撤回文章
 * @param {number} post_id
 */
function draft_post(post_id) {

    open_confirm_modal('确认要把稿件转为草稿状态吗?', '撤回后需要重新提交审核才会再次公开显示', () => {

        const data = {
            post_id,
        };

        send_post(
            URLS.draftPost,
            data,
            () => {
                show_loading_modal();
            },
            () => {
                MyToast.show_success('撤回成功, 请到投稿管理页面查看');
            },
            defaultFailCallback,
            () => {
                hide_loading_modal();
            }
        );

    });
}


/**
 * 删除投稿
 * @param {number} post_id
 * @param {function} success_callback
 */
function delete_post(post_id, success_callback = null) {

    open_confirm_modal('确认要删除该投稿吗?', '', () => {

        send_delete(
            URLS.posts + '/' + post_id,
            {},
            () => {
                show_loading_modal();
            },
            () => {
                MyToast.show_success('删除成功');
                if (isFunction(success_callback)) {
                    success_callback();
                }
            },
            defaultFailCallback,
            () => {
                hide_loading_modal();
            }
        );

    });

}

/**
 * 驳回投稿
 * @param {number} post_id
 * @param {string} cause
 * @param {function} success_callback
 */
function reject_post(post_id, cause = '', success_callback = null) {

    const send_reject_post = (value) => {

        //如果还是空的就中断运行
        if (!value) {
            MyToast.show_error('退稿原因不能为空');
            return;
        }

        const data = {
            post_id,
            cause: value,
        };

        send_post(
            URLS.rejectPost,
            data,
            () => {
                show_loading_modal();
            },
            () => {
                MyToast.show_success('退稿成功');
                if (isFunction(success_callback)) {
                    success_callback();
                }
            },
            defaultFailCallback,
            () => {
                hide_loading_modal();
            }
        );
    }

    //如果没有提供, 就显示弹窗手动输入
    if (!cause) {
        open_prompt_modal('退稿原因', '下载地址失效', (value) => {

            send_reject_post(value);
        });
    }
    //否则直接发送退稿请求
    else {
        send_reject_post(cause);
    }

}

