/// <reference path="common/base.js" />
/// <reference path="common/constant.js" /> 
/// <reference path="function-user.js" />

/**
 * 浏览历史列表页面专用JS
 */


$(function () {

    //如果发现了相关元素 触发动作
    let $history_page_element = $('body.page .page-history');
    if ($history_page_element.length) {

        //绑定 清除历史按钮
        $history_page_element.on('click', 'button.clear_history', '', clear_all_history);

    }

});



/**
 * 清除所有浏览历史
 */
function clear_all_history() {

    open_confirm_modal('确认要清除浏览历史吗?', '', () => {

        //清空本地存储里的历史文章数组
        clear_array_history_post_id();
        //添加1个不存在的数字, 来避免过滤无效
        push_in_array_history_post_id(1);

        //刷新页面
        location.reload();

    });
}

