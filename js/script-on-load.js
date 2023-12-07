/**
 页面加载完成后 自动运行
 */


$(function () {


    //输出名言名句
    // printPhrase();

    //输出当前年份
    printCurrentYear();

    //图片灯箱 自定义配置
    lightbox.option({
        'albumLabel': "第 %1 张 / 总共 %2 张",

        wrapAround: true,
        disableScrolling: true
    });

   

});