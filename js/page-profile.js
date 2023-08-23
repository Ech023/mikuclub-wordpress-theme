/**
 * 用户资料页面专用JS
 */


$(function () {

    //如果发现了相关元素 触发动作
    let $pageProfileElement = $('body.page .page-profile');
    if ($pageProfileElement.length) {

        $pageProfileElement.find('input[type="file"]').on('change', '', '', selectImage);

        $pageProfileElement.find('form.user-profile').on('submit', '', '', updateUserProfile);


        let $bodyPage = $('body.page');
        //在模态窗显示后 初始化剪切图片库
        $bodyPage.on('shown.bs.modal', '.modal.image-cropper-modal', '', initCropper);
        //在模态窗关闭后 清空input file选中内容
        $bodyPage.on('hidden.bs.modal', '.modal.image-cropper-modal', '', clearInput);

        $bodyPage.on('click', '.modal.image-cropper-modal button.cropper-image', '', cropperUserAvatar);
        $bodyPage.on('click', '.modal.image-cropper-modal button.upload-image', '', uploadUserAvatar);


    }

});


function selectImage(event) {

    if (!window.File || !window.FileReader || !window.FileList || !window.Blob) {
        TOAST_SYSTEM.add('当前的浏览器不支持JS文件API', TOAST_TYPE.error);
        return;
    }

    let $inputFile = $(this);

    //如果未选中文件
    if (!$inputFile.prop('files').length) {
        TOAST_SYSTEM.add('未选中正确文件', TOAST_TYPE.error);
        return;
    }

    //获取文件对象
    let file = $inputFile.prop('files')[0];
    //创建阅读器
    let fileReader = new FileReader();
    //阅读完毕后 回调
    fileReader.onload = function () {
        //显示剪切图片模态窗
        new MyImageCropperModal(fileReader.result).show();
    };
    //开始阅读
    fileReader.readAsDataURL(file);


}

/**
 * 初始化剪切图片库
 * @param {Event} event
 */
function initCropper(event) {

    let $modalElement = $(this);
    let $image = $modalElement.find('img.img-cropper');

    $image.cropper({
        aspectRatio: 1,
        viewMode: 2,
        minCropBoxWidth: 100,
        minCropBoxHeight: 100,
    });

}

/**
 * 清除input file表单选中内容
 */
function clearInput() {

    let $pageProfileElement = $('body.page .page-profile');
    $pageProfileElement.find('input[type="file"]').val('');
}

/**
 * 剪切图片
 */
function cropperUserAvatar() {

    let $modalElement = $('.modal.image-cropper-modal');
    let $image = $modalElement.find('img.img-cropper');
    let $uploadButton = $modalElement.find('button.upload-image');

    // 获取剪切对象
    let cropper = $image.data('cropper');
    let canvas = cropper.getCroppedCanvas({ width: 100, height: 100, imageSmoothingQuality: 'high', fillColor: 'white' });
    $(canvas).addClass('rounded-circle');

    //输出预览
    let $previewElement = $modalElement.find('.preview');
    $previewElement.empty().append(canvas);

    //激活上传按钮
    $uploadButton.removeAttr('disabled');


}

/**
 * 上传图片
 * @param {Event} event
 */
function uploadUserAvatar(event) {

    //获取按钮
    const $button = $(this);

    let $pageProfileElement = $('body.page .page-profile');
    let $avatar = $pageProfileElement.find('img.avatar');

    let $modalElement = $('.modal.image-cropper-modal');
    let imgData = $modalElement.find('.preview canvas').get(0).toDataURL();

    //请求主体
    let data = new FormData();
    //随机文件名
    let fileName = Date.now() + '.jpg';
    data.append('file', dataURLtoBlob(imgData), fileName);
    data.append('action_update_avatar', true);


    //切换按钮的显示
    $button.toggleDisabled();
    $button.children().toggle();

    //成功的情况
    let successCallback = function (response) {


        if (response instanceof Object && response.hasOwnProperty('guid') && response.guid.hasOwnProperty('rendered')) {
            //更新头像地址
            $avatar.attr('src', response.guid.rendered);
        }

        //关闭模态窗
        $modalElement.modal('hide');

    };


    let completeCallback = function () {
        //激活按钮
        $button.toggleDisabled();
        $button.children().toggle();
    };


    $.ajax({
        url: URLS.media,
        data,
        type: HTTP_METHOD.post,
        mimeType: "multipart/form-data",
        contentType: false,
        processData: false,
        dataType: 'json',
        headers: createAjaxHeader()
    }).done(successCallback).fail(defaultFailCallback).always(completeCallback);


}


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







