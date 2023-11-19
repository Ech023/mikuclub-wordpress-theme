<?php

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Config;
use mikuclub\constant\Post_Feedback_Rank;
use mikuclub\User_Capability;

use function mikuclub\print_adult_404_content_for_no_logging_user;
use function mikuclub\breadcrumbs_component;
use function mikuclub\get_custom_user;
use function mikuclub\get_post_favorites;
use function mikuclub\get_post_like;
use function mikuclub\get_post_shares;
use function mikuclub\get_post_unlike;
use function mikuclub\get_post_views;
use function mikuclub\get_theme_option;
use function mikuclub\get_user_fans_count;
use function mikuclub\in_user_black_list;
use function mikuclub\is_adult_category;
use function mikuclub\is_user_followed;
use function mikuclub\print_post_content;
use function mikuclub\print_user_avatar;
use function mikuclub\print_user_badges;
use function mikuclub\related_posts_component;

get_header();

$current_user_id = get_current_user_id();

//检测是否允许访问 (用户已登陆 或者 不是 成人区分类)
$access_allowed = $current_user_id > 0 || is_adult_category() === false;





//检测是否允许访问
if ($access_allowed)
{

    //确定当前 WordPress 查询是否有可循环的文章。
    while (have_posts())
    {

        //获取当前文章信息
        the_post();

        $post_id = get_the_ID();

        $tag_list = get_the_tags();

        //增加文章点击数 (已改成通过JS增加)
        //add_post_views( $post_id );

        $author_id = intval(get_post_field('post_author', $post_id));
        $author = get_custom_user($author_id);
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
            
                <div class="col user-follow" data-user-fans-count="{$user_fans_count}">
                     <button class="btn btn-miku btn-sm w-100 add-user-follow-list"  style="{$add_follow_button_style}" data-target-user-id="{$author->id}">
                         <i class="fa-solid fa-plus"></i>
                         <span>关注</span>
                         <span class="user-fans-count">{$user_fans_count}</span>
                     </button>
                     <button class="btn btn-secondary btn-sm w-100 delete-user-follow-list"  style="{$delete_follow_button_style}" data-target-user-id="{$author->id}">
                         <i class="fa-solid fa-minus"></i>
                         <span>已关注</span>
                         <span class="user-fans-count">{$user_fans_count}</span>
                     </button>
                 </div>


                 <div class="col">
                    <button class="btn btn-primary btn-sm w-100 show-private-message-modal" data-recipient_id="{$author->id}" data-recipient_name="{$author->display_name}">
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
                             <a class="btn btn-secondary btn-sm " href="javascript:void(0);" role="button" data-bs-toggle="dropdown" title="更多操作">
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


        <header class="article-header">

            <div class="row gy-3">

                <div class="col-12 col-lg-8">


                    <!-- 文章标题 -->
                    <h4 class="article-title my-3">
                        <?php the_title(); ?>
                    </h4>

                    <!-- 文章信息 -->
                    <div class="meta row gy-3">

                        <!-- 网站面包屑-->
                        <div class="col-12 col-xl-auto">
                            <?php
                            echo breadcrumbs_component();

                            $post_like = get_post_like($post_id);
                            $post_unlike = get_post_unlike($post_id);

                            ?>
                        </div>

                        <div class="col-auto d-none d-sm-block">
                            <!-- 文章时间 -->
                            <div class="post-date ">
                                发布时间 <?php echo get_the_date(Config::DATE_FORMAT); ?>
                            </div>
                        </div>
                        <div class="col-auto ">
                            <div class="post-modified-date ">
                                更新时间 <?php echo get_the_modified_date(Config::DATE_FORMAT); ?>
                            </div>
                        </div>

                        <div class="w-100 m-0"></div>

                        <div class="col-auto ">
                            <!-- 评价 -->
                            <div class="post-feedback ">
                                <i class="fa-solid fa-square-poll-vertical"></i>
                                <span>
                                    <?php echo Post_Feedback_Rank::get_rank($post_like, $post_unlike) ?>
                                </span>

                            </div>
                        </div>


                        <div class="col-auto ">
                            <!-- 文章点赞 -->
                            <div class="post-likes">
                                <i class="fa-solid fa-thumbs-up" aria-hidden="true"></i>
                                <?php echo (float)$post_like; ?> 好评
                            </div>
                        </div>
                        <div class="col-auto ">
                            <!-- 文章差评 -->
                            <div class="post-likes">
                                <i class="fa-solid fa-thumbs-down"></i>
                                <?php echo (float)$post_unlike; ?> 差评
                            </div>
                        </div>
                        <div class="col-auto ">
                            <!-- 文章点击量 -->
                            <div class="post-views">
                                <i class="fa-solid fa-eye"></i> <?php echo get_post_views($post_id); ?> 点击
                            </div>
                        </div>

                        <div class="col-auto ">
                            <!-- 文章评论数量 -->
                            <div class="post-comments">
                                <i class="fa-solid fa-comments"></i>
                                <?php echo get_comments_number() . ' 评论'; ?>
                            </div>
                        </div>

                        <div class="col-auto ">
                            <!-- 文章收藏 -->
                            <div class="post-favorite">
                                <i class="fa-solid fa-heart" aria-hidden="true"></i>
                                <?php echo (float)get_post_favorites($post_id); ?> 收藏
                            </div>
                        </div>
                        <div class="col-auto ">
                            <!-- 文章分享 -->
                            <div class="post-sharing me-3 my-1 my-md-0">
                                <i class="fa-solid fa-share-square" aria-hidden="true"></i>
                                <?php echo (float)get_post_shares($post_id); ?> 分享
                            </div>
                        </div>
                        <div class="col-auto ">
                            <!-- 编辑按钮 -->
                            <?php
                            //如果当前用户是作者或者管理员
                            if ($current_user_id === $author->id || User_Capability::is_admin())
                            {
                                echo '<div class="post-edit-link my-1 my-md-0">' .
                                    '<a class="text-info" href="' . get_edit_post_link($post_id) . '" target="_blank">[编辑投稿]</a>' .
                                    '</div>';
                            }
                            ?>
                        </div>

                    </div>


                    <?php
                    //如果有标签
                    if ($tag_list)
                    {

                        echo '<div class="tags row my-3 g-2 align-items-center">';
                        echo '<div class="col-auto me-2"><i class="fa-solid fa-tags"></i> 标签</div>';
                        foreach ($tag_list as $tag)
                        {
                            echo '<div class="col-auto"><a class="btn btn-outline-secondary" href="' . get_tag_link($tag->term_id) . '" >' . $tag->name . '</a></div>';
                        }
                        echo '</div>';
                    }
                    ?>


                </div>

                <div class="col-12 col-lg-4">
                    <div class="post-author-data rounded p-2">
                        <div>
                            <div class="d-inline-block m-1">
                                <a href="<?php echo $author->user_href; ?>" title="查看UP主页面" target="_blank">
                                    <?php echo print_user_avatar($author->user_image, 40); ?>
                                </a>
                            </div>
                            <div class="d-inline-block m-1">
                                <a href="<?php echo $author->user_href; ?>" title="查看UP主页面" target="_blank">
                                    <?php echo $author->display_name; ?>
                                </a>
                            </div>
                            <div class="d-inline-block m-1">
                                <?php echo print_user_badges($author->id); ?>
                            </div>


                        </div>
                        <div class="small my-2 overflow-hidden" style="max-height: 42px;">
                            <?php echo $author->user_description; ?>
                        </div>

                        <div class="user-functions row gx-3">
                            <?php echo $author_buttons_element; ?>
                        </div>
                    </div>


                </div>


            </div>


        </header>


        <?php
        //电脑端 文章页 - 页面标题下
        if (get_theme_option(Admin_Meta::POST_TITLE_BOTTOM_ADSENSE_PC_ENABLE))
        {
            echo '<div class="pop-banner text-center my-4  d-md-block">' . get_theme_option(Admin_Meta::POST_TITLE_BOTTOM_ADSENSE_PC) . '</div>';
        }

        //手机端 文章页 - 页面标题下
        if (get_theme_option(Admin_Meta::POST_TITLE_BOTTOM_ADSENSE_PHONE_ENABLE))
        {
            echo '<div class="pop-banner text-center my-3 d-md-none">' . get_theme_option(Admin_Meta::POST_TITLE_BOTTOM_ADSENSE_PHONE) . '</div>';
        }

        ?>


        <!-- 文章内容 -->
        <article class="article-content">

            <?php echo print_post_content($post_id); ?>

        </article>


    <?php } ?>

    <!-- 标签 -->
    <footer class="article-footer ">


        <!-- 相关文章 -->
        <?php echo related_posts_component(); ?>

    </footer>

<?php

    //广告：文章页 - 评论区上方
    if (get_theme_option(Admin_Meta::POST_COMMENT_ADSENSE_PC_ENABLE))
    {
        echo '<div class="pop-banner  text-center  my-4 d-none d-md-block">' . get_theme_option(Admin_Meta::POST_COMMENT_ADSENSE_PC) . '</div>';
    }

    //手机广告 - 评论区上方
    if (get_theme_option(Admin_Meta::POST_COMMENT_ADSENSE_PHONE_ENABLE))
    {
        echo '<div class="pop-banner text-center my-3 d-md-none">' . get_theme_option(Admin_Meta::POST_COMMENT_ADSENSE_PHONE) . '</div>';
    }

    comments_template('', true);
}
else
{
    echo print_adult_404_content_for_no_logging_user();
}


get_footer();
