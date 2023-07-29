<?php

get_header();


$author           = get_custom_author( get_queried_object_id() );
$is_user_followed = is_user_followed( $author->id );

?>


    <div class="content">

        <header class="author-header row my-5 gy-4" data-author-id="<?php echo $author->id; ?>">

            <div class="col-12 col-xl-8 d-flex justify-content-start row">
                <div class="col-12 col-sm-auto text-center">
					<?php echo print_user_avatar( $author->user_image, 100 ); ?>
                </div>
                <div class="col  mt-xl-0">
                    <h4 class="text-center text-md-start">
                        <span class="fw-bold d-block d-sm-inline mb-2 mb-sm-0 me-2 text-center"><?php echo $author->display_name ?></span>
                        <span class="badge bg-miku   p-2"><?php echo get_user_level( $author->id ) ?></span>
						<?php echo print_user_badges( $author->id ); ?>
                    </h4>
                    <div class="text-center text-md-start mt-3">
						<?php echo $author->user_description; ?>
                    </div>
                </div>

            </div>
            <div class="user-functions col-12 col-xl-4 my-3 ">
                <div class=" row  justify-content-center justify-content-xl-end">
                    <?php
                    //必须是登陆用户, 并且不能是作者自己
                    if ( get_current_user_id() > 0 && get_current_user_id() != $author->id ) {
                    
                        ?>
                        <div class="col-auto">
                            <button class="btn btn-lg <?php echo $is_user_followed ? "btn-secondary unfollow" : "btn-miku follow"; ?> user-followed"
                                    data-user-id="<?php echo $author->id ?>">
                                <span class="text follow" style="display: <?php echo $is_user_followed ? 'none' : 'inline'; ?>">
                                                <i class="fas fa-plus"></i> 关注
                                </span>
                                <span class="text unfollow" style="display: <?php echo !$is_user_followed ? 'none' : 'inline'; ?>">
                                    已关注
                                </span>
                                <span class="user-fans-count"><?php echo get_user_fans_count( $author->id ); ?></span>
                            </button>
                        </div>
                        <div class="col-auto">
                            <div class="create-private-message-modal">
                                <button class="btn btn-lg btn-primary">
                                    <i class="fas fa-envelope"></i> 发私信
                                </button>
                                <input type="hidden" name="recipient_name" value="<?php echo $author->display_name; ?>"/>
                                <input type="hidden" name="recipient_id" value="<?php echo $author->id; ?>"/>
                            </div>
                        </div>
                      

                       
                        <?php
                    }
                    ?>
                </div>
            </div>

            <div class="col-12">

                <div class="row  align-items-center g-0 justify-content-end">

                    <div class="col-12 col-xl-4">
                        <div class="input-group author-internal-search ">

                            <input type="text" class="form-control search-value " placeholder="搜索该UP主的投稿"
                                name="<?php echo AUTHOR_INTERNAL_SEARCH ?>" autocomplete="off"
                                value="<?php echo sanitize_text_field(get_query_var( AUTHOR_INTERNAL_SEARCH )); ?>"/>

                            <button class="btn btn-outline-miku"><i class="fas fa-search"></i></button>

                        </div>
                    </div>

                 

                </div>


            </div>


        </header>

       


		<?php echo post_list_component() ?>
    </div>


<?php get_footer(); ?>