/// <reference path="common/base.js" />
/// <reference path="function-ajax.js" />
/// <reference path="class/class-comment.js" />
/// <reference path="class/class-message.js" />
/// <reference path="class/class-modal.js" />
/// <reference path="class/class-post.js" />
/// <reference path="class/class-toast.js" />
/// <reference path="class/class-ua-parser.js" />
/// <reference path="class/class-user.js" />

/// <reference path="function-float-menu-bar.js" />




/**
 * 和模态窗相关的函数
 * 
 */

$(function () {

    /**
     * 创建私信窗口按钮 点击事件
     * 创建显示模态窗
     */
    $('body').on('click', 'button.open_private_message_modal', '', function () {
        const recipient_id = $(this).data('recipient_id');
        const recipient_name = $(this).data('recipient_name');
        new MyPrivateMessageModal(recipient_id, recipient_name).create().show();
    });


    /**
     * 在线播放按钮 点击事件
     * 创建视频模态窗
     */
    $('body').on('click', 'button.open_video_modal', '', function () {

        const value = $(this).val();
        const video_type = $(this).data('video-type');
        const post_id = $(this).data('post-id');
        open_video_modal(value, video_type, post_id);

    });


    /**
    * 头像上传 change事件
    * 创建头像裁剪模态窗
    */
    $('body').on('change', 'input.open_image_cropper_modal[type="file"]', '', function () {
        open_image_cropper_modal($(this));
    });


    $('body').on('click', 'button.open_post_report_modal', '', function () {

        const post_id = $(this).data('post-id');

        new MyPostReportModal(post_id).create().show();

    });


    /**
    * 创建私信窗口按钮 点击事件
    * 创建显示模态窗
    */
    $('body').on('click', 'button.open_change_paged_modal', '', function () {

        const data = get_post_list_component_paged_and_max_num_pages();
        const paged = data.paged;
        const max_num_pages = data.max_num_pages;

        new ChangePagedModal(paged, max_num_pages).create().show();
    });


});



/**
 * 显示加载模态窗
 * @returns {void}
 */
function show_loading_modal() {
    new MyLoadingModal().create().show();
}

/**
 * 隐藏加载模态窗
 * @returns {void}
 */
function hide_loading_modal() {
    //关闭模态窗
    $('.modal.loading-modal').modal('hide');
}

/**
 * 显示确认模态窗
 * @param {string} text 需要确认的标题
 * @param {string} text_secondary 需要确认的副标题
 * @param {function|null} confirm_callback 确认触发的回调
 * @param {function|null} cancel_callback 确认触发的回调
 * @returns {void}
 */
function open_confirm_modal(text, text_secondary, confirm_callback, cancel_callback = null) {
    new ConfirmModal(text, text_secondary).create(confirm_callback, cancel_callback).show();
}

/**
 * 打开视频播放窗口
 * 
 * @param {string} value
 * @param {string} video_type
 * @param {number|undefined} post_id
 */
function open_video_modal(value, video_type, post_id = undefined) {

    //如果不是BILIBILI视频, 直接打开模态窗
    if (video_type !== VIDEO_TYPE.bilibili) {

        //解义url字符串
        value = decodeURIComponent(value.replace(/\+/g, ' '));

        //创建打开模态窗
        new MyVideoModal(value).create().show();

    }
    //如果是b站视频, 需要先获取CID号
    else {

        //请求参数
        const data = {
            post_id,
        };

        //如果是旧AV号
        if (value.includes('av')) {
            data.aid = value.slice(2);
        }
        else {
            data.bvid = value;
        }

        const success_callback = function (response) {

            const url = 'https://player.bilibili.com/player.html?' + $.param({
                aid: response.aid,
                bvid: response.bvid,
                cid: response.cid,
                page: 1,
                danmaku: 1,
                autoplay: 1,
                //high_quality : 1,
            });
            const iframe_code = '<iframe src="' + url + '" allowfullscreen></iframe>';

            // iframeCode = '<iframe src="//player.bilibili.com/player.html?aid=' + response.aid + '&bvid=' + response.bvid + '&cid=' + response.cid + '&page=1&high_quality=1&danmaku=1" allowfullscreen="true"></iframe>';
            //创建打开模态窗
            new MyVideoModal(iframe_code).create().show();

        };

        send_get(
            URLS.bilibili,
            data,
            () => {
                show_loading_modal();
            },
            success_callback,
            defaultFailCallback,
            () => {
                hide_loading_modal();
            }
        );
    }

}

/**
   * 
   * @param {jQuery} $input_file_element 
   * @returns 
   */
function open_image_cropper_modal($input_file_element) {

    if (!window.File || !window.FileReader || !window.FileList || !window.Blob) {
        MyToast.show_error('当前的浏览器不支持JS文件API');
        return;
    }

    //如果未选中文件
    if (!$input_file_element.prop('files').length) {
        MyToast.show_error('未选中正确文件');
        return;
    }

    //获取文件对象
    let file = $input_file_element.prop('files')[0];
    //创建阅读器
    let fileReader = new FileReader();
    //阅读完毕后 回调
    fileReader.onload = function () {
        //显示剪切图片模态窗
        new MyImageCropperModal(fileReader.result).create().show();

        //清空数据
        $input_file_element.val('');
    };
    //开始阅读
    fileReader.readAsDataURL(file);

}

