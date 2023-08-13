<?php
/*
	template name: 签到页面
*/
get_header();

global $post;
?>

    <div class="content page-qiandao">

        <header class="page-header">
            <h4 class="my-4">
				<?php echo breadcrumbs_component(); ?>
            </h4>


            <div class="text-end">
				<?php echo print_page_edit_link(); ?>
            </div>
        </header>

		<?php
		while ( have_posts() ) {
			the_post();

			$max         = 1136;
			$rand_image = rand( 1, $max );
            //在左方添加0
            $rand_image = str_pad($rand_image, 3, '0', STR_PAD_LEFT);

            $link = 'https://'.CDN_MIKUCLUB_FUN.'/project_sekai_cg/'.$rand_image.'.jpg';

			?>
            <div class="page-content page-qiandao my-2">

                <div class="qiandao-img text-center my-4 row">
				
                        <div class="col-12">
                            <a href="<?php echo $link; ?>"
                               data-lightbox="qiandao-images">
                                <img class="img-fluid"
                                     src="<?php echo $link; ?>"
                                     alt="签到壁纸">
                            </a>
                        </div>

                </div>
                <hr class="my-4"/>

                <div class="qiandao-button-container my-4 text-center">

                </div>

                <div class="my-4">

					<?php the_content(); ?>

                </div>

            </div>

            <hr/>


			<?php
			comments_template( '', true );
		}
		?>


    </div>

<?php get_footer(); ?>