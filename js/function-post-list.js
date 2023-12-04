/// <reference path="common/base.js" />
/// <reference path="common/constant.js" /> 

let $post_list_container_component;

$(function () {

    $post_list_container_component = $('.post-list-container');

    //如果文章列表容器存在
    if ($post_list_container_component.length) {
        //绑定滚动事件
        $(document).on('scroll', function () {

            //如果可以看到列表底部
            if (isVisibleOnScreen($post_list_container_component.find('.post_list_footer'))) {
                //触发自动加载
                get_post_list();
            }

        });
    }

});

/**
 * 加载文章列表
 * @param {bool} is_new_load true = 清空列表, false = 在现有基础上添加
 */
function get_post_list(is_new_load = false) {
    
    //如果是重新加载, 重置end属性
    if (is_new_load) {
        get_post_list.is_end = false;
    }

    //如果已经在请求了
    if (get_post_list.is_end || get_post_list.is_loading) {
        //中断函数 避免重复请求
        return;
    }
    //开启loading属性
    get_post_list.is_loading = true;

    const $post_list_component = $post_list_container_component.find('.post-list');
    const data = $post_list_container_component.data('parameters');

    //如果是搜索词语, 需要解义
    if (data.s) {
        data.s = decodeURIComponent(parameters.s);
        //关闭缓存
        data.no_cache = true;
    }

    //获取当前页数
    data.paged = parseInt(data.paged) || 1;
    //如果是清空列表
    if (is_new_load) {
        //清空当前列表
        $post_list_component.empty();
        //重设为1
        data.paged = 1;
    }
    else {
        data.paged++;
    }



    const success_callback = (response) => {

        //不是空的
        if (isNotEmptyArray(response)) {

            //创建自定义文章列表
            const newPostList = new MyPostSlimList(POST_TYPE.post);
            //转换成自定义文章格式
            newPostList.add(response);

            if (is_new_load) {
                //再清空一次当前列表 避免多次请求下导致错误
                $post_list_component.empty();
            }
            //输出成html加入到页面里
            $post_list_component.append(newPostList.toHTML());

            //更新参数
            $post_list_container_component.data('parameters', data);

        }
        //无内容的情况
        else {
            MyToast.show_success('列表已全部加载');
            //添加end属性 用来避免继续尝试
            get_post_list.is_end = true;
        }

    };

    send_get(
        URLS.postList,
        data,
        () => {
            show_loading_row($post_list_container_component);
        },
        success_callback,
        (jqXHR) => {

            defaultFailCallback(jqXHR);
        },
        () => {
            hide_loading_row();
            //关闭loading属性
            get_post_list.is_loading = false;
        }

    )
}