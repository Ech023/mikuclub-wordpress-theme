<?php

use function mikuclub\post_list_component;

get_header();

?>


<div>
	<h1><?php
		if (is_day())
		{
			the_time('Y年m月j日');
		}
		elseif (is_month())
		{
			the_time('Y年m月');
		}
		elseif (is_year())
		{
			the_time('Y年');
		}
		?>的内容</h1>
</div>
<?php echo post_list_component() ?>



<?php

//get_sidebar();
get_footer();

?>