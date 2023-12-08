/**
 * UP管理投稿页面专用JS
 */


$(function () {

    //如果发现了相关元素 触发动作
    let $uploaderPageElement = $('body.page .page-uploader');
    if ($uploaderPageElement.length) {

        getManagePostList();

        //绑定 下一页按钮点击事件
        $uploaderPageElement.find('button.get-next-page').on('click', '', '', getManagePostList);
        

        //绑定搜索按钮事件
        $uploaderPageElement.on('click', 'button.search-post-button', '', sendManagePostListSearch);

    }


});

/**
 * 重置列表状态 然后发送搜索请求
 */
function sendManagePostListSearch() {

    let $pageElement = $('body.page .page-uploader');
    //重置页数
    $pageElement.find('input[name="paged"]').val(0);
    //清空列表
    $pageElement.children('.post-list').empty();

    //如果按钮是注销状态  重置按钮状态
    let $button = $pageElement.find('button.get-next-page');
    if ($button.attr('disabled')) {
        $button.removeAttr('disabled');
        $button.children('.button-text').html('下一页').show();
        $button.children('.button-loading').hide();
    }

    getManagePostList();

}

/**
 * 获取管理文章列表
 */
function getManagePostList() {

    let $pageElement = $('body.page .page-uploader');

    let $button = $pageElement.find('button.get-next-page');
    let $pageInput = $pageElement.find('input[name="paged"]');


    //获取当前页数+1
    let paged = parseInt($pageInput.val()) + 1;
    //获取当前用户ID
    let author = $pageElement.find('input[name="author"]').val();
    //获取搜索词
    let search = $pageElement.find('input[name="search-post"]').val();


    //创建请求数据
    let data = {
        paged,
        author,
        post_status: ['publish', 'pending', 'draft'], //获取所有状态的文章
        no_cache: true, //禁用缓存
    };

    //只有在搜索词不是空的时候
    if (search) {
        data.s = search;
    }
    console.log("获取用户投稿列表");

    //切换按钮的显示
    $button.toggleDisabled();
    $button.children().toggle();

    //回调函数
    let successCallback = function (response) {

        //确保不是空内容
        if (isNotEmptyArray(response.posts)) {

            //创建自定义文章列表
            let newPostList = new MyPostSlimList(POST_TEMPLATE.managePost);
            //转换成自定义文章格式
            newPostList.add(response.posts);
            //插入内容到页面中
            $pageElement.children('.post-list').append(newPostList.toHTML());

            //更新页码
            $pageInput.val(paged);

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
        }
        else {
            buttonText = '您还没有创建过任何投稿';
            errorInfo = '投稿列表为空';
        }

        MyToast.show_error(errorInfo);
        $button.children('.button-text').html(buttonText);
        $button.toggleDisabled();

    };


    //请求结束后
    let completeCallback = function () {
        //切换按钮激活状态 和 按钮显示内容
        $button.toggleDisabled();
        $button.children().toggle();
    };


    $.ajax({
        url: URLS.postList,
        data,
        type: HTTP_METHOD.get,
        headers: createAjaxHeader()
    }).done(successCallback).fail(defaultFailCallback).always(completeCallback);


}

