<?php

namespace mikuclub;

class Post_Query
{
    //文章显示数量
    const POSTS_PER_PAGE = 'posts_per_page';

    //排除置顶文章
    const IGNORE_STICKY_POSTS = 'ignore_sticky_posts';

    //搜索内容
    const SEARCH = 's';

    //分类ID
    const CAT = 'cat';
    //标签ID
    const TAG_ID = 'tag_id';
    const TAG__IN = 'tag__in';


    //时间过滤器
    const DATE_QUERY = 'date_query';

    //文章类型
    const POST_TYPE = 'post_type';
    //文章状态
    const POST_STATUS = 'post_status';

    //作者ID
    const AUTHOR = 'author';

    //排序
    const ORDERBY = 'orderby';
    //排序顺序
    const ORDER = 'order';

    //排序用的元数据
    const META_KEY = 'meta_key';

    //文章ID数组
    const POST__IN = 'post__in';
    const POST__NOT_IN = 'post__not_in';

    const PAGED = 'paged';

    //自定义分类ID
    const CUSTOM_MAIN_CAT = 'main_cat';
    //自定义子分类ID
    const CUSTOM_SUB_CAT = 'sub_cat';
    //自定义排序
    const CUSTOM_ORDERBY = 'custom_orderby';
    //自定义日期范围
    const CUSTOM_ORDER_DATA_RANGE = 'custom_order_data_range';
    //自定义搜索
    const CUSTOM_SEARCH = 'custom_search';
    //自定义页面类型
    const CUSTOM_PAGE_TYPE = 'page_type';

    //自定义禁用缓存标识
    const CUSTOM_NO_CACHE = 'no_cache';

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
        $query_vars[] = Post_Query::CUSTOM_SEARCH;
        $query_vars[] = Post_Query::CUSTOM_MAIN_CAT;
        $query_vars[] = Post_Query::CUSTOM_SUB_CAT;

        return $query_vars;
    }
}
