<?php get_header();

?>



    <div class="content">


		<?php
        

		//如果不存在分页变量
		if ( !get_query_var( 'paged' ) ) {
		    //加载首页组件
			echo home_main_page();
		} else {
		    //加载最新发布
			echo home_recently_page();
		}


		?>



    </div>


<?php

//get_sidebar(); 
get_footer();

?>