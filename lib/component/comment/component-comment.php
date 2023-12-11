<?php

/**
 * 评论组件
 */

namespace mikuclub;


/**
 * 输出评论输入框
 *
 * @param int $post_id
 * @return string
 */
function print_comment_input_box($post_id)
{

    $output = '';

    $user_id = get_current_user_id();
    $avatar = print_user_avatar(get_my_user_avatar($user_id) ?: get_my_user_default_avatar(), 40);

    $placeholder = $user_id ? '输入评论' : '请登陆后再输入评论'; 
    //如果用户未登陆, 禁止输入 和隐藏按钮
    $textarea_attribute =  $user_id ? '' : 'disabled';
    $class_button_row = $user_id ? '' : 'd-none';

    //如果文章有开启评论
    if (comments_open())
    {
        $output =<<<HTML

            <div class="comment-form-container main-form-container row my-2">

                <div class="col-auto d-none my-2 d-md-block">
                    {$avatar}
                </div>
                <div class="col-12 col-md clearfix">

                    <form class="comment_form" method="post" data-comment_post_ID="{$post_id}" data-comment_parent="0" >

                        <textarea placeholder="{$placeholder}" class="form-control my-2" name="comment_content" rows="3" {$textarea_attribute}></textarea>
                        <div class="row align-items-center {$class_button_row}">
                            <div class="col-auto order-0 me-auto me-sm-0">
                                <button class="open_emoji_popover btn btn-sm btn-light-2" data-bs-toggle="popover" title="表情代码" type="button" data-target_comment_parent="0">
                                    <i class="fa-solid fa-grin-squint"></i> 表情
                                </button>
                            </div>
                            <div class="col-12 col-sm-auto me-0 me-sm-auto mt-2 mt-sm-auto order-4 order-sm-1">
                                <div class="notify_author_check_container form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="notify_author" name="notify_author" value="1">
                                    <label class="form-check-label fs-75 fs-sm-875" for="notify_author">
                                        本评论要通知UP主
                                    </label>
                                </div>
                            </div>
                            <div class="col-auto reset_respond_container d-none order-2">
                                <button class="btn btn-sm btn-light-2 px-4 reset_respond" type="reset"  >
                                    取消回复
                                </button>
                            </div>
                            <div class="col-auto order-3">
                                <button class="btn btn-sm  btn-miku px-4" type="submit">
                                    <span class="button-text">发表评论</span>
                                </button>
                            </div>
                            
                        </div>

                    </form>
            
                </div>

            </div>

HTML;
    }
    else{
        $output = <<<HTML

            <div class="text-center my-4">
                当前文章已关闭评论
            </div>

HTML;
    }
    

    return $output;
}
