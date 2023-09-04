<?php



//当前用户ID
$user_id = get_current_user_id();
$pub_comment_element = '';

if (is_single())
{
    $pub_link = 'https://shop119340084.taobao.com/shop/view_shop.htm?spm=a230r.1.14.4.75dc14ecGDLY4r&user_number_id=1965847533&mm_sycmid=1_139544_204eb550d8889ed3be7ea07664a8ccf6';
    $cdn_domain = Web_Domain::CDN_MIKUCLUB_FUN;
    $pub_comment_element = <<<HTML

    <!-- A酱的绅士玩具屋 评论区广告 -->
    <div class="my-2">

        <div class="row comment-body border-bottom  ">

            <div class="col-auto col-md-1 my-2 avatar-container">
                <a class="" href="{$pub_link}" title="查看用户主空间" target="_blank" rel="nofollow">
                    <img class="avatar rounded-circle" src="https://{$cdn_domain}/pub/A酱的绅士玩具屋/头像.jpg" width="50" height="50" alt="用户头像">
                </a>
            </div>
            <div class="col col-md-10 my-2">
                <div class="user-meta">
                    <a class="m-1 d-block d-sm-inline" href="{$pub_link}" title="查看用户主空间" target="_blank" rel="nofollow">A酱的绅士玩具屋</a>
                    <span class="badge bg-warning m-1">飞机杯</span>
                </div>
                <div class="comment-content my-3" style="">
                    <p class="mb-0">天天在家手冲会不会阳痿? 如何锻炼自己的牛子持久不射? <img src="https://www.mikuclub.cc/wp-content/themes/miku/img/smilies/icon_question.gif" alt=":?:" class="wp-smiley" style="height: 1em; max-height: 1em;"></p>
                    <p class="mb-0">我也想像哥布林一样一天一个女骑士。<img src="https://www.mikuclub.cc/wp-content/themes/miku/img/smilies/icon_wink.gif" alt=":?:" class="wp-smiley" style="height: 1em; max-height: 1em;"></p>
                    <p class="mb-0">那就快去“<a class="text-info" href="{$pub_link}" target="_blank" rel="nofollow">A酱的绅士玩具屋</a>”吧, 初音社为大家申请到了限时粉丝专属价, 只有和客服A酱说是初音社来的就可以享受到优惠哦!~ <img src="https://www.mikuclub.cc/wp-content/themes/miku/img/smilies/icon_neutral.gif" alt=":|" class="wp-smiley" style="height: 1em; max-height: 1em;"></p>
                    <p class="my-2"> <a class="text-info text-decoration-underline" href="{$pub_link}" target="_blank" rel="nofollow">戳这里即可拥有>> 一个榨汁飞(lao)机(po)杯,快来我和签订契约成为绅(hen)士(tai)吧!</a> </p>

                </div>
                <div class="comment-meta small">
                    <span class="m-1 text-muted d-block d-sm-inline mb-3 mb-sm-1">2022-09-03 00:00:00</span>
                    <span class="d-none d-md-inline m-1 text-muted"><i class="fa-solid fa-store"></i> 淘宝店</span>

                    <div class="comment-likes d-inline ms-0 ms-sm-3 me-3">
                        <span class="text-muted">
                            <i class="fa-solid fa-thumbs-up"></i> 点赞
                        </span>
                        <span class="mx-3 comment-likes-count">
                            666
                        </span>
                        <span class="text-muted">
                            <i class="fa-solid fa-thumbs-down"></i> 踩
                        </span>
                    </div>


                </div>

            </div>

        </div>



    </div>


HTML;
}






?>

<div class="comments-part my-4" id="comments-part">

    <h4 class="my-2 comments-part-title"><?php echo get_post_comments() ?> 评论</h4>

    <div class="my-2 row comment-form-container">

        <?php
        //用户有登陆 并且有开启评论
        if ($user_id && comments_open())
        { ?>

            <div class="d-none d-md-block col-1 my-2">
                <?php
                echo print_user_avatar(get_my_user_avatar($user_id), 40);
                ?>
            </div>

            <div class="col-12 col-md-11">

                <form class="main-comment" method="post">

                    <textarea placeholder="评价一下吧" class="form-control my-2" name="comment_content" rows="3"></textarea>
                    <div>
                        <button class="btn btn-secondary emoji my-1" data-bs-toggle="popover" title="表情">
                            <i class="fa-solid fa-grin-squint"></i> 表情
                        </button>

                        <div class="form-check form-check-inline m-1 ms-3 ">
                            <input class="form-check-input" type="checkbox" id="notify_author" name="notify_author" value="1">
                            <label class="form-check-label" for="notify_author">本评论要通知UP主</label>
                        </div>

                        <button class="btn btn-miku my-1 float-end  px-3 px-md-5" type="submit">
                            <span class="button-text">发表评论</span>
                            <span class="spinner-border spinner-border-sm" style="display: none"></span>
                        </button>
                        <button class="btn btn-secondary my-1 float-end me-2 reset-respond" style="display: none">
                            取消回复
                        </button>
                        <?php
                        // 输出默认用回复评论的 隐藏表单数值
                        comment_id_fields();
                        do_action('comment_form', $post->ID);
                        ?>
                    </div>

                </form>

                <hr class="clear-both" />

            </div>


        <?php
        }

        //如果未开启评论
        else if (!comments_open())
        { ?>

            <div class="text-center my-4 col-12">
                当前文章已关闭评论
            </div>

        <?php }
        //未登录
        else
        { ?>

            <div class="text-center my-4 col-12">
                请先 <a href="<?php echo wp_login_url(get_permalink()); ?>">登录</a> 才能发表评论
            </div>
        <?php }
        ?>


    </div>


    <?php
    //if ( have_comments() ) {
    ?>
    <div>

        <?php echo $pub_comment_element; ?>

        <div class="comment-list">
        </div>
        
        <div class="get-next-page text-center" data-post-id="<?php echo get_the_ID() ?>" data-offset="0">
            <div class="spinner-border text-miku" style="display: none"></div>
        </div>
    </div>
    <?php
    //}
    ?>

</div>