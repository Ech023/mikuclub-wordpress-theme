<?php

/**
 * 评论区的广告单元
 */

namespace mikuclub;

use mikuclub\constant\Web_Domain;

/**
 * 文章页评论区第一广告
 * @return string
 */
function print_single_comment_adsense()
{

    $output = '';

    if (is_single())
    {
        $pub_link = 'https://shop119340084.taobao.com/shop/view_shop.htm?spm=a230r.1.14.4.75dc14ecGDLY4r&user_number_id=1965847533&mm_sycmid=1_144962_8d3c3900107ba43419eb50be47edd98d';
        $cdn_domain = Web_Domain::CDN_MIKUCLUB_FUN;
        $output = <<<HTML
    
        <!-- A酱的绅士玩具屋 评论区广告 -->
        <div class="mt-4">
    
            <div class="row comment-body border-bottom pb-2 border-bottom ">
    
                <div class="col-12 col-sm-auto avatar-container text-center text-sm-start">
                    <a class="" href="{$pub_link}" title="查看用户主空间" target="_blank" rel="nofollow">
                        <img class="avatar rounded-circle" src="https://{$cdn_domain}/img/A酱的绅士玩具屋/头像.jpg" style="width:40px;height:40px" alt="用户头像">
                    </a>
                </div>
                <div class="col">
                    <div class="row align-items-center g-2">
                        <div class="col-12">
                            <div class="user-meta text-center text-sm-start">
                                <a class="m-1 d-block d-sm-inline small" href="{$pub_link}" title="查看用户主空间" target="_blank" rel="nofollow">A酱的绅士玩具屋</a>
                                <span class="badge bg-warning rounded-1 m-1">飞机杯</span>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="comment-content my-2 small">
                                <p class="mb-0">天天在家手冲会不会阳痿? 如何锻炼自己的牛子持久不射? <img src="https://www.mikuclub.cc/wp-content/themes/miku/img/smilies/icon_question.gif" alt=":?:" class="wp-smiley" style="height: 1em; max-height: 1em;"></p>
                                <p class="mb-0">我也想像哥布林一样一天一个女骑士。<img src="https://www.mikuclub.cc/wp-content/themes/miku/img/smilies/icon_wink.gif" alt=":?:" class="wp-smiley" style="height: 1em; max-height: 1em;"></p>
                                <p class="mb-0">那就快去“<a class="text-info" href="{$pub_link}" target="_blank" rel="nofollow">A酱的绅士玩具屋</a>”吧, 初音社为大家申请到了限时粉丝专属价, 只有和客服A酱说是初音社来的就可以享受到优惠哦!~ <img src="https://www.mikuclub.cc/wp-content/themes/miku/img/smilies/icon_neutral.gif" alt=":|" class="wp-smiley" style="height: 1em; max-height: 1em;"></p>
                                <p class="my-2"> <a class="text-info text-decoration-underline" href="{$pub_link}" target="_blank" rel="nofollow">戳这里即可拥有>> 一个榨汁飞(lao)机(po)杯,快来我和签订契约成为绅(hen)士(tai)吧!</a> </p>
                            </div>
                        </div>
                        <div class="col-auto fs-75 fs-sm-875">
                            <span class="">2023-12-12 12:12:12</span>
                        </div>
                        <div class="col-auto fs-75 fs-875">
                            <i class="fa-solid fa-store"></i> 淘宝店
                        </div>
                        <div class="my-2 w-100 d-lg-none"></div>
                      
                    </div>
                </div>
    
            </div>
    
        </div>
    
    
HTML;
    }


    return $output;
}
