/// <reference path="common/base.js" />
/// <reference path="common/constant.js" /> 

let $message_page_element;

$(function () {

    /**
     * 如果在DOM中发现消息页面的css类名时触发
     * 消息列表 默认触发加载
     */
    $message_page_element = $('body.page .page-message');
    if ($message_page_element.length) {

        get_message_list();

        //绑定滚动事件
        $(document).on('scroll', function () {

            //如果可以看到列表底部
            if (isVisibleOnScreen($message_page_element.find('.message-list-end'))) {
                //触发自动加载
                get_message_list();
            }

        });

        //绑定列表展开事件, 获取和特定收件人之间的消息列表
        $message_page_element.on('show.bs.collapse', '.collapse', '', function () {
            get_message_list_with_one_sender($(this));
        });

        //绑定删除私信按钮点击事件
        $message_page_element.on('click', 'a.delete-message-by-user', '', function () {

            //删除私信
            delete_private_message_by_user($(this));

        });

    }

});


/**
 * 获取消息列表
 */
function get_message_list() {

    //如果是重新加载, 重置end属性
    if (get_message_list.is_loading || get_message_list.is_end) {
        return;
    }

    //开启信号标
    get_message_list.is_loading = true;

    const $message_list_element = $message_page_element.find('.message-list');
    const message_type = $message_list_element.data('message-type');

    //如果未能正确获取 提示错误
    if (!message_type) {
        MyToast.show_error('TYPE参数错误或缺失');
        return;
    }

    let paged = get_message_list.paged || 0;
    paged++;



    //消息类型, 请求地址
    let url;

    //根据URL变量设置要获取的消息类型
    switch (message_type) {
        case MESSAGE_TYPE.privateMessage:
            url = URLS.privateMessage;
            break;
        case MESSAGE_TYPE.commentReply:
            url = URLS.comments;
            break;
        /*
    case MESSAGE_TYPE.forumReply:
        url = URLS.bbpressReply;
        break;*/
    }

    //创建请求数据
    const data = { paged };


    //回调函数
    let success_callback = (response) => {

        //结果不是空的
        if (isNotEmptyArray(response)) {

            //创建消息列表类
            let newMessageList = new MyMessageList(message_type);
            //根据类型名称 转换成对应的消息类型
            newMessageList.add(response);

            //插入内容到页面中
            $message_list_element.append(newMessageList.toHTML());

            //更新页码
            get_message_list.paged = paged;

        }
        //空内容错误
        else {
            MyToast.show_success('无更多私信');

            //设置end属性, 避免继续尝试
            get_message_list.is_end = true;

            //如果列表原本就是空的
            if ($message_list_element.children().length === 0) {
                //显示无结果
                show_not_found_row($message_list_element);
            }
        }

    };

    send_get(
        url,
        data,
        () => {
            show_loading_row($message_list_element.parent());
        },
        success_callback,
        defaultFailCallback,
        () => {
            hide_loading_row();
            //关闭loading属性
            get_message_list.is_loading = false;
        }

    );

}


/**
 * 获取和某个特定发件人之间的消息列表
 * 只获取最后50条消息, 不支持换页功能
 * @param {jQuery} $message_element
 */
function get_message_list_with_one_sender($message_element) {
    

    let $message_element_body = $message_element.children('div.card-body');

    let sender_id = $message_element.data('sender');
    let senderName = $message_element.prev().find('.display-name').html();

    //清空列表里的旧内容
    $message_element_body.empty();

    //创建请求数据
    const data = {
        sender_id,
        //number: 50
    };

    let black_list_button_class = 'add-user-black-list';
    let black_list_button_text = '加入黑名单';
    //如果目标用户已经被拉黑
    if (MY_SITE.user_black_list.includes(parseInt(sender_id))) {

        black_list_button_class = 'delete-user-black-list';
        black_list_button_text = '从黑名单里移除';
    }


    const createMessageModalButton = $(`

    <div class="row mt-5 justify-content-center">
        <div class="col-9 col-md-6">

            <button class="btn btn-sm btn-miku w-100 open_private_message_modal" data-recipient_id="${sender_id}" data-recipient_name="${senderName}">
                回复
            </button>

        </div>
        <div class="col-auto">
            <div class="dropdown" >
                <a class="btn btn-sm btn-light-2 px-4" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" title="更多操作">
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

    //回调函数
    let success_callback = function (response) {

        //结果不是空的
        if (isNotEmptyArray(response)) {

            //改变默认列表排序
            response.reverse();

            //创建消息列表类
            let newMessageList = new MyMessageList(MESSAGE_TYPE.privateMessageWithOneSender);
            newMessageList.add(response);
            //插入列表内容到页面中
            $message_element_body.append(newMessageList.toHTML());

            //只有在发件人不是系统(0)的时候才提供回复按钮
            if (sender_id > 0) {
                //插入回复按钮
                $message_element_body.append(createMessageModalButton);
            }

            //滚动列表到底部
            $message_element_body.scrollTop($message_element_body.prop("scrollHeight"));



        }

    };

    send_get(
        URLS.privateMessage,
        data,
        () => {
            // show_loading_modal();

        },
        success_callback,
        defaultFailCallback,
        () => {
            // hide_loading_modal(); //点击头像的时候会触发 无法隐藏的BUG
            
        }
    );



}


/**
 * 删除特定发件人发送给用户的所有私信
 * @param {jQuery} $button 
 */
function delete_private_message_by_user($button) {

    open_confirm_modal('确认要删除该用户发送给你的所有私信记录吗?', '', () => {

        const target_user_id = $button.data('target-user-id');

        const data = {
            target_user_id,
        };

        send_delete(
            URLS.privateMessage,
            data,
            () => {
                show_loading_modal();
            },
            () => {
                MyToast.show_success('删除私信成功');
                //隐藏对应的私信容器
                $button.closest('.message-item').fadeOut(300);
            },
            defaultFailCallback,
            () => {
                hide_loading_modal();
            }
        );

    });
}








