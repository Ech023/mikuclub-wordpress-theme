<?php

namespace mikuclub;

use mikuclub\constant\Admin_Meta;
use mikuclub\constant\Category;
use mikuclub\constant\Config;
use mikuclub\constant\Constant;
use mikuclub\constant\Expired;

get_header();



//如果不存在分页变量
if (!get_query_var(Post_Query::PAGED))
{
	//加载首页组件
	echo print_home_component();
}
else
{
	//加载热门页面
	echo print_home_hot_post_page_component();
}


//get_sidebar(); 
get_footer();



