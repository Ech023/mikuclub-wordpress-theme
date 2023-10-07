<?php
namespace mikuclub;

class Post_Query
{
    //分类ID
    const CAT = 'cat';

    const CUSTOM_MAIN_CAT = 'main_cat';
    const CUSTOM_SUB_CAT = 'sub_cat';
    const CUSTOM_ORDERBY = 'custom_orderby';
    const CUSTOM_ORDER_DATA_RANGE = 'custom_order_data_range';
    const AUTHOR_INTERNAL_SEARCH = 'author_internal_search';

    /**
     * 添加 自定义query变量支持 到系统Wp_query里
     *
     * @param string[] $query_vars
     * @return string[]
     */
    public static function add_custom_query_vars($query_vars)
    {
        $query_vars[] = Post_Query::CUSTOM_ORDERBY;
        $query_vars[] = Post_Query::CUSTOM_ORDER_DATA_RANGE;
        $query_vars[] = Post_Query::AUTHOR_INTERNAL_SEARCH;
        $query_vars[] = Post_Query::CUSTOM_MAIN_CAT;
        $query_vars[] = Post_Query::CUSTOM_SUB_CAT;

        return $query_vars;
    }

}
