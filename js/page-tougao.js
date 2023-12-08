$(function () {

    //如果发现了相关元素 触发动作
    let $pageTougaoElement = $('body.page .page-tougao');
    if ($pageTougaoElement.length) {

        addStyleClassToForm();



        /* WPUF 3.5.14版本 及之前 */
        //showChildCategories();
        /* WPUF 3.5.14版本 之后 */
        fixCategories();

        $pageTougaoElement.find('form').on('submit', '', '', changeSubmitButtonText);

        
        $pageTougaoElement.on('click', 'button.set-sticky-post', '', setStickyPost);
        $pageTougaoElement.on('click', 'button.delete-sticky-post', '', deleteStickyPost);


      


        $pageTougaoElement.on('click', 'button.update-post-date', '', updatePostDate);

        //给插入本地图片按钮 移除gif文件支持
        $pageTougaoElement.find('form #wpuf-insert-image-container').on('click', 'input[type="file"]', function (event) {
            let accept = $(this).attr('accept').replace('image/gif,.gif,', '');
            $(this).attr('accept', accept);
        });
        //给表单上传图片按钮增加多选功能 和移除gif文件支持
        $pageTougaoElement.find('form .previews').on('click', 'input[type="file"]', function (event) {
            $(this).attr('multiple', true);
            let accept = $(this).attr('accept').replace('image/gif,.gif,', '').replace('image/bmp,.bmp', '');
            $(this).attr('accept', accept);
        });

        $pageTougaoElement.find('form input.textfield, form textarea.textareafield').on('change', '', '', inputTrim);

        $pageTougaoElement.find('form input.textfield[name="post_title"]').on('change', '', '', actionOnInputTitle);

        $pageTougaoElement.find('form input.textfield[name="tags"]').on('change', '', '', actionOnInputTags);

        $pageTougaoElement.find('form input.textfield[name="source"]').on('change', '', '', actionOnInputSourceLink);

        $pageTougaoElement.find('form input.textfield[name^="down"]').on('change', '', '', actionOnInputDownload);

        $pageTougaoElement.find('form input.textfield[name^="down"]').on('change', '', '', function (event) {
            checkInputDownloadBaiduValidity(event.target);
        });

        $pageTougaoElement.find('form input.textfield[name^="password"]').on('change', '', '', actionOnInputPassword);

        $pageTougaoElement.find('form input.textfield[name="bilibili"]').on('change', '', '', actionOnInputBilibili);

        $pageTougaoElement.find('form textarea.textareafield[name="video"]').on('change', '', '', actionOnInputVideo);


        $pageTougaoElement.find('form textarea.textareafield[name="baidu_fast_link"]').on('change', '', '', actionOnBaiduFastLink);


        /**
         * 加载完毕后运行 百度链接检测
         */
        $pageTougaoElement.find('form input.textfield[name^="down"]').each((index, element) => {
            checkInputDownloadBaiduValidity(element);
        });


        $pageTougaoElement.find('.fixed-submit-button-div button').on('click', '', '', actionOnFixedSubmitButton);


        //滚动的时候 根据 底部提交审核按钮  的可见度 来显示悬浮提交按钮
        $(document).on('scroll', showFixedSubmitButtonOnScroll);


    }


});


/**
 * 给表单添加bootstrap类名
 */
function addStyleClassToForm() {

    let $pageTougaoElement = $('body.page .page-tougao');

    $pageTougaoElement.find('form input, form textarea').addClass('form-control');

    let $insertImageButton = $pageTougaoElement.find('#wpuf-insert-image-container a.wpuf-insert-image');
    let buttonText = $insertImageButton.html();
    buttonText = buttonText.replace('插入照片', '插入本地图片');
    $insertImageButton.html(buttonText);

    //修改删除图片按钮
    $pageTougaoElement.find('.attachment-delete').html('删除');


}

/**
 * 提交后 更改按钮文字+显示通知弹窗
 */
function changeSubmitButtonText() {

    //只有查询到 提交按钮 处于注销状态的时候 才会执行
    let $submitButton = $('.wpuf-submit input.button-primary-disabled[type="submit"]');
    if ($submitButton.length) {

        let previousValue = $submitButton.val();

        $submitButton.val('提交中...');
        MyToast.show_continue('提交中...');
        //设置定时任务, 如果超过一定时间还未提交成功, 说明出现错误 , 要重置按钮状态
        setTimeout(function () {
            MyToast.show_error('提交失败 请重试');
            $submitButton.val(previousValue);
            $submitButton.removeClass('button-primary-disabled');
            $submitButton.toggleDisabled();
        }, 30000);

    }


}




/**
 * 设置置顶文章
 * @param {Event} event
 */
function setStickyPost(event) {

    //获取按钮
    const $button = $(this);

    let previousValue = $button.html();
    let post_id = $button.data('post-id');
    let data = { post_id };

    //切换按钮状态
    $button.toggleDisabled();


    //回调函数
    let successCallback = function (response) {

        MyToast.show_success('置顶成功');
        $button.html('取消置顶');
        $button.toggleClass('set-sticky-post').toggleClass('delete-sticky-post');
    };

    /**
     * 请求结束后
     */
    let completeCallback = function () {
        //恢复按钮状态
        $button.toggleDisabled();
    };

    $.ajax({
        url: URLS.stickyPosts,
        data,
        type: HTTP_METHOD.post,
        headers: createAjaxHeader()
    }).done(successCallback).fail(defaultFailCallback).always(completeCallback);

}

/**
 * 取消置顶文章
 * @param {Event} event
 */
function deleteStickyPost(event) {

    //获取按钮
    const $button = $(this);

    let previousValue = $button.html();
    let post_id = $button.data('post-id');
    let data = { post_id };

    //切换按钮状态
    $button.toggleDisabled();


    //回调函数
    let successCallback = function (response) {

        MyToast.show_success('取消置顶成功');
        $button.html('置顶投稿');
        $button.toggleClass('set-sticky-post').toggleClass('delete-sticky-post');

    };

    /**
     * 请求结束后
     */
    let completeCallback = function () {
        //恢复按钮状态
        $button.toggleDisabled();
    };

    $.ajax({
        url: URLS.stickyPosts,
        data,
        type: HTTP_METHOD.delete,
        headers: createAjaxHeader()
    }).done(successCallback).fail(defaultFailCallback).always(completeCallback);

}






/**
 * 更新文章创建时间
 * @param {Event} event
 */
function updatePostDate(event) {

    //获取按钮
    const $button = $(this);

    let post_id = $button.data('post-id');

    let data = { post_id };

    //切换按钮状态
    $button.toggleDisabled();

    //回调函数
    let successCallback = function (response) {

        MyToast.show_success('更新成功');
        $button.html('更新成功');
        $button.toggleDisabled();

    };

    /**
     * 请求结束后
     */
    let completeCallback = function () {
        //恢复按钮状态
        $button.toggleDisabled();
    };

    $.ajax({
        url: URLS.updatePostDate,
        data,
        type: HTTP_METHOD.post,
        headers: createAjaxHeader()
    }).done(successCallback).fail(defaultFailCallback).always(completeCallback);

}


/**
 * 移除表单前后空格
 * @param {Event} event
 */
function inputTrim(event) {
    const $input = $(this);
    let value = $input.val().trim();
    $input.val(value);
}

/**
 * 标题栏 动作
 * @param {Event} event
 */
function actionOnInputTitle(event) {
    const $input = $(this);
    //把全角字符转换成半角
    let value = fullCharToHalfChar($input.val());
    //替换标题的方括号
    value = value.replace(/\[/g, "【").replace(/]/g, "】").replace(/\(/g, "【").replace(/\)/g, "】");

    $input.val(value);
}

/**
 * 标签栏 动作
 * @param {Event} event
 */
function actionOnInputTags(event) {
    const $input = $(this);
    //把全角字符转换成半角
    let value = fullCharToHalfChar($input.val());
    //移除分隔符周围的空格
    value = value.replace(', ', ',').replace(' ,', ',').toUpperCase();

    $input.val(value);

}


/**
 * 来源地址栏 动作
 * @param {Event} event
 */
function actionOnInputSourceLink(event) {
    const $input = $(this);
    let value = $input.val();

    //如果链接无效
    if (!validateURL(value)) {
        MyToast.show_error('来源地址格式错误');
        $input.val('');
    }

}

/**
 * 下载地址栏 动作
 * @param {Event} event
 */
function actionOnInputDownload(event) {

    let $pageTougaoElement = $('body.page .page-tougao');
    const $input = $(this);
    let value = $input.val();

    if (value.length === 0) {
        return;
    }

    value = fullCharToHalfChar($input.val());
    //移除空格
    value = value.replace(/\s*/g, '');

    //如果是飞猫云
    if (value.includes('fmpan') || value.includes('fmapp')) {
        MyToast.show_error('禁止使用飞猫云');
        $input.val('');
        return;
    }


    let linkText = '链接:';
    let passwordText = '提取码:';

    //如果内容里 链接和提取码同时存在
    if (value.includes(linkText) && value.includes(passwordText)) {

        //把提取码复制到 对应的输入框内
        let startString = passwordText;
        let passwordLength = 4;
        let index = value.indexOf(startString);
        //如果"提取码:" 存在
        if (index !== -1) {

            //提取出访问密码
            let password = value.substr(index + startString.length, passwordLength);
            //移除后续 "--来自百度网盘超级会员的分享"
            password = password.split('--')[0];

            //判断是下载点1还是下载点2
            //如果是下载点2 就复制到密码2
            if ($input.attr('name').includes('down2')) {
                $pageTougaoElement.find('form input.textfield[name="password2"]').val(password);
            }
            //如果是下载点1 就复制到密码1
            else {
                $pageTougaoElement.find('form input.textfield[name="password"]').val(password);
            }

            //移除访问密码后续部分
            value = value.substring(0, index);

        }

    }
    //如果是115盘
    else if (value.includes('115.com')) {

        //移除#符号后面的文件名称
        value = value.split('#')[0];

    }

    //如果"链接:" 不在第一位, 移除所有之前的东西
    if (value.indexOf(linkText) >= 0) {
        value = value.split(linkText)[1];
    }

    if (!validateURL(value) && !value.includes('magnet')) {
        MyToast.show_error('链接格式错误, 必须包含http或https');
        $input.val('');
        return;
    }


    //更新地址
    $input.val(value);

}

/**
 * 下载地址栏  检测百度链接有效性
 * @param {HTMLInputElement} inputElement
 */
function checkInputDownloadBaiduValidity(inputElement) {

    let $input = $(inputElement);
    let value = $input.val();

    //获取输入栏的父元素
    let $inputParent = $input.parent(".wpuf-fields");
    //获取检测信息框
    let $linkInfo = $inputParent.children(".link-status");
    //如果信息框还未创建
    if ($linkInfo.length === 0) {
        //创建新元素
        $linkInfo = $("<span class=\"link-status\"></span>");
    }
    //设置信息框文字
    $linkInfo.html("检测链接有效性中...");

    //重置下载框和信息框类名
    $input.removeClass("link-error link-valid");
    $linkInfo.removeClass("link-error link-valid");


    if (!value || (!value.includes('pan.baidu.com') && !value.includes('aliyun'))) {
        //删除新消息框
        $linkInfo.remove();
        return;
    }

    //在父元素底部插入新消息框
    $inputParent.append($linkInfo);


    let isValidCallback = function () {

        $input.addClass("link-valid");
        $linkInfo.addClass("link-valid");
        $linkInfo.html("分享链接正常");
    };

    let isInValidCallback = function () {
        $input.addClass("link-error");
        $linkInfo.addClass("link-error");
        $linkInfo.html("分享链接已失效");
    };

    let errorCallback = function () {
        $linkInfo.html("检测失败");
    };

    checkBaiduPanValidity(value, isValidCallback, isInValidCallback, errorCallback);


}


/**
 *  密码栏  动作
 * @param {Event} event
 */
function actionOnInputPassword(event) {

    const $input = $(this);
    let value = $input.val();
    //去除 @密码, 去除 @提取码 去除 @复制这段内容后打开百度网盘手机App，操作更方便哦,   去除 左右空格
    value = value.replace(/\u5bc6\u7801\uff1a/g, "").replace(/\u5bc6\u7801\u003a/g, "").replace(/\u63d0\u53d6\u7801\uff1a/g, "").replace(/\u63d0\u53d6\u7801\u003a/g, "").replace(/\u590d\u5236\u8fd9\u6bb5\u5185\u5bb9\u540e\u6253\u5f00\u767e\u5ea6\u7f51\u76d8\u624b\u673a\u0041\u0070\u0070\uff0c\u64cd\u4f5c\u66f4\u65b9\u4fbf\u54e6/g, '');

    //移除后续 "--来自百度网盘超级会员的分享"
    value = value.split('--')[0];

    $input.val(value);

}

/**
 *  BILIBILI栏  动作
 * @param {Event} event
 */
function actionOnInputBilibili(event) {
    const $input = $(this);
    let value = $input.val();

    if (!value.includes('av') && !value.includes('BV')) {
        MyToast.show_error('不是B站地址');
        return;
    }

    let index = value.indexOf("BV");
    if (index === -1) {
        index = value.indexOf("av");
    }

    //提取出av号或者BV号
    value = value.slice(index);

    //移除其他无用变量
    index = value.indexOf("/");
    if (index !== -1) {
        value = value.slice(0, index);
    }
    index = value.indexOf("?");
    if (index !== -1) {
        value = value.slice(0, index);
    }
    index = value.indexOf("#");
    if (index !== -1) {
        value = value.slice(0, index);
    }

    $input.val(value);


}

/**
 *  在线播放 动作
 * @param {Event} event
 */
function actionOnInputVideo(event) {

    const $input = $(this);
    let value = $input.val().trim();

    //把小于和大于的符号 替换成 方括号
    value = value.replace(/</g, '[').replace(/>/g, ']');

    if (value.includes('[')) {
        $input.val(value);
    } else {
        MyToast.show_error('这不是正确的外链HTML播放地址');
        $input.val('');
    }

}

/**
 * 全角字符串转半角
 * @param str
 * @return {string}
 */
function fullCharToHalfChar(str) {
    let result = '';
    for (i = 0; i < str.length; i++) {
        //获取当前字符的unicode编码
        let code = str.charCodeAt(i);
        //在这个unicode编码范围中的是所有的英文字母已经各种字符
        if (code >= 65281 && code <= 65373) {
            //把全角字符的unicode编码转换为对应半角字符的unicode码
            result += String.fromCharCode(str.charCodeAt(i) - 65248);
        }
        //空格
        else if (code === 12288) {
            result += String.fromCharCode(str.charCodeAt(i) - 12288 + 32);
        } else {
            result += str.charAt(i);
        }
    }
    return result;
}

/**
 * 修正分类选项的显示
 * @since WPUF 3.5.14版本之后
 * 
 */
function fixCategories() {

    //获取主分类列表
    let $mainCategoryList = $('.category div[level="0"]');

    //添加缺少的分类明
    $mainCategoryList.addClass('hasChild');

    //移除主分类列表里的所有子分类
    $mainCategoryList.find('select option.level-1').remove();

    $childCategoryContainer = $(`<div id="wpuf-category-dropdown-lvl-1" level="1"></div>`);

    //添加缺少的子列表容器
    $mainCategoryList.parent().append($childCategoryContainer);


    //获取子分类列表
    let childCategoryList = $mainCategoryList.nextAll('select');
    //把子分类移动到新建的容器里面
    $childCategoryContainer.append(childCategoryList);



}

/**
 * 创建转运子分类列表选项
 * @since WPUF 3.5.14版本之后不适用
 * @deprecated
 */
function showChildCategories() {

    //主分类列表
    let $mainCategoryList = $('.category div[level="0"]');



    //如果当前是二级子分类被选中
    let $childCategoryElement = $mainCategoryList.find('select option.level-1:selected');

    if ($childCategoryElement.length) {



        //添加缺少的分类明
        $mainCategoryList.addClass('hasChild');

        //克隆分类列表 创建子分类列表
        //修改属性
        let $childCategoryList = $mainCategoryList.clone().attr('level', '1').removeClass('hasChild').prop('id', 'wpuf-category-dropdown-lvl-1');

        //选中 主分类列表中 对应的父分类
        $childCategoryElement.prevAll('.level-0').first().prop('selected', true);
        //获取子分类中 对应的应该被选中的子分类
        let $newChildCategoryElement = $childCategoryList.find('option[value="' + $childCategoryElement.val() + '"');
        //选中子分类  + 添加标识类名
        $newChildCategoryElement.prop('selected', true).addClass('child-level');
        //给属于同个父分类的  其余兄弟子分类 添加 标识类名 用来保留
        $newChildCategoryElement.prevUntil('option.level-0').addClass('child-level');
        $newChildCategoryElement.nextUntil('option.level-0').addClass('child-level');

        //移除主分类候选框里的所有没有 标识类名 的选项
        $childCategoryList.find('option:not(.child-level)').remove();
        $childCategoryList.find('option.child-level').each((index, element) => {
            //移除选项文本中的空格字符
            let displayText = $(element).html().replace(/&nbsp;/g, '');
            $(element).html(displayText);
        });

        //获取所有分类列表的父元素
        let $categoryContainer = $mainCategoryList.parent();
        //插入子分类到页面里
        $categoryContainer.append($childCategoryList);


    }

    //最后移除主分类列表里的所有子分类
    $mainCategoryList.find('select option.level-1').remove();
}

/**
 * 根据 提交按钮的可见度 显示悬浮 提交按钮区
 */
function showFixedSubmitButtonOnScroll() {

    //悬浮按钮区
    let $fixedSubmitButtonDiv = $('.fixed-submit-button-div');
    //默认提交按钮
    let $submitButton = $('.wpuf-submit input.wpuf-submit-button');

    //如果提交按钮可见
    if (isVisibleOnScreen($submitButton)) {
        //隐藏悬浮提交按钮
        $fixedSubmitButtonDiv.hide();
    }
    //如果不可见
    else {
        //显示悬浮提交按钮
        $fixedSubmitButtonDiv.show();
    }

}

/**
 * 悬浮提交按钮点击事件
 */
function actionOnFixedSubmitButton() {

    //触发提交按钮的点击事件 (只有在没触发的情况才会触发, 避免重复提交)
    let $submitButton = $('.wpuf-submit input.wpuf-submit-button:not(.button-primary-disabled)');
    if ($submitButton.length) {
        $submitButton.trigger('click');
    }

    $fixedSubmitButton = $(this);

    //注销按钮
    $fixedSubmitButton.toggleDisabled();
    //5秒后恢复
    setTimeout(function () {
        $fixedSubmitButton.toggleDisabled();
    }, 5000);
}


/**
 * 百度秒传链接栏 触发变化事件
 */
function actionOnBaiduFastLink() {

    let value = $(this).val();

    const keywords = '#bdlink=';

    //如果是一键秒传地址
    if (value.includes(keywords)) {

        //从链接中提取出秒传加密数值
        let index = value.indexOf(keywords);
        value = value.substr(index + keywords.length);
        //从base64解密
        value = atob(value);
        //移除特殊字符和中文
        value = value.replace(/[^a-z0-9#.\s]/gi, '_');
        //更新数值
        $(this).val(value);
    }

}


