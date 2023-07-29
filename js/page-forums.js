/**
 * 论坛 专用JS
 */


$(function () {


        //当前是在 论坛页面
    let $forumsElement = $('#wpforo');
    if ($forumsElement.length) {

        //隐藏论坛用户信息页面更换封面按钮
        $forumsElement.find('.wpforo-profile').find('.wpf-edit-cover').addClass('d-none');

        //隐藏多余的附件图片上传按钮和提示信息
        //$forumsElement.find('.wpf_attach_button_wrap').find('.fa-paperclip.wpfa-form-ico').addClass('d-none');
        //$forumsElement.find('.wpf_attach_button_wrap').find('.wpfa-browse').addClass('d-none');
        //$forumsElement.find('.wpf_attach_button_wrap').find('.wpf_dd_info').addClass('d-none');
    }


});


