/**
 * 签到页面专用JS
 */


$(function () {


    //如果发现了相关元素 触发动作
    let $pageQiandaoElement = $('body.page .page-qiandao');
    if ($pageQiandaoElement.length) {

        printQiandaoButton();

        //绑定签到按钮点击事件
        $pageQiandaoElement.on('click', '.qiandao-button','', setQiandao);

    }

});


function printQiandaoButton() {

    let qiandao = getCookie('qiandao');

    let $container = $('.qiandao-button-container');

    let content;
    //如果没有相关信息
    if (!qiandao) {
        //添加签到按钮
        content = `<button class="btn btn-miku qiandao-button w-50">签到</button>`;
    }
    //否则添加上次签到时间
    else{
        content = `<h4 class="">今天您已经签到成功了, 签到时间为 ${qiandao}</h4>`;
    }

    $container.append(content);

}

/**
 * 设置签到
 */
function setQiandao(){

    //设置cookie名称
    let cookieName = "qiandao";
    //设置cookie日期
    let currentDate = new Date();
    let cookieValue = currentDate.getFullYear() + "年" + (currentDate.getMonth() + 1) + "月" + currentDate.getDate() + "日 " + currentDate.getHours() + "时" + currentDate.getMinutes() + "分" + currentDate.getSeconds() + "秒";

    setCookie(cookieName, cookieValue, 0);

    let $container = $('.qiandao-button-container');
    let content =   `<h4 class="">发射评论,留下你的痕迹吧!</h4>`;
    $container.empty().append(content);


    let $textarea = $('textarea[name="comment_content"]');
    if($textarea.length){
        //创建评论内容+获取焦点
        $textarea.val(` 签到成功 (=・ω・=) , 签到时间为 ${cookieValue} `);
        $textarea.trigger('focus');
    }


}

