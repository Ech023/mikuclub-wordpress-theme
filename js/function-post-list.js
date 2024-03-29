/// <reference path="common/base.js" />
/// <reference path="common/constant.js" /> 

let $post_list_container_component;

$(function () {

    $post_list_container_component = $('.post-list-container');
    const $history_page = $('body.page .page-history');

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

        //如果是历史页面, 
        if ($history_page.length) {
            // 用个性化加载文章列表
            const array_history_post_id = get_array_history_post_id();
            update_post_list_component_data({
                post__in: array_history_post_id,
                custom_orderby: 'post__in',
            })

            get_post_list();
        }
        //其他页面
        else {
            //用默认方式加载文章列表
            get_post_list();
        }

    }



});

/**
 * 加载文章列表
 * @param {bool} is_new_load true = 重新加载列表 (清空列表), false = 在现有基础上添加
 * @param {bool} new_paged 在重新加载列表的时候 指定页码
 */
function get_post_list(is_new_load = false, new_paged = 1) {

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
        data.s = decodeURIComponent(data.s);
        //关闭缓存
        // data.no_cache = true;
    }

    //获取当前页数
    data.paged = parseInt(data.paged) || 1;
    //如果是清空列表
    if (is_new_load) {
        //清空当前列表
        $post_list_component.empty();
        //重设为新页数
        data.paged = new_paged;
    }




    const success_callback = (response) => {

        //不是空的
        if (isNotEmptyArray(response.posts)) {

            const post_template = get_post_list_component_post_template();
            //创建自定义文章列表
            const newPostList = new MyPostSlimList(post_template);
            //转换成自定义文章格式
            newPostList.add(response.posts);

            if (is_new_load) {
                //再清空一次当前列表 避免多次请求下导致错误
                $post_list_component.empty();
                MyToast.show_success('当前页数' + data.paged + '/' + response.max_num_pages + '总页数');
            }

            //限制文章列表的总长度
            const $array_post_element = $post_list_component.children();
            if ($array_post_element.length > MAX_POST_LIST_LENGTH) {
                //移除最旧的那一页
                $array_post_element.slice(0, POST_LIST_LENGTH).remove();
            }

            //输出成html加入到页面里
            $post_list_component.append(newPostList.toHTML());


            //更新列表容器的最大页数
            update_post_list_component_max_num_pages(response.max_num_pages);
            //更新跳转按钮的显示页数
            update_button_open_change_paged_modal_paged(data.paged);

            //更新参数
            data.paged++;
            update_post_list_component_data(data);
        }
        //无内容的情况
        else {
            MyToast.show_success('无更多结果');
            //添加end属性 用来避免继续尝试
            get_post_list.is_end = true;

            //如果列表原本就是空的
            if ($post_list_component.children().length === 0) {
                //显示无结果
                show_not_found_row($post_list_component);
            }
        }

    };

    const pre_callback = () => {
        show_loading_row($post_list_container_component);
        //禁用过滤/排序/搜索按钮
        disable_filter_buttons();
    };
    const always_callback = () => {
        hide_loading_row();
        //启用过滤/排序/搜索按钮
        enable_filter_buttons();
        //关闭loading属性
        get_post_list.is_loading = false;
    }

    //如果请求参数包含超长数组, 改用POST请求, 否则默认用GET请求
    const long_data_key = [
        'post__in',
    ];
    const has_long_data = long_data_key.some(function (prop) {
        return data.hasOwnProperty(prop);
    });

    if (has_long_data) {
        send_post(
            URLS.postList,
            data,
            pre_callback,
            success_callback,
            defaultFailCallback,
            always_callback
        );
    }
    else {
        send_get(
            URLS.postList,
            data,
            pre_callback,
            success_callback,
            defaultFailCallback,
            always_callback
        );
    }

}

/**
 * 更新 文章列表容器的请求参数
 * @param {object} new_data 
 */
function update_post_list_component_data(new_data) {


    const $post_list_container_component = $('.post-list-container');
    const data = $post_list_container_component.data('parameters');
    Object.assign(data, new_data);
    $post_list_container_component.data('parameters', data);

}

/**
 * 获取 文章列表容器的当前页数和最大页数
 * @returns {object}
 * [
 *  'paged' => int,
 *  'max_num_pages' => int,
 * ]
 */
function get_post_list_component_paged_and_max_num_pages() {

    const $post_list_container_component = $('.post-list-container');
    const data = $post_list_container_component.data('parameters');
    const max_num_pages = $post_list_container_component.data('max_num_pages');

    return {
        paged: data.paged || 1,
        max_num_pages: max_num_pages || 0,
    }
}

/**
 * 更新 文章列表容器的最大页数
 * @param {number} max_num_pages 
 */
function update_post_list_component_max_num_pages(max_num_pages) {
    const $post_list_container_component = $('.post-list-container');
    $post_list_container_component.data('max_num_pages', parseInt(max_num_pages));
}

/**
 * 获取列表的文章类型模板
 * @returns {string}
 */
function get_post_list_component_post_template() {

    const $post_list_container_component = $('.post-list-container');
    const post_template = $post_list_container_component.data('post-template');

    return post_template || POST_TEMPLATE.default;
}