<?php
/*
  template name: 投稿页面
  description: 新建投稿和编辑投稿页面
 */

use mikuclub\constant\Config;
use mikuclub\User_Capability;

use function mikuclub\breadcrumbs_component;

use function mikuclub\is_sticky_post;


$home = get_home_url();

//如果未登陆 重定向回首页
User_Capability::prevent_not_logged_user();

get_header();

?>

<div class="page-tougao">

    <header class="page-header">
        <h4 class="my-4">
            <?php echo breadcrumbs_component(); ?>
        </h4>

        <?php
        if (array_key_exists('pid', $_GET) && isset($_GET['pid']))
        {

            $post_id = $_GET['pid'];

            $author_id = intval(get_post_field('post_author', $post_id));
            $author_name = get_the_author_meta('display_name', $author_id);

            //获取文章状态
            $post_status      = get_post_status($_GET['pid']);
            $post_status_text = '';
            $text_color_class = '';
            $show_draft_button = false;

            switch ($post_status)
            {
                case "publish":
                    $post_status_text = '已发布';
                    $text_color_class = 'text-success';
                    $show_draft_button = true;
                    break;
                case "pending":
                    $post_status_text = '等待审核';
                    $text_color_class = 'text-danger';
                    $show_draft_button = true;
                    break;
                case "draft":
                    $post_status_text = '草稿';
                    $text_color_class = 'text-secondary';
                    break;
            }


        ?>

            <div class="row gy-2">

                <div class="col-12 col-sm-6">

                    <div class="row">

                        <div class="col-auto">
                            稿件状态: <span class="<?php echo $text_color_class; ?>"><?php echo $post_status_text; ?></span>
                        </div>
                        <div class="col-auto">
                            创建时间: <span class="text-primary"><?php echo get_the_date(Config::DATE_FORMAT, $_GET['pid']); ?></span>
                        </div>
                        <div class="col-auto">
                            更新时间: <span class="text-info"><?php echo get_the_modified_date(Config::DATE_FORMAT, $_GET['pid']); ?></span>
                        </div>
                        <?php

                        //高级用户可见
                        if (User_Capability::is_admin())
                        {
                        ?>
                            <div class="col-auto">
                                作者: <a href="<?php echo get_author_posts_url($author_id); ?>" target="_blank"><?php echo $author_name; ?></a>
                            </div>

                        <?php
                        }
                        ?>

                    </div>


                </div>

                <div class="col-12 col-sm-6">

                    <div class="row justify-content-sm-end gy-2 gx-2">

                        <div class="col-auto">
                            <!--  查看投稿链接 -->
                            <a class=" btn btn-secondary" href="<?php echo get_permalink($post_id) ?>">
                                查看投稿
                            </a>
                        </div>

                        <div class="col-auto">
                            <!--  投稿管理链接 -->
                            <a class="btn btn-secondary" href="<?php echo $home . '/up_home_page'; ?>" target="_blank">
                                稿件管理
                            </a>
                        </div>

                        <div class="col-auto">

                            <?php if ($show_draft_button)
                            { ?>
                                <!--  转为草稿链接 -->
                                <button class="btn btn-warning draft-post " data-post-id="<?php echo $post_id; ?>">
                                    转为草稿
                                </button>
                            <?php } ?>

                        </div>
                        <div class="col-auto">

                            <!--  删除投稿链接 -->
                            <button class="btn btn-danger delete-post " data-post-id="<?php echo $post_id; ?>">
                                删除投稿
                            </button>

                        </div>
                        <div class="col-auto">

                            <?php

                            //高级用户可见
                            if (User_Capability::is_admin())
                            {
                            ?>

                                <button class="btn btn-success update-post-date" data-post-id="<?php echo $post_id; ?>">
                                    更新创建时间
                                </button>

                            <?php
                            }
                            ?>

                        </div>

                        <?php
                        //管理员才可见
                        if (User_Capability::is_admin())
                        {

                            if (!is_sticky_post($post_id))
                            {
                                $sticky_post_button_class = 'set-sticky-post';
                                $sticky_post_button_text  = '置顶投稿';
                            }
                            //文章已被置顶
                            else
                            {
                                $sticky_post_button_class = 'delete-sticky-post';
                                $sticky_post_button_text  = '取消置顶';
                            }
                        ?>

                            <div class="col-auto">
                                <button class="btn btn-primary <?php echo $sticky_post_button_class; ?>" data-post-id="<?php echo $post_id; ?>">
                                    <?php echo $sticky_post_button_text; ?>
                                </button>
                            </div>

                            <div class="col-auto">
                                <button class="btn btn-warning  reject-post" data-post-id="<?php echo $post_id; ?>">
                                    驳回投稿
                                </button>
                            </div>

                        <?php
                        }

                        ?>


                    </div>

                </div>



            </div>

        <?php
        }
        ?>

    </header>


    <?php
    while (have_posts())
    {
        the_post();
    ?>

        <div class="page-content my-3">

            <?php the_content(); ?>

            <div class="fixed-submit-button-div position-fixed bottom-0 start-0 end-0 bg-white text-center p-4 border-top shadow" style="z-index: 99">
                <button class="btn btn-large btn-miku w-75">
                    <?php echo array_key_exists('pid', $_GET) && isset($_GET['pid']) ? "更新" : "提交审核"; ?>
                </button>
            </div>

        </div>


    <?php
    }
    ?>


</div>

<?php get_footer(); ?>