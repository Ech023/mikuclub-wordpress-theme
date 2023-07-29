/***
 * JS函数文件
 */

/**
 * 让未登录用户跳转离开魔法区
 */
function mofaRedirect() {
    //成人区分类ids
    let adultCategoryIds = [
        1120,
        3055,
        211,
        1741,
        1121,
        1192,
        5998,
        6678,
        6713,
    ];

    //如果是未登录用户
    if (parseInt(MY_SITE.user_id) === 0) {
        //不是搜索引擎  + 在成人区分类或成人区文章页面
        if (!isSearchSpider() && (adultCategoryIds.includes(parseInt(MY_SITE.post_main_cat)) || adultCategoryIds.includes(parseInt(MY_SITE.category_id)))) {
            //跳转
            window.location.href = MY_SITE.home + '/404?mofa=true';
        }

    }

}

/**
 * 检测用户是否是搜索引起的蜘蛛
 * @return boolean
 */
function isSearchSpider() {
    //搜索引擎列表
    let searchSpiderlist = [
        'mediapartners',
        'google',
        'baiduspider',
        'yisouspider',
        'www.sogou.com',
        'sospider',
        '360spider',
        'haosouspider',
        'easou',
        'yisou',
        'bingbot',
        'yahoo.com',
        'spider',
    ];
    let isSpider = false;
    let userAgent = navigator.userAgent.toLowerCase();
    //检测是否是搜索引擎
    for (let i = 0; i < searchSpiderlist.length; i++) {
        if (userAgent.indexOf(searchSpiderlist[i]) !== -1) {
            isSpider = true;
            i = 99;
        }
    }
    return isSpider;

}


/**
 * 发送搜索
 * @param form
 */
function sendSearch(form) {
    "use strict";

    //获取搜索内容
    let searchValue = form.search.value.trim();

    //如果搜索内容为空
    if (!searchValue) {
        TOAST_SYSTEM.add('搜索内容为空', TOAST_TYPE.error);
        return;
    }

    //如果有分类选项 并且有选择特定分类
    let params = '';
    if (form.hasOwnProperty('cat') && form.cat.value > 0) {
        params = `?${$.param({ cat: form.cat.value })}`;
    }

    let path = encodeURIComponent(searchValue);
    location.href = `${MY_SITE.home}/search/${path}${params}`;

}

/**
 * 在顶部菜单中显示 新发布投稿数量
 * @param number
 */
function showNewPostCountInTopMenu(number) {
    "use strict";

    //创建元素 并添加到菜单中
    let newBadge = ` <span class="badge bg-miku px-2">${number}</span>`;
    $('#nav-header nav ul li.new-post-count a').append(newBadge);
}


/**
 * 切换显示下拉菜单
 * @param navItem
 * @param isAdd
 */
function toggleDropDownMenu(navItem, isAdd) {


    //切换类名 来显示下拉列表
    if (isAdd) {
        $(navItem).addClass('show').children('.dropdown-menu').show();
    } else {

        $(navItem).removeClass('show').children('.dropdown-menu').hide();
    }

}

/**
 * 输出名言名句
 */
function printPhrase() {

    let randomIndex = Math.floor(Math.random() * phrases.length);
    $("#custom-phrase").html(`"${phrases[randomIndex]}"`);
}

/**
 * 输出当前年份
 */
function printCurrentYear() {

    let year = new Date().getFullYear();
    $("#current-year").html(year);

}

/**
 * 分页导航跳转功能
 */
function changePagingPage() {

    let $input = $('.change-page input.change-page-value');
    //获取要跳转的页码
    let paged = $input.val();
    if (paged) {

        //提取当前链接地址, 然后替换掉123456占位符
        let href = $('.change-page input.change-page-href').val().replace('123456', paged);
        //跳转
        location.href = href;

    } else {
        $input.trigger('focus');
        TOAST_SYSTEM.add('请先输入页数', TOAST_TYPE.error);
    }


}

/**
 * 限制用户输入在input大小限制内
 */
function respectInputValueRange() {

    let max = parseInt($(this).attr('max'));
    let min = parseInt($(this).attr('min'));
    let value = $(this).val();
    if (value > max) {
        value = max;
    } else if (value.val() < min) {
        value = min;
    }

    //修正页码数值
    $(this).val(value);

}

/**
 * 使用自定义列表排序参数 来刷新页面
 */
function postListCustomOrder() {

    //获取当前URL查询参数的对象
    let queryObject = getQueryParameters();
    $('.post-list-order select').each((index, element) => {
        //获取自定义排序参数
        let name = $(element).attr('name');
        let value = $(element).val();

        queryObject[name] = value;

    });

    refreshPostListPage(queryObject);

}


/**
 * 使用新参数重新刷新文章列表页面
 * @param {Object} queryObject
 */
function refreshPostListPage(queryObject) {

    //遍历对象, 删除键值为空的 键值对
    let keys = Object.keys(queryObject);
    for (let i = 0; i < keys.length; i++) {
        if (!queryObject[keys[i]]) {
            delete queryObject[keys[i]];
        }
    }

    //重新生成URL, 移除可能存在的page页数变量
    let url = `${location.protocol}//${location.host}${location.pathname.split('/page')[0].toString()}/page/1`;

    //如果有请求参数
    if (Object.keys(queryObject).length) {
        //把请求参数对象转换成字符串加入url
        url += `?${$.param(queryObject)}`;
    }

    //跳转
    location.href = url;
}


/**
 * 创建私信模态窗
 */
function createPrivateMessageModal(event) {

    //获取收件人信息
    let recipient_name = $(this).siblings('input[name="recipient_name"]').val();
    let recipient_id = $(this).siblings('input[name="recipient_id"]').val();

    //console.log('nome' +recipient_name);
    //console.log('id' +recipient_id);

    //显示模态窗
    new MyPrivateMessageModal(recipient_id, recipient_name).show();


}

/**
 * APP唤醒链接
 * @param {Event} event
 */
function invokeAppLink(event) {

    let $linkElement = $(event.target);
    if (!$linkElement.is('a')) {
        $linkElement = $linkElement.parent();
    }

    //app唤醒链接
    let appInvokeUrl = $linkElement.data('app-invoke-url');
    //备用网页链接
    let originUrl = $linkElement.data('origin-url');


    //如果为空 则直接使用备用链接
    if (!appInvokeUrl) {
        appInvokeUrl = originUrl;
    }
    //打开新窗口尝试唤醒APP
    let newWindow = window.open(appInvokeUrl);

    //定时2秒后
    setTimeout(function () {
        //再跳转到备用网页链接
        newWindow.location.replace(originUrl);
    }, 2000);

}

/**
 * 随机显示或隐藏元素
 */
function randomDisplayElement() {

    $elements = $(".random-display");
    $elements.each(function (index) {
        //获取显示几率
        let percente = $(this).data('percente');
        let randomValue = Math.floor(Math.random() * 101);
        //元素将在 随机数小于显示几率的情况才显示
        if (percente && randomValue < percente) {
            $(this).hide();
        } else {
            $(this).show();
        }
    });


}


/**
 * 根据窗口大小触发的动作
 */
function actionOnBrowserSize() {

    let windowWidth = $(window).width() + 15; //需要手动加15px (进度条宽度) 才能获得实际大小
    let windowHeight = $(window).height();

    if (windowWidth >= 768) {
        actionOnMediumScreen();
    } else {
        actionOnSmallScreen();
    }


}


/**
 * 在宽度768px 以上屏幕 触发的动作
 */
function actionOnMediumScreen() {

    //菜单选项 增加滑动事件监听
    $('nav.navbar ul li.dropdown').on('mouseenter', function () {

        toggleDropDownMenu(this, true);
    }).on('mouseleave', function () {

        toggleDropDownMenu(this, false);
    });

    //临时雪花特效
    //显示雪花
    $('#websnowjqcan').show();

}

/**
 * 在宽度768px 以下屏幕 触发的动作
 */
function actionOnSmallScreen() {

    //菜单选项 移除滑动事件监听
    $('nav.navbar ul li.dropdown').off('mouseenter').off('mouseleave');

    //临时雪花特效
    //隐藏雪花
    $('#websnowjqcan').hide();

}

/**
 * 隐藏空白谷歌广告 (只隐藏0px高度的广告)
 * @param event
 * @link https://stackoverflow.com/questions/49677547/how-to-programmatically-collapse-space-in-empty-div-when-google-ad-does-not-show
 */
function hideEmptyAdSense() {

    let $adsense = $('div ins.adsbygoogle');
    if ($adsense.length) {
        $adsense.each((index, element) => {
            //console.log('检测谷歌广告');
            if ($(element).css('height') === '0px') {
                console.log('广告不存在, 移除元素');
                $(element).remove();
            } else {
                //console.log('广告存在');
            }

        });
    }

}

/**
 * 获取浏览记录数组
 * @return {array}
 */
function getHistoryPostArray() {

    //从本地存储获取浏览记录
    let history = getLocalStorage(LOCAL_STORAGE_KEY.postHistory);
    //如果浏览记录数组为空
    if (!history) {
        history = [];
    }


    return history;


}

/**
 * 设置浏览记录
 * @param {int} postId
 */
function setHistoryPostArray(postId) {

    const HISTORY_LENGTH = 200;

    //如果ID为空 结束函数
    if (!postId) {
        return;
    }

    //获取浏览记录
    let history = getHistoryPostArray();
    //如果浏览记录超过最大长度
    if (history.length >= HISTORY_LENGTH) {
        //移除最后一个元素
        history.pop();
    }

    //过滤掉已存在与数组中的同iD
    history = history.filter(element => (+element) !== (+postId));
    //添加ID到头部
    history.unshift(postId);

    //保存新的浏览记录到本地数组中
    setLocalStorage(LOCAL_STORAGE_KEY.postHistory, history);


}

/**
 * 清除浏览记录
 */
function clearHistoryPostArray() {
    //清除本地储存的历史数组
    setLocalStorage(LOCAL_STORAGE_KEY.postHistory, []);
}


/**
 * 滚动的时候显示浮动菜单 并在 5秒 后隐藏
 */
function showSidebarMenuOnScroll() {

    //如果有旧的定时任务
    if (showSidebarMenuOnScroll.timeout) {
        //清除
        clearTimeout(showSidebarMenuOnScroll.timeout);
    }

    $('#fixed-sidebar-menu').fadeIn('slow');

    //设置新的定时任务
    showSidebarMenuOnScroll.timeout = setTimeout(function () {
        //延时隐藏
        $('#fixed-sidebar-menu').fadeOut('slow');
    }, 3000);

}

/**
 * 根据当前时间 显示浮动菜单里的日夜模式切换按钮
 */
function showSidebarMenuUiModeButton() {

    //获取悬浮菜单
    let $sidebarMenu = $('#fixed-sidebar-menu');

    let currentHours = new Date();

    //如果是晚上
    if (currentHours < 7 || currentHours > 19 || true) {

        let $item = $(`
            <a class="switch-ui-mode list-group-item list-group-item-action " href="javascript:void(0)" >
                关灯
            </a>
        `);

        $sidebarMenu.prepend($item);
    }

}

/**
 * 仿造 A标签的功能
 */
function openLink(link) {
    window.open(link, '_blank').focus();
}



/**
 * 修正所有A标签的href属性为副域名
 */
function update_a_href_to_secondary_domain() {

    const array_old_domain = [
        'www.mikuclub.online',
        'www.mikuclub.eu',
        'www.mikuclub.win',
        'www.mikuclub.cc',
    ];

    const current_domain = location.host;
    /*
    //如果当前不是主域名
    if (origin_domain !== current_domain) {

        //替换为副域名
        $('a[href*="' + origin_domain + '"]').each(function (index, element) {
            //更新href
            let href = $(this).attr('href');
            $(this).attr('href', href.replace(origin_domain, current_domain));
        });

    }
    //如果是主域名
    else{*/

    //修正所有老链接
    for (const old_domain of array_old_domain) {

        //如果域名不一样
        if (old_domain !== current_domain) {

            $('a[href*="' + old_domain + '"]').each(function (index, element) {
                //更新href
                let href = $(this).attr('href');
                $(this).attr('href', href.replace(old_domain, current_domain));
            });

        }

    }

    //}






}