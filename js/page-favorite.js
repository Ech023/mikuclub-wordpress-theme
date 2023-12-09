/**
 * 收藏列表页面专用JS
 */


$(function () {

    //如果发现了相关元素 触发动作
    let $favoritePageElement = $('body.page .page-favorite');
    if ($favoritePageElement.length) {

        $favoritePageElement.on('click', 'button.delete_favorite', '', function () {
            delete_favorite_from_favorite_page($(this))
        });

    }


});




/**
 * 取消收藏动作
 * @param {jQuery} $button
 */
function delete_favorite_from_favorite_page($button) {

    open_confirm_modal('确认要取消收藏吗?', '', () => {

        const $post_element = $button.closest('.post-element');

        //创建请求数据
        const data = {
            post_id: $button.data('post_id'),
        };

        //回调函数
        const success_callback = (response) => {

            //隐藏文章元素
            $post_element.fadeOut(300);
            //创建通知弹窗
            MyToast.show_success('已取消收藏');

        };

        send_delete(
            URLS.favorite,
            data,
            () => {
                show_loading_modal();
            },
            success_callback,
            defaultFailCallback,
            () => {
                hide_loading_modal();
            }
        );

    });
}

