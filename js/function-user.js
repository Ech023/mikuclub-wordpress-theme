/// <reference path="common/base.js" />
/// <reference path="common/constant.js" />
/// <reference path="class/class-comment.js" />
/// <reference path="class/class-message.js" />
/// <reference path="class/class-modal.js" />
/// <reference path="class/class-post.js" />
/// <reference path="class/class-toast.js" />
/// <reference path="class/class-ua-parser.js" />
/// <reference path="class/class-user.js" />


/*
和用户相关的操作
*/


/**
 * 加黑名单
 * @param {number} target_user_id
 */
function add_user_black_list(target_user_id) {

    if(!MY_SITE.user_id){
        MyToast.show_error('请先进行登陆');
        return;
    }

    open_confirm_modal('确认要将该用户添加到黑名单里吗?', '添加后对方将无法在你的投稿里评论/无法发私信给你/对方的投稿将会被遮盖, 在个人的用户信息页里可以管理黑名单', () => {

        const data = {
            target_user_id,
        }

        send_post(
            URLS.userBlackList,
            data,
            () => {
                show_loading_modal();
            },
            () => {
                MyToast.show_success('添加黑名单成功');
            },
            () => {
                MyToast.show_error('添加黑名单失败');
            },
            () => {
                hide_loading_modal();
            }
        );

    });

}

/**
 * 移除黑名单
 * @param {number} target_user_id
 */
function delete_user_black_list(target_user_id) {

    if(!MY_SITE.user_id){
        MyToast.show_error('请先进行登陆');
        return;
    }

    open_confirm_modal('确认要将该用户从黑名单里移除吗?', '', () => {

        const data = {
            target_user_id,
        }

        send_delete(
            URLS.userBlackList,
            data,
            () => {
                show_loading_modal();
            },
            () => {
                MyToast.show_success('移除黑名单成功');
            },
            () => {
                MyToast.show_error('移除黑名单失败');
            },
            () => {
                hide_loading_modal();
            }
        );

    });


}




/**
 * 添加关注
 * @param {jQuery} $button
 */
function add_user_follow_list($button) {

    if(!MY_SITE.user_id){
        MyToast.show_error('请先进行登陆');
        return;
    }

    //取消关注按钮
    const $delete_follow_button = $button.siblings('button.delete-user-follow-list');
    //取消关注按钮的关注数子元素
    const $user_fans_count_element = $delete_follow_button.children('.user-fans-count');
    //获取当前关注数
    const $button_container_element = $button.parent();
    const user_fans_count = $button_container_element.data('user-fans-count') + 1;
    $button_container_element.data('user-fans-count', user_fans_count);

    //获取要关注的用户ID
    const target_user_id = $button.data('target-user-id');


    //添加为请求数据
    const data = {
        target_user_id,
    }

    send_post(
        URLS.userFollowed,
        data,
        () => {
            show_loading_modal();
        },
        () => {
            MyToast.show_success('已添加关注');

            //隐藏当前按钮
            $button.hide();
            //显示取消关注按钮
            $delete_follow_button.show();
            //更新取消关注按钮的关注数
            $user_fans_count_element.html(user_fans_count);

        },
        () => {
            MyToast.show_error('添加关注失败');
        },
        () => {
            hide_loading_modal();
        }
    );


}

/**
 * 移除关注
 * @param {jQuery} $button
 */
function delete_user_follow_list($button) {

    if(!MY_SITE.user_id){
        MyToast.show_error('请先进行登陆');
        return;
    }

    //添加关注按钮
    const $add_follow_button = $button.siblings('button.add-user-follow-list');
    //添加关注按钮的关注数子元素
    const $user_fans_count_element = $add_follow_button.children('.user-fans-count');
    //获取当前关注数
    const $button_container_element = $button.parent();
    const user_fans_count = $button_container_element.data('user-fans-count') - 1;
    $button_container_element.data('user-fans-count', user_fans_count);

    //获取要关注的用户ID
    const target_user_id = $button.data('target-user-id');


    //添加为请求数据
    const data = {
        target_user_id,
    }


    send_delete(
        URLS.userFollowed,
        data,
        () => {
            show_loading_modal();
        },
        () => {
            MyToast.show_success('已取消关注');

            //隐藏当前按钮
            $button.hide();
            //显示关注按钮
            $add_follow_button.show();
            //更新关注按钮的关注数
            $user_fans_count_element.html(user_fans_count);

        },
        () => {
            MyToast.show_error('取消关注失败');
        },
        () => {
            hide_loading_modal();
        }
    );

}


/**
 * 获取浏览记录数组
 * @return {array}
 */
function getHistoryPostArray() {

    //从本地存储获取浏览记录
    let history = getLocalStorage(LOCAL_STORAGE_KEY.postHistory);
    //如果浏览记录数组为空
    if (!history) {
        history = [];
    }

    return history;


}

/**
 * 设置浏览记录
 * @param {int} postId
 */
function setHistoryPostArray(postId) {

    const HISTORY_LENGTH = 200;

    //如果ID为空 结束函数
    if (!postId) {
        return;
    }

    //获取浏览记录
    let history = getHistoryPostArray();
    //如果浏览记录超过最大长度
    if (history.length >= HISTORY_LENGTH) {
        //移除最后一个元素
        history.pop();
    }

    //过滤掉已存在与数组中的同iD
    history = history.filter(element => (+element) !== (+postId));
    //添加ID到头部
    history.unshift(postId);

    //保存新的浏览记录到本地数组中
    setLocalStorage(LOCAL_STORAGE_KEY.postHistory, history);


}

/**
 * 清除浏览记录
 */
function clearHistoryPostArray() {
    //清除本地储存的历史数组
    setLocalStorage(LOCAL_STORAGE_KEY.postHistory, []);
}
