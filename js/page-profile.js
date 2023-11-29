/**
 * 用户资料页面专用JS
 */


$(function () {

    //如果发现了相关元素 触发动作
    let $pageProfileElement = $('body.page .page-profile');
    if ($pageProfileElement.length) {

        $pageProfileElement.find('form.user-profile').on('submit', '', '', updateUserProfile);

    }

});











/**
 * 把base64data转换成 blob文件类型
 * @param {String} dataURL
 * @return {Blob}
 */
function dataURLtoBlob(dataURL) {
    let array, binary, i;
    binary = atob(dataURL.split(',')[1]);
    array = [];
    i = 0;
    while (i < binary.length) {
        array.push(binary.charCodeAt(i));
        i++;
    }
    return new Blob([new Uint8Array(array)], {
        type: 'image/jpeg'
    });
}


/**
 * 更新用户信息
 * @param {Event} event
 */
function updateUserProfile(event) {

    event.preventDefault();

    console.log('发送');

    let $form = $(this);

    let $button = $form.find('button[type="submit"]');

    let email = $form.find('input[name="email"]').val().trim();
    let name = $form.find('input[name="user_name"]').val().trim();
    let description = $form.find('input[name="description"]').val().trim();
    let password = $form.find('input[name="password"]').val().trim();

    if (!validateEmail(email) || email.includes('@fake.com')) {
        TOAST_SYSTEM.add('请填写一个有效的邮箱地址', TOAST_TYPE.error);
        return;
    }
    if (!name) {
        TOAST_SYSTEM.add('昵称不能为空', TOAST_TYPE.error);
        return;
    }

    let data = {
        email,
        name,
        description
    };

    //如果需要更改密码
    if (password) {
        data.password = password;
    }

    //激活按钮
    $button.toggleDisabled();
    $button.children().toggle();


    //成功的情况
    let successCallback = function (response) {
        TOAST_SYSTEM.add('保存成功', TOAST_TYPE.success);
    };

    let completeCallback = function () {
        //激活按钮
        $button.toggleDisabled();
        $button.children().toggle();
    };


    $.ajax({
        url: URLS.userSelf,
        data,
        type: HTTP_METHOD.post,
        headers: createAjaxHeader()
    }).done(successCallback).fail(defaultFailCallback).always(completeCallback);


}







