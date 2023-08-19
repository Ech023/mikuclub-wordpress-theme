<?php get_header();


?>


<div class="content ">

    <?php

    //如果未登录 访问成人分类 和成人文章 输出404内容
    if (!is_user_logged_in() && is_adult_category())
    {
        echo adult_404_content_for_no_logging_user();
    }
    else
    {

        while (have_posts())
        {

            //获取当前文章信息
            the_post();

            $post_id = get_the_ID();

            //增加文章点击数 (已改成通过JS增加)
            //add_post_views( $post_id );

            global $authordata;
            $author = get_custom_author($authordata->ID);


            $tag_list = get_the_tags();

            $is_user_followed = is_user_followed($author->id);

    ?>

            <?php
            //手机广告 标题上方
            if (dopt('Mobiled_adpost_00_b'))
            {
            ?>
                <div class="d-md-none">
                    <?php echo dopt('Mobiled_adpost_00'); ?>
                </div>

            <?php } ?>


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
                                if (dopt('d_singleMenu_b'))
                                {
                                    echo breadcrumbs_component();
                                } ?>
                            </div>

                            <div class="col-auto d-none d-sm-block">
                                <!-- 文章时间 -->
                                <div class="post-date ">
                                    发布时间 <?php echo get_the_date(MY_DATE_FORMAT); ?>
                                </div>
                            </div>
                            <div class="col-auto ">
                                <div class="post-modified-date ">
                                    更新时间 <?php echo get_the_modified_date(MY_DATE_FORMAT); ?>
                                </div>
                            </div>

                            <div class="w-100 m-0"></div>

                            <div class="col-auto ">
                                <!-- 文章点击量 -->
                                <div class="post-views">
                                    <i class="fas fa-eye"></i> <?php echo get_post_views($post_id); ?> 点击
                                </div>
                            </div>
                            <div class="col-auto ">
                                <!-- 文章评论数量 -->
                                <div class="post-comments">
                                    <i class="far fa-comments"></i>
                                    <?php echo get_comments_number() . ' 评论'; ?>
                                </div>
                            </div>
                            <div class="col-auto ">
                                <!-- 文章评分 -->
                                <div class="post-likes">
                                    <i class="far fa-star" aria-hidden="true"></i>
                                    <?php echo (float)get_post_likes($post_id); ?> 点赞
                                </div>
                            </div>
                            <div class="col-auto ">
                                <!-- 文章收藏 -->
                                <div class="post-favorite">
                                    <i class="far fa-heart" aria-hidden="true"></i>
                                    <?php echo (float)get_post_favorites($post_id); ?> 收藏
                                </div>
                            </div>
                            <div class="col-auto ">
                                <!-- 文章分享 -->
                                <div class="post-sharing me-3 my-1 my-md-0">
                                    <i class="far fa-share-square" aria-hidden="true"></i>
                                    <?php echo (float)get_post_shares($post_id); ?> 分享
                                </div>
                            </div>
                            <div class="col-auto ">
                                <!-- 编辑按钮 -->
                                <?php
                                //如果当前用户是作者或者管理员
                                if (get_current_user_id() === $author->id || current_user_is_admin())
                                {
                                ?>
                                    <div class="post-edit-link my-1 my-md-0">
                                        <a class="text-info" href="<?php echo get_edit_post_link($post_id); ?>" target="_blank">[编辑投稿]</a>
                                    </div>
                                <?php } ?>
                            </div>

                        </div>





                        <?php
                        //如果有标签
                        if ($tag_list)
                        {

                            echo '<div class="tags row my-3 g-2 align-items-center">';
                            echo '<div class="col-auto me-2"><i class="fas fa-tags"></i> 标签</div>';
                            foreach ($tag_list as $tag)
                            {
                                echo '<div class="col-auto"><a class="btn btn-outline-secondary" href="' . get_tag_link($tag->term_id) . '" >' . $tag->name . '</a></div>';
                            }
                            echo '</div>';
                        }
                        ?>



                    </div>

                    <div class=" col-12 col-lg-4">
                        <div class="post-author-data rounded p-3">
                            <div class="">
                                <a href="<?php echo $author->user_href; ?>" title="查看UP主页面">
                                    <?php echo print_user_avatar($author->user_image, 40); ?>
                                    <span class="mx-2">
                                        <?php echo $author->display_name; ?>
                                    </span>
                                    <span class="badge bg-miku">
                                        <?php echo get_user_level($author->id); ?>
                                    </span>
                                </a>
                            </div>
                            <div class="small text-truncate my-3">
                                <?php echo $author->user_description; ?>
                            </div>

                            <div class=" user-functions row gx-2">
                                <?php

                                $current_user_id = get_current_user_id();

                                //当前用户有登陆, 并且不是作者本人
                                if ($current_user_id > 0 && $current_user_id != $author->id)
                                {
                                ?>
                                    <div class="col-auto">
                                        <button class="btn btn-sm <?php echo $is_user_followed ? "btn-secondary unfollow" : "btn-miku follow"; ?> user-followed" data-user-id="<?php echo $author->id ?>">
                                            <span class="text follow" style="display : <?php echo $is_user_followed ? 'none' : 'inline'; ?>">
                                                <i class="fas fa-plus"></i> 关注
                                            </span>
                                            <span class="text unfollow" style="display : <?php echo !$is_user_followed ? 'none' : 'inline'; ?>">
                                                已关注
                                            </span>
                                            <span class="user-fans-count"><?php echo get_user_fans_count($author->id); ?></span>
                                        </button>
                                    </div>

                                    <div class="col-auto">
                                        <div class="create-private-message-modal">
                                            <button class="btn btn-sm btn-primary">
                                                <i class="fas fa-envelope"></i> 发私信
                                            </button>
                                            <input type="hidden" name="recipient_name" value="<?php echo $author->display_name; ?>" />
                                            <input type="hidden" name="recipient_id" value="<?php echo $author->id; ?>" />
                                        </div>
                                    </div>

                                    <div class="col-auto">
                                        <div class="dropdown">
                                            <a class="btn btn-secondary btn-sm" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" title="更多操作">
                                                <i class="fa-solid fa-ellipsis-vertical"></i>
                                            </a>
                                            <ul class="dropdown-menu">

                                                <?php
                                                //如果该作者已被用户加入黑名单
                                                if (in_user_black_list($current_user_id, $author->id))
                                                {
                                                    echo <<<HTML
                                                        <li><a class="dropdown-item delete-user-black-list" href="javascript:void(0);" data-target-user-id="{$author->id}">从黑名单里移除</a></li>
HTML;
                                                }
                                                //如果还未加入黑名单
                                                else
                                                {
                                                    echo <<<HTML
                                                        <li><a class="dropdown-item add-user-black-list" href="javascript:void(0);" data-target-user-id="{$author->id}">加入黑名单</a></li>
HTML;
                                                }

                                                ?>
                                            </ul>
                                        </div>
                                    </div>



                                <?php } ?>
                            </div>
                        </div>


                    </div>


                </div>


            </header>


            <?php
            //电脑端 文章页 - 页面标题下
            if (dopt('d_adpost_01_b'))
            {
                echo '<div class="pop-banner text-center my-4  d-md-block">' . dopt('d_adpost_01') . '</div>';
            }

            //手机端 文章页 - 页面标题下
            if (dopt('Mobiled_adpost_01_b'))
            {
                echo '<div class="pop-banner text-center my-3 d-md-none">' . dopt('Mobiled_adpost_01') . '</div>';
            }

            ?>


            <!-- 文章内容 -->
            <article class="article-content">


                <?php

                echo print_post_content($post_id);


                //广告：文章页 - 内容下方
                if (dopt('d_adpost_03_b'))
                {
                    echo '<div class="pop-banner text-center   my-4 d-none d-md-block">' . dopt('d_adpost_03') . '</div>';
                }

                //手机广告 - 内容下方
                if (dopt('Mobiled_adpost_03_b'))
                {
                    echo '<div class="pop-banner text-center my-3 d-md-none">' . dopt('Mobiled_adpost_03') . '</div>';
                }

                ?>


            </article>


        <?php } ?>

        <!-- 标签 -->
        <footer class="article-footer ">


            <!-- 相关文章 -->
            <?php echo related_posts_component(); ?>

        </footer>

        <?php

        //广告：文章页 - 评论区上方
        if (dopt('d_adpost_04_b'))
        {
            echo '<div class="pop-banner  text-center  my-4 d-none d-md-block">' . dopt('d_adpost_04') . '</div>';
        }

        //手机广告 - 评论区上方
        if (dopt('Mobiled_adpost_04_b'))
        {
            echo '<div class="pop-banner text-center my-3 d-md-none">' . dopt('Mobiled_adpost_04') . '</div>';
        }

        ?>


    <?php comments_template('', true);
    }
    ?>


</div>


<?php

//get_sidebar();


get_footer();

?>