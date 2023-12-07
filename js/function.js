/// <reference path="common/base.js" />
/// <reference path="function-ajax.js" />
/// <reference path="class/class-comment.js" />
/// <reference path="class/class-message.js" />
/// <reference path="class/class-modal.js" />
/// <reference path="class/class-post.js" />
/// <reference path="class/class-toast.js" />
/// <reference path="class/class-ua-parser.js" />
/// <reference path="class/class-user.js" />


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
 * 更新分类按钮的类名
 * @param {jQuery} $button
 */
function update_category_button_group_class($button) {

    //移除分类组和子分类组里所有选中状态
    $('.array_cat_button_group button.category, .array_sub_cat_group button.category').removeClass('selected');
    //只为当前点击的按钮添加识别类名
    $button.addClass('selected');


}

/**
 * 发送搜索
 * @param {jQuery} $form
 */
function sendSearch($form) {


    const $input = $form.find('input[name="search"]');

    //获取搜索内容
    let searchValue = $input.val();

    //如果搜索内容为空
    if (!searchValue) {
        MyToast.show_error('搜索内容为空');
        return;
    }

    //获取被选中的分类和子分类按钮里的ID
    const main_cat = $form.find('button.main_cat.selected').data('main_cat');
    const sub_cat = $form.find('button.sub_cat.selected').data('sub_cat');

    const data = {};
    //如果cat存在
    if (main_cat) {
        data.main_cat = main_cat;
    }
    //如果sub_cat存在
    else if (sub_cat) {
        data.sub_cat = sub_cat;
    }

    let params = '';
    //如果有分类选项
    if (Object.keys(data).length) {
        params = '?' + $.param(data);
    }

    let path = encodeURIComponent(searchValue);
    location.href = `${MY_SITE.home}/search/${path}${params}`;
}

/**
 * 在顶部菜单中显示 新发布投稿数量
 * @param number
 */
function showNewPostCountInTopMenu(number) {

    //创建元素 并添加到菜单中
    let newBadge = ` <span class="badge text-bg-miku px-2">${number}</span>`;
    $(' .main-menu nav ul li.new-post-count a').append(newBadge);
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
 * APP唤醒链接
 * @param {Event} event
 */
function invokeAppLink(event) {

    //app唤醒链接
    let appInvokeUrl = $(this).data('app-invoke-url');
    //备用网页链接
    let originUrl = $(this).data('origin-url');


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


}

/**
 * 在宽度768px 以下屏幕 触发的动作
 */
function actionOnSmallScreen() {

    //菜单选项 移除滑动事件监听
    $('nav.navbar ul li.dropdown').off('mouseenter').off('mouseleave');


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
                // console.log('广告不存在, 移除元素');
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
 * 仿造 A标签的功能
 */
function openLink(link) {
    window.open(link, '_blank').focus();
}



/**
 * 修正所有A标签的href属性为副域名
 */
function update_a_href_to_secondary_domain() {

    const array_old_domain = SITE_DOMAIN.get_array_site_domain();

    const current_domain = location.host;

    //遍历所有网站用过域名
    for (const old_domain of array_old_domain) {

        //如果域名和当前url不一样
        if (old_domain !== current_domain) {

            //修正所有链接的URL
            $('a[href*="' + old_domain + '"]').each(function (index, element) {
                //更新href
                let href = $(this).attr('href');
                $(this).attr('href', href.replace(old_domain, current_domain));
            });

        }

    }

}

/**
 * 切换所有图片的域名到备用域名
 * @param {string} image_src
 */
function replace_image_src_to_backup_image_domain(image_src) {

    if (image_src) {

        const search_domain = SITE_DOMAIN.get_array_site_domain();
        // 遍历主域名数组，逐一进行替换
        for (const domain of search_domain) {
            image_src = image_src.replace(domain, BACKUP_IMAGE_DOMAIN);
        }

        // 使用正则表达式匹配文件名，把 ww.mikuclub.win / ww.mikuclub.eu 的 地址 改成 file.mikuclub.fun
        // image_src = image_src.replace(/www\.mikuclub\.(win|eu)/g, BACKUP_IMAGE_DOMAIN);

        // 使用正则表达式匹配文件名，把 file*.mikuclub.fun 域名 统一替换成 file.mikuclub.fun
        image_src = image_src.replace(/file\d+\.mikuclub\.fun/g, BACKUP_IMAGE_DOMAIN);
    }

    return image_src;
}

/**
 * 在页面加载完成后 更新页面中 img标签里的src地址
 */
function update_image_src_of_element_to_backup_image_domain() {

    //如果备用图床域名 为开启状态
    if (is_enable_backup_image_domain()) {

        let query_selector = '';
        for (const domain of SITE_DOMAIN.get_array_site_domain()) {
            query_selector += `img[src*="${domain}"],`;
            query_selector += `img[file*="${domain}"],`;
        }

        //抓取所有 使用 file域名的图片元素
        $(query_selector + `
            img[src*="file"], 
            img[file*="file"]
        `).each(function (index, element) {

            //切换到备用域名
            let image_src = $(this).attr('file');
            //如果图片没有file属性
            if (typeof image_src === 'undefined' || image_src === false) {
                //重新抓取 src属性
                image_src = $(this).attr('src');
                image_src = replace_image_src_to_backup_image_domain(image_src);
                //更新src属性
                $(this).attr('src', image_src);
            }
            else {
                image_src = replace_image_src_to_backup_image_domain(image_src);
                //更新file属性
                $(this).attr('file', image_src);
            }


        });

    }

}


/**
 * 修正链接,替换成当前域名
 * @param {string} href
 */
function replace_link_href_to_current_domain(href) {

    if (href) {

        const search_domain = SITE_DOMAIN.get_array_site_domain();
        // 遍历主域名数组，逐一进行替换
        for (const domain of search_domain) {
            href = href.replace(domain, SITE_DOMAIN.get_current_domain);
        }

    }

    return href;
}

/**
 * 把失效的域名跳转到当前的主域名
 */
function redirect_site_domain_deactivated() {

    //获取所有失效的域名
    const array_site_domain = SITE_DOMAIN.get_array_site_domain_disabled();
    //遍历所有失效域名
    for (const domain_disabled of array_site_domain) {
        //如果当前正在访问失效域名
        if (location.host === domain_disabled) {

            //把失效域名 重定向到 当前主域名
            const url = location.href.replace(domain_disabled, SITE_DOMAIN.get_main_domain());
            location.replace(url);

        }

    }


}

/**
 * 创建一个加载占位行
 * @param {jQuery} $parent_component 父元素
 */
function show_loading_row($parent_component) {

    const $loading_row = $(`
        <div class=" loading-row w-100 my-5 text-center">
            <div class="spinner-border fs-4" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `);
    $parent_component.append($loading_row);
}

/**
 * 隐藏加载占位行
 * @param {jQuery} $parent_component 父元素
 */
function hide_loading_row() {
    $('.loading-row').remove();
}

/**
 * 创建一个无内容占位行
 * @param {jQuery} $parent_component 父元素
 */
function show_not_found_row($parent_component) {

    const $not_found_row = $(`
        <div class="not-found-row w-100 my-5 ">
            <div class="text-center fs-5">
                未查找到符合条件的内容
            </div>
        </div>
    `);
    $parent_component.append($not_found_row);
}

/**
 * 自动显示论坛未读消息的图标
 */
function show_wpforo_notification_alert() {

    const $alertIconElement = $("#wpforo #wpforo-wrap .wpf-bar-right .wpf-alerts");
    const query = getQueryParameters();

    //如果是在论坛页, 并且有要求显示消息的参数
    if ($($alertIconElement).length && query.hasOwnProperty('show_notification')) {

        //在消息图标上延时触发点击事件
        setTimeout(function () {
            $alertIconElement.trigger('click');
        }, 500);

    }


}

/**
 * 打开搜索页
 * @param {jQuery} $form 
 */
function open_search_page($form) {

    const search_value = $form.find('input[name="search"]').val();

    //如果搜索内容为空
    if (!search_value) {
        MyToast.show_error('搜索内容为空');
        return;
    }

    const path = encodeURIComponent(search_value);
    location.href = `${MY_SITE.home}/search/${path}`;

}