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







