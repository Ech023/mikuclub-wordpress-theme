<?php

use mikuclub\constant\Admin_Meta;

use function mikuclub\print_adult_404_content_for_no_logging_user;
use function mikuclub\breadcrumbs_component;

use function mikuclub\get_hot_list_by_random;
use function mikuclub\get_sub_category_list;
use function mikuclub\get_theme_option;
use function mikuclub\has_sub_category;
use function mikuclub\hot_posts_most_comments;
use function mikuclub\hot_posts_most_rating;
use function mikuclub\is_adult_category;
use function mikuclub\post_list_component;
use function mikuclub\sticky_posts_component;
use function mikuclub\top_hot_posts_component;

 get_header(); 
 
 $cat_id = get_queried_object_id();
 
 
 ?>


<div class="content my-4">

	<?php

	//如果未登录 访问成人分类 和成人文章 输出404内容
	if (!is_user_logged_in() && is_adult_category())
	{
		echo print_adult_404_content_for_no_logging_user();
	}
	else
	{
	?>

		<header class="archive-header">
			<h4>
				<?php echo breadcrumbs_component(); ?>
			</h4>
		</header>

		<?php
		//只在第一页显示
		if (!get_query_var('paged'))
		{

			//如果是有子分类的主分类
			//输出幻灯片+3种热门列表
			if (has_sub_category($cat_id))
			{
		?>

				<div class="row my-4">
					<div class="col-12 col-lg-6 col-xl-5">
						<?php echo sticky_posts_component($cat_id); ?>
					</div>
					<div class="col-12 col-lg-6 col-xl-7 my-3 my-lg-0">
						<?php echo top_hot_posts_component($cat_id); ?>
					</div>
				</div>




				<?php

				if (get_theme_option(Admin_Meta::CATEGORY_TOP_ADSENSE_ENABLE))
				{
					echo '<div class="pop-banner  text-center my-4">' . get_theme_option(Admin_Meta::CATEGORY_TOP_ADSENSE) . '</div>';
				}

				$sub_categories = get_sub_category_list($cat_id);
				if ($sub_categories)
				{
					echo '
                    <div>
                        <div class="row my-4">
                            <h4 class="col">
                                子分区
                            </h4>
                        </div>
                        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 gy-4">';
					foreach ($sub_categories as $sub_category)
					{
						echo '
                                <div class="col">
                                    <a class="btn btn-lg btn-outline-secondary w-100" href="' . get_category_link($sub_category->term_id) . '">' . $sub_category->name . '</a>
                                </div>
                            ';
					}
					echo '
                        </div>
                    </div>';
				}


				?>


				<?php echo hot_posts_most_rating($cat_id, 8); ?>

				<?php echo hot_posts_most_comments($cat_id, 8); ?>


		<?php } //如果是没有子分类的分类
			else
			{
				//随机输出一种热门文章
				echo get_hot_list_by_random($cat_id, 8);
				if (get_theme_option(Admin_Meta::CATEGORY_TOP_ADSENSE_ENABLE))
				{
					echo '<div class="pop-banner  text-center my-4">' . get_theme_option(Admin_Meta::CATEGORY_TOP_ADSENSE) . '</div>';
				}

			}
		}

		else
		{


			if (get_theme_option(Admin_Meta::CATEGORY_TOP_ADSENSE_ENABLE))
			{
				echo '<div class="pop-banner  text-center my-4">' . get_theme_option(Admin_Meta::CATEGORY_TOP_ADSENSE) . '</div>';
			}
		}

		?>





		<?php echo post_list_component(); ?>

	<?php
	}
	?>


</div>


<?php

//get_sidebar(); 
get_footer();

?>