/// <reference path="common/base.js" />
/// <reference path="common/constant.js" /> 

/**
 * 用户资料页面专用JS
 */


$(function () {

    //如果发现了相关元素 触发动作
    let $pageProfileElement = $('body.page .page-profile');
    if ($pageProfileElement.length) {
        $pageProfileElement.find('form.user-profile').on('submit', '', '', function (event) {
            event.preventDefault();
            update_user_profile($(this));
        });

        $pageProfileElement.find('.delete_user_self').on('click', '', '', function (event) {
            delete_user_self();
        });

    }

});



/**
 * 更新用户信息
 * @param {jQuery} $form
 */
function update_user_profile($form) {

    const email = $form.find('input[name="email"]').val().trim();
    const name = $form.find('input[name="user_name"]').val().trim();
    const description = $form.find('input[name="description"]').val().trim();
    const password = $form.find('input[name="password"]').val().trim();

    if (!validateEmail(email) || email.includes('@fake.com')) {
        MyToast.show_error('请填写一个有效的邮箱地址');
        return;
    }
    if (!name) {
        MyToast.show_error('昵称不能为空');
        return;
    }

    const data = {
        email,
        name,
        description
    };

    //如果需要更改密码
    if (password) {
        data.password = password;
    }

    send_post(
        URLS.userSelf,
        data,
        () => {
            show_loading_modal();
        },
        () => {
            MyToast.show_success('保存成功');
        },
        defaultFailCallback,
        () => {
            hide_loading_modal();
        }
    );

}

/**
 * 删除当前用户自己
 */
function delete_user_self() {

    //删除请求
    const send_delete_request = () => {

        send_post(
            URLS.deleteUserSelf,
            {
                force: true,
                reassign: false,
            },
            () => {
                show_loading_modal();
            },
            () => {
                MyToast.show_success('删除成功');
                setTimeout(() => {
                    // 设置页面跳转回网站根目录
                    window.location.href = "/";
                }, 3000)
            },
            defaultFailCallback,
            // (jqXHR) => {
            //     MyToast.show_success('删除失败');
            // },
            () => {
                hide_loading_modal();
            }
        );


    }


    //需要手动输入的口令
    $confirm_text = '确认删除';

    open_prompt_modal('要删除账号, 请在下方输入 ' + $confirm_text + ' 这4个字后点击确定', '', (value) => {

        //口令正确, 发送请求
        if (value.trim() === $confirm_text) {
            send_delete_request();
        }
        else {
            MyToast.show_error('确认信息不匹配, 请输入: ' + $confirm_text);
        }

    });


}




