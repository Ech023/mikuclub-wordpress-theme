/**
 * 收藏列表页面专用JS
 */


$(function () {

    //如果发现了相关元素 触发动作
    let $favoritePageElement = $('body.page .page-favorite');
    if ($favoritePageElement.length) {
        getFavoritePostList(true);
        //绑定 按钮点击, 加载下一页
        $favoritePageElement.find('button.get-next-page').on('click', '', '', () => {
            getFavoritePostList(false);
        });
        $favoritePageElement.on('click', 'button.delete-favorite', '', deleteFavorite);

        //绑定搜索按钮 和 单选框 , 重新加载列表
        $favoritePageElement.find('.inside-search button, .inside-search input.cat').on('click', function () {
            getFavoritePostList(true);
        })
        //绑定搜索栏回车键
        $favoritePageElement.find('.inside-search input.search').on('keypress', function (e) {
            //如果键值是回车
            if (e.which == 13) {
                getFavoritePostList(true);
            }
        });;

    }


});



/**
 * 获取收藏文章列表
 * @param {boolean} is_new_load 是否是全新的加载
 */
function getFavoritePostList(is_new_load = true) {

    let $pageElement = $('body.page .page-favorite');

    let $button = $pageElement.find('button.get-next-page');
    let searchValue = $pageElement.find('.inside-search input.search').val().trim();

    //获取选中的分类单选框数值
    let cat = $pageElement.find('.inside-search input.cat:checked').val();

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

    //创建请求数据
    let data = {
        paged
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
            let newPostList = new MyPostSlimList(POST_TYPE.favoritePost);
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
            errorInfo = '没有更多投稿了';
        } else {
            buttonText = '没有找到相关内容';
            errorInfo = '没有找到相关内容';
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


    $.ajax({
        url: URLS.favoritePostList,
        data,
        type: HTTP_METHOD.get,
        headers: createAjaxHeader()
    }).done(successCallback).fail(defaultFailCallback).always(completeCallback);


}

/**
 * 取消收藏动作
 * @param {Event} event
 */
function deleteFavorite(event) {

    //确认窗口
    if (!confirm('确认要取消收藏吗?')) {
        return;
    }

    //获取按钮
    const $button = $(this);

    let $grandparentElement = $button.parent().parent();

    //创建请求数据
    let data = {
        post_id: $button.data('post-id'),
    };

    //注销按钮
    $button.toggleDisabled();
    $button.children().toggle();

    //回调函数
    let successCallback = function (response) {

        //隐藏当前爷爷元素
        $grandparentElement.fadeOut(300);
        //创建通知弹窗
        MyToast.show_success('已取消收藏');

    };


    /**
     * 错误情况
     */
    let failCallback = function () {

        //创建通知弹窗
        MyToast.show_error('请求错误 请重试');

        //重新激活按钮
        $button.toggleDisabled();
        $button.children().toggle();
    };


    $.ajax({
        url: URLS.favorite,
        data,
        type: HTTP_METHOD.delete,
        headers: createAjaxHeader()
    }).done(successCallback).fail(failCallback);


}

