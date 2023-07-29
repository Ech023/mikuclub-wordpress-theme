/**
 * 作者个人主页 专用JS
 */


$(function () {


        //当前是在 作者页面
    let $pageElement = $('body.author');
    if ($pageElement.length) {

        //内部搜索按钮点击事件
        $pageElement.find('.author-internal-search button').on('click', '', '', authorInternalSearch);
    }


});


/**
 * 用关键词搜索作者的相关投稿
 */
function authorInternalSearch() {

    //获取当前URL查询参数的对象
    let queryObject = getQueryParameters();

    let $input = $('.author-internal-search input.search-value');

    //获取自定义排序参数
    let name = $input.attr('name');
    let value = $input.val().trim();


    //添加到查询对象
    queryObject[name] = value.trim();

    //跳转
    refreshPostListPage(queryObject);


}
