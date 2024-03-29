/**
 * 签到页面专用JS
 */


$(function () {


    //如果发现了相关元素 触发动作
    const $pageQiandaoElement = $('body.page .page-qiandao');
    if ($pageQiandaoElement.length) {

        printQiandaoButton();

        //绑定签到按钮点击事件
        $pageQiandaoElement.on('click', '.qiandao-button', '', setQiandao);

    }

});


function printQiandaoButton() {

    const qiandao = getCookie('qiandao');
    const $container = $('.qiandao-button-container');

    let content;
    //如果没有相关信息
    if (!qiandao) {
        //添加签到按钮
        content = `<button class="btn btn-miku qiandao-button w-50">签到</button>`;
    }
    //否则添加上次签到时间
    else {
        content = `<h5 class="">今天您已经签到成功了, 签到时间为 ${qiandao}</h5>`;
    }

    $container.append(content);

}

/**
 * 设置签到
 */
function setQiandao() {

    //设置cookie名称
    const cookieName = "qiandao";
    //设置cookie日期
    const currentDate = new Date();
    const cookieValue = currentDate.getFullYear() + "年" + (currentDate.getMonth() + 1) + "月" + currentDate.getDate() + "日 " + currentDate.getHours() + "时" + currentDate.getMinutes() + "分" + currentDate.getSeconds() + "秒";

    setCookie(cookieName, cookieValue, 0);

    const $container = $('.qiandao-button-container');
    const content = `<h5 class="">想留下什么评论吗?</h5>`;
    $container.empty().append(content);


    const $textarea = $('textarea[name="comment_content"]');
    if ($textarea.length) {
        //创建评论内容+获取焦点
        $textarea.val(` 签到成功 (=・ω・=) , 签到时间为 ${cookieValue} `);
        $textarea.trigger('focus');
    }


}

