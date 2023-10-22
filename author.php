<?php

use mikuclub\Post_Query;

use function mikuclub\get_custom_user;
use function mikuclub\get_user_fans_count;
use function mikuclub\in_user_black_list;
use function mikuclub\is_user_followed;
use function mikuclub\post_list_component;
use function mikuclub\print_author_statistics;
use function mikuclub\print_user_avatar;
use function mikuclub\print_user_badges;

get_header();

$current_user_id = get_current_user_id();
$author           = get_custom_user(get_queried_object_id());
$is_user_followed = is_user_followed($author->id);


$author_buttons_element = '';
//必须是登陆用户, 并且不能是作者自己
if ($current_user_id > 0 && $current_user_id != $author->id)
{
    //关注按钮样式
    $add_follow_button_style = $is_user_followed ? 'display: none;' : '';
    $delete_follow_button_style = $is_user_followed ? '' : 'display: none;';
    //作者的关注数
    $user_fans_count = get_user_fans_count($author->id);

    $author_buttons_element = <<<HTML

     <div class="col-auto user-follow" data-user-fans-count="{$user_fans_count}">
         <button class="btn btn-miku add-user-follow-list"  style="{$add_follow_button_style}" data-target-user-id="{$author->id}">
             <i class="fa-solid fa-plus"></i>
             <span>关注</span>
             <span class="user-fans-count">{$user_fans_count}</span>
         </button>
         <button class="btn btn-secondary delete-user-follow-list"  style="{$delete_follow_button_style}" data-target-user-id="{$author->id}">
             <i class="fa-solid fa-minus"></i>
             <span>已关注</span>
             <span class="user-fans-count">{$user_fans_count}</span>
         </button>
     </div>
     <div class="col-auto">
         <button class="btn btn-primary show-private-message-modal" data-recipient_id="{$author->id}" data-recipient_name="{$author->display_name}">
            <i class="fa-solid fa-envelope"></i> 发私信
         </button>
     </div>
HTML;

    $toggle_black_list_button = '';
    //如果该作者已被用户加入黑名单
    if (in_user_black_list($current_user_id, $author->id))
    {
        $toggle_black_list_button = <<<HTML
         <li><a class="dropdown-item delete-user-black-list" href="javascript:void(0);" data-target-user-id="{$author->id}">从黑名单里移除</a></li>
HTML;
    }
    //如果还未加入黑名单
    else
    {
        $toggle_black_list_button =  <<<HTML
              <li><a class="dropdown-item add-user-black-list" href="javascript:void(0);" data-target-user-id="{$author->id}">加入黑名单</a></li>
HTML;
    }

    $author_buttons_element .= <<<HTML
         <div class="col-auto">
             <div class="dropdown">
                 <a class="btn btn-secondary" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" title="更多操作">
                     <i class="fa-solid fa-ellipsis-vertical"></i>
                 </a>
                 <ul class="dropdown-menu">
                     {$toggle_black_list_button}
                 </ul>
             </div>
         </div>

HTML;
}







?>


<div class="content">

    <header class="author-header row my-3 gy-3" data-author-id="<?php echo $author->id; ?>">

        <div class="col-12 col-lg">
            <div class="row justify-content-start">
                <div class="col-12 col-sm-auto ">
                    <div class="text-center">
                        <?php echo print_user_avatar($author->user_image, 100); ?>
                    </div>
                </div>
                <div class="col mt-xl-0">
                    <div class="fs-5 fw-bold text-center text-sm-start m-1">
                        <?php echo $author->display_name ?>
                    </div>

                    <div class="m-1 text-center text-sm-start">
                        <?php echo print_user_badges($author->id); ?>
                    </div>

                    <div class="my-2 overflow-hidden text-center text-sm-start" style="max-height: 96px;">
                        <?php echo $author->user_description; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-auto">
            <div class="user-functions row justify-content-center justify-content-lg-end gx-2 h-100 align-items-center">
                <?php echo $author_buttons_element; ?>
            </div>
        </div>
        
        <div class="m-0"></div>

        <div class="col-12 col-xxl-8">

            <div class="row row-cols-3 row-cols-md-6 text-center small g-2">
                <?php echo print_author_statistics($author->id); ?>
            </div>

        </div>

        <div class="col-12 col-xxl-4">

            <div class="input-group author-internal-search ">

                <input type="text" class="form-control search-value " placeholder="搜索该UP主的投稿" name="<?php echo Post_Query::CUSTOM_SEARCH ?>" autocomplete="off" value="<?php echo sanitize_text_field(get_query_var(Post_Query::CUSTOM_SEARCH)); ?>" />
                <button class="btn btn-miku"><i class="fa-solid fa-search"></i></button>

            </div>


        </div>


    </header>

    <hr/>


    <?php echo post_list_component() ?>
</div>


<?php get_footer(); ?>