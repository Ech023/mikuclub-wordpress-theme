$(function () {

    /**
     * 如果在DOM中发现消息页面的css类名时触发
     * 消息列表 默认触发加载
     */
    let $messagePageElement = $('body.page .content .page-message');
    if ($messagePageElement.length) {

        getMessageList();

        //绑定 下一页按钮点击事件, 获取下一页消息列表
        $messagePageElement.find('button.get-next-page').on('click', '', '', getMessageList);
        //绑定列表展开事件, 获取和特定收件人之间的消息列表
        $messagePageElement.on('show.bs.collapse', '.collapse', '', getMessageListWithOneSender);

        //绑定删除私信按钮点击事件
        $('body.page .content .page-message').on('click', 'a.delete-message-by-user', '', function () {

            //删除私信
            const target_user_id = $(this).attr('data-target-user-id');
            delete_private_message_by_user(target_user_id);

            //隐藏对应的发件人私信窗口和列表
            $(this).closest('div.message-item').hide();
        });

        /**模态窗消失时触发
         *
       */
        $('body.page').on('hidden.bs.modal', '.my-modal', event => {
            //隐藏当前展开的窗口
            $('body.page .content .page-message .message-item .collapse.show').collapse('hide');
        });

    }

});


/**
 * 获取消息列表
 */
function getMessageList() {

    let $messagePage = $('body.page .content .page-message');
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
            /*
        case MESSAGE_TYPE.forumReply:
            messageType = MESSAGE_TYPE.forumReply;
            url = URLS.bbpressReply;
            break;*/
        }

    }

    //如果未能正确获取 提示错误
    if (!messageType) {
        MyToast.show_error('TYPE参数错误或缺失');
        return;
    }

    //创建请求数据
    let data = { paged };


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

        MyToast.show_error(errorInfo);
        $button.html(buttonText);

    };

    /**
     * 错误情况
     */
    let failCallback = function () {

        //创建通知弹窗
        MyToast.show_error('请求错误 请重试');
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

    let $elementParent = $(this);
    let $element = $elementParent.children('div.card-body');

    let sender_id = $elementParent.data('sender');
    let senderName = $elementParent.prev().find('.display-name').html();

    //创建请求数据
    let data = {
        sender_id,
        //number: 50
    };

    let loadingElement = $(' <div class="text-center"><div class="loading spinner-border text-miku" role="status" aria-hidden="true"></div></div>');


    let black_list_button_class = 'add-user-black-list';
    let black_list_button_text = '加入黑名单';
    //如果目标用户已经被拉黑
    if (MY_SITE.user_black_list.includes(parseInt(sender_id))) {

        black_list_button_class = 'delete-user-black-list';
        black_list_button_text = '从黑名单里移除';
    }


    let createMessageModalButton = $(`

    <div class="row mt-5 justify-content-center">
        <div class="col-9 col-md-6">

            <button class="btn btn-miku w-100 open_private_message_modal" data-recipient_id="${sender_id}" data-recipient_name="${senderName}">
                回复
            </button>

        </div>
        <div class="col-auto">
            <div class="dropdown" >
                <a class="btn btn-secondary px-3" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" title="更多操作">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item delete-message-by-user" href="javascript:void(0);" data-target-user-id="${sender_id}">删除私信</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item ${black_list_button_class}" href="javascript:void(0);" data-target-user-id="${sender_id}">${black_list_button_text}</a></li>
                </ul>
             </div>

        </div>
    </div>
  
    `);

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
            if (sender_id > 0) {
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


/**
 * 删除特定发件人发送给用户的所有私信
 * @param {number} target_user_id 
 */
function delete_private_message_by_user(target_user_id) {

    open_confirm_modal('确认要删除该用户发送给你的所有私信记录吗?', '', () => {


        const data = {
            target_user_id,
        }

        //成功的情况
        let successCallback = function (response) {
            MyToast.show_success('删除私信成功');



        };

        //错误的情况
        let failCallback = function () {
            MyToast.show_error('删除私信失败');
        };

        let completeCallback = function () {

        };


        $.ajax({
            url: URLS.privateMessage,
            data,
            type: HTTP_METHOD.delete,
            headers: createAjaxHeader()
        }).done(successCallback).fail(failCallback).always(completeCallback);

    });
}








