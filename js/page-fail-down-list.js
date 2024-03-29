

/**
 * 失效列表页面专用JS
 */


$(function () {

    //如果当前是在 下载失效页面
    let $failDownListPage = $('body.page .page-fail-down-list');
    if ($failDownListPage.length) {

        //遍历 列表元素
        //为每个按钮统一分配post-id
        let $failDownListItem = $failDownListPage.find('.list-item');

        //延迟运行
        let delay = 0;

        $failDownListItem.each((index, element) => {

            //检测链接有效性
            $(element).find('a.down').each(
                (index, element) => {
                    setTimeout(function () {
                        failDownListLinkCheck($(element));
                    }, delay);
                    //累积每2秒运行一次查询
                    delay += 2000;
                }
            );

            //绑定按钮
            $(element).on('click', 'button.reset-fail-times', {
                post_id: $(element).data('post-id'),
                reset: true
            }, resetFailTimes);
            $(element).on('click', 'button.disable-fail-times', {
                post_id: $(element).data('post-id'),
                disable: true
            }, disableFailTimes);
            $(element).on('click', 'button.delete_down_link', '', function () {
                delete_down_link($(this));
            });



        });


    }


});


/**
 * 失效列表 百度分享链接检测功能
 */
/**
 * 失效列表 百度分享链接检测功能
 * @param {JQuery} $linkElement 要检测的A标签
 */
function failDownListLinkCheck($linkElement) {

    const link = $linkElement.attr('href');
    const $down_container_element = $linkElement.closest('.down-container');
    const $badge_container_element = $down_container_element.find('.badge-container');

    if (!link.includes('pan.baidu.com') && !link.includes('aliyun') && !link.includes('alipan')) {
        return;
    }

    const isValidCallback = function () {

        $badge_container_element.empty().append('<span class="badge bg-success">还有效</span>');
        $linkElement.addClass('text-success');
    };

    const isInValidCallback = function () {
        $badge_container_element.empty().append('<span class="badge bg-danger">已失效</span>');
        $linkElement.addClass('text-danger');
    };

    const errorCallback = function () {
        $badge_container_element.empty().append('<span class="badge bg-warning">检测失败</span>');
    };

    checkBaiduPanValidity(link, isValidCallback, isInValidCallback, errorCallback);


}

/**
 * 重置下载失效
 * @param {Event} event
 */
function resetFailTimes(event) {

    //获取 列表主元素
    const $button = $(this);
    let $parentItem = $button.closest('.list-item');

    //切换按钮状态
    $button.toggleDisabled();

    let data = event.data;

    //回调函数
    let successCallback = function (response) {

        //创建通知弹窗
        MyToast.show_success('已清零');
        //设置背景颜色
        $parentItem.css('background-color', '#ccc');

    };

    /**
     * 错误情况
     */
    let failCallback = function () {

        //创建通知弹窗
        MyToast.show_error('请求错误 请重试');
        //切换按钮状态
        $button.toggleDisabled();

    };

    $.ajax({
        url: URLS.failDown,
        data,
        type: HTTP_METHOD.get,
        headers: createAjaxHeader()
    }).done(successCallback).fail(failCallback);

}


/**
 * 关闭失效统计
 * @param {Event} event
 */
function disableFailTimes(event) {

    //获取 列表主元素
    const $button = $(this);
    let $parentItem = $button.closest('.list-item');

    //切换按钮状态
    $button.toggleDisabled();

    let data = event.data;

    //回调函数
    let successCallback = function (response) {

        //创建通知弹窗
        MyToast.show_success('已关闭失效');
        //设置背景颜色
        $parentItem.css('background-color', '#ccc');

    };

    /**
     * 错误情况
     */
    let failCallback = function () {

        //创建通知弹窗
        MyToast.show_error('请求错误 请重试');
        //切换按钮状态
        $button.toggleDisabled();

    };

    $.ajax({
        url: URLS.failDown,
        data,
        type: HTTP_METHOD.get,
        headers: createAjaxHeader()
    }).done(successCallback).fail(failCallback);

}


//删除对应的下载链接
function delete_down_link($button) {

    open_confirm_modal('确认要清空该下载地址吗?', '', () => {

        const data = {
            post_id: $button.data('post_id'),
            meta_key: $button.data('meta_key'),
            meta_value: '',
        };

        send_post(
            URLS.updatePostMeta,
            data,
            () => {
                show_loading_modal();
            },
            () => {
                MyToast.show_success('清空成功');

                //更新按钮状态
                $button.removeClass('btn-light-2').addClass('btn-dark-1')
                $button.html('清空成功');
                $button.toggleDisabled();

            },
            defaultFailCallback,
            () => {
                hide_loading_modal();
            }
        );

    });


}