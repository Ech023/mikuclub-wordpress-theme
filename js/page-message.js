$(function () {

    /**
     * 如果在DOM中发现消息页面的css类名时触发
     * 消息列表 默认触发加载
     */
    let $messagePageElement = $('body.page .content.page-message');
    if ($messagePageElement.length) {

        getMessageList();

        //绑定 下一页按钮点击事件, 获取下一页消息列表
        $messagePageElement.find('button.get-next-page').on('click', '', '', getMessageList);
        //绑定列表展开事件, 获取和特定收件人之间的消息列表
        $messagePageElement.on('show.bs.collapse', '.collapse', '', getMessageListWithOneSender);

        /**模态窗消失时触发
         *
       */
        $('body.page').on('hidden.bs.modal', '.my-modal', event => {
            //隐藏当前展开的窗口
            $('body.page .content.page-message .message-item .collapse.show').collapse('hide');
        });

    }

});


/**
 * 获取消息列表
 */
function getMessageList() {

    let $messagePage = $('body.page .content.page-message');
    let $button = $messagePage.find('button.get-next-page');
    let $pageInput = $messagePage.find('input[name="paged"]');

    //切换按钮的显示
    $button.toggleDisabled();
    $button.children().toggle();

    //获取当前页数+1
    let paged = parseInt($pageInput.val()) + 1;

    //消息类型, 请求地址
    let messageType, url;
    //根据URL变量设置要获取的消息类型
    let currentQueryParam = getQueryParameters();


    if (currentQueryParam.hasOwnProperty('type')) {

        switch (currentQueryParam.type) {
            case MESSAGE_TYPE.privateMessage:
                messageType = MESSAGE_TYPE.privateMessage;
                url = URLS.privateMessage;
                break;
            case MESSAGE_TYPE.commentReply:
                messageType = MESSAGE_TYPE.commentReply;
                url = URLS.comments;
                break;
            case MESSAGE_TYPE.forumReply:
                messageType = MESSAGE_TYPE.forumReply;
                url = URLS.bbpressReply;
                break;
        }

    }

    //如果未能正确获取 提示错误
    if (!messageType) {
        TOAST_SYSTEM.add('TYPE参数错误或缺失', TOAST_TYPE.error);
        return;
    }

    //创建请求数据
    let data = {paged};


    //回调函数
    let successCallback = function (response) {

        //结果不是空的
        if (isNotEmptyArray(response)) {

            //创建消息列表类
            let newMessageList = new MyMessageList(messageType);
            //根据类型名称 转换成对应的消息类型
            newMessageList.add(response);
            //插入内容到页面中
            $messagePage.children('.message-list').append(newMessageList.toHTML());

            //更新页码
            $pageInput.val(paged);

            //恢复按钮
            $button.toggleDisabled();
            $button.children().toggle();

        }
        //空内容错误
        else {
            notMoreCallback();
        }

    };

    /**
     * 错误情况: 没有更多
     */
    let notMoreCallback = function () {

        let buttonText = '';
        let errorInfo = '';

        //如果列表不是空的, 提示没有下一页
        if ($messagePage.find('.message-item').length) {
            buttonText = '已经到最后一页了';
            errorInfo = '没有更多消息了';
        } else {
            buttonText = '您还没有任何消息记录';
            errorInfo = '无相关信息';
        }

        TOAST_SYSTEM.add(errorInfo, TOAST_TYPE.error);
        $button.html(buttonText);

    };

    /**
     * 错误情况
     */
    let failCallback = function () {

        //创建通知弹窗
        TOAST_SYSTEM.add('请求错误 请重试', TOAST_TYPE.error);
        //切换按钮激活状态 和 按钮显示内容
        $button.toggleDisabled();
        $button.children().toggle();
    };


    $.ajax({
        url,
        data,
        type: HTTP_METHOD.get,
        headers: createAjaxHeader()
    }).done(successCallback).fail(failCallback);

}


/**
 * 获取和某个特定发件人之间的消息列表
 * 只获取最后50条消息, 不支持换页功能
 * @param {Event} event
 */
function getMessageListWithOneSender(event) {

    let $elementParent = $(event.target);
    let $element = $elementParent.children('div.card-body');

    let senderId = $elementParent.data('sender');
    let senderName = $elementParent.prev().find('.display-name').html();

    //创建请求数据
    let data = {
        sender_id: senderId,
        number: 50
    };

    let loadingElement = $(' <div class="text-center"><div class="loading spinner-border text-miku" role="status" aria-hidden="true"></div></div>');
    let createMessageModalButton = $(`
    <div class="text-center mt-5 create-private-message-modal">
        <button class="btn btn-miku w-50">回复</button>
        <input type="hidden" name="recipient_name" value="${senderName}">
        <input type="hidden" name="recipient_id" value=${senderId}>
    </div>`);

    //清空当前内容, 然后添加进度条
    $element.empty().append(loadingElement);



    //回调函数
    let successCallback = function (response) {

        //结果不是空的
        if (isNotEmptyArray(response)) {

            //改变默认列表排序
            response.reverse();

            //创建消息列表类
            let newMessageList = new MyMessageList(MESSAGE_TYPE.privateMessageWithOneSender);
            newMessageList.add(response);
            //插入列表内容到页面中
            $element.append(newMessageList.toHTML());

            //只有在发件人不是系统(0)的时候才提供回复按钮
            if (senderId > 0) {
                //插入回复按钮
                $element.append(createMessageModalButton);
            }

            //滚动列表到底部
            $element.scrollTop($element.prop("scrollHeight"));

            //隐藏加载进度条
            loadingElement.hide();

        }

    };



    $.ajax({
        url: URLS.privateMessage,
        data,
        type: HTTP_METHOD.get,
        headers: createAjaxHeader()
    }).done(successCallback).fail(defaultFailCallback);


}











