/**
 * 浏览历史列表页面专用JS
 */


$(function () {

    //如果发现了相关元素 触发动作
    let $historyPageElement = $('body.page .page-history');
    if ($historyPageElement.length) {
        getHistoryPostList();


        //删除记录
        $historyPageElement.on('click', 'button.delete-favorite', '', deleteFavorite);

        //绑定 清除历史按钮
        $historyPageElement.on('click', 'button.clear_history', '', clearHistory);


        //绑定 下一页按钮点击事件
        $historyPageElement.find('button.get-next-page').on('click', function () {
            getHistoryPostList(false);
        });
        //绑定搜索按钮 和 单选框 , 重新加载列表
        $historyPageElement.find('.inside-search button, .inside-search input.cat').on('click', function () {
            getHistoryPostList(true);
        })
        //绑定搜索栏回车键
        $historyPageElement.find('.inside-search input.search').on('keypress', function (e) {
            //如果键值是回车
            if (e.which == 13) {
                getHistoryPostList(true);
            }
        });;

    }


});


/**
 * 获取收藏文章列表
 * @param {boolean} is_new_load 是否是全新的加载
 */
function getHistoryPostList(is_new_load = true) {

    let $pageElement = $('body.page .page-history');

    let $button = $pageElement.find('button.get-next-page');
    //获取搜索值
    let searchValue = $pageElement.find('.inside-search input.search').val().trim();
    //获取选中的分类单选框数值
    let cat = $pageElement.find('.inside-search input.cat:checked').val();

    //获取当前页数+1
    let paged = $pageElement.data('paged') || 1;

    //如果是新的加载
    //重置分页参数, 页面内容, 和 按钮状态
    if (is_new_load) {
        paged = 1;
        $pageElement.children('.post-list').empty();
        $button.find('.button-text').html('下一页');
        $button.removeDisabled();
    }
    //否则是设置下一页参数
    else {
        paged++;
    }

    //保存分页参数
    $pageElement.data('paged', paged);


    let historyPostArray = getHistoryPostArray();


    //创建请求数据
    let data = {
        paged,
        post__in: historyPostArray,
        orderby: 'post__in',
        no_cache: true, //避免服务端缓存
        ignore_sticky_posts: 1, //需要忽略置顶文章 否则会影响结果
    };

    //设置搜索参数 有的话
    if (searchValue) {
        data.s = searchValue;
    }
    //设置分类参数, 有的话
    if (cat) {
        data.cat = cat;
    }

    //切换按钮的显示
    $button.toggleDisabled();
    $button.children().toggle();


    //回调函数
    let successCallback = function (response) {

        //结果无错误
        if (isNotEmptyArray(response)) {

            //创建自定义文章列表
            let newPostList = new MyPostSlimList(POST_TEMPLATE.historyPost);
            //转换成自定义文章格式
            newPostList.add(response);
            //插入内容到页面中
            $pageElement.children('.post-list').append(newPostList.toHTML());



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
        if ($pageElement.find('.post-list').children().length) {
            buttonText = '已经到最后一页了';
            errorInfo = '没有更多记录了';
        }
        else {
            buttonText = '您还没有任何浏览记录';
            errorInfo = '浏览历史为空';
        }

        MyToast.show_error(errorInfo);
        $button.find('.button-text').html(buttonText);
        $button.toggleDisabled();

    };


    //请求结束后
    let completeCallback = function () {
        //切换按钮激活状态 和 按钮显示内容
        $button.toggleDisabled();
        $button.children().toggle();
    };


    //如果浏览历史数组为空,, 直接显示错误提示
    if (historyPostArray.length === 0) {
        completeCallback();
        notMoreCallback();
    }
    else {
        $.ajax({
            url: URLS.postList,
            data,
            type: HTTP_METHOD.post,
            headers: createAjaxHeader()
        }).done(successCallback).fail(defaultFailCallback).always(completeCallback);
    }

}

/**
 * 清除用户历史
 */
function clearHistory() {

    open_confirm_modal('确认要清除浏览历史吗?', '', () => {

        //清空本地存储里的历史文章数组
        clearHistoryPostArray();

        //刷新页面
        location.reload();

    });
}

