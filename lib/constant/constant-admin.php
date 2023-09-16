<?php

namespace mikuclub\constant;


/**
 * 网站主题设置的 常量
 */
class Admin_Page{
     //页面标题
     const PAGE_TITLE = '初音社主题设置';
     //页面的路径名称
     const PAGE_PATH_NAME = 'miku_theme_config';

}

/**
 * 网站主题设置的元数据
 */
class Admin_Meta extends Constant
{
   
    //网站元描述
    const SITE_DESCRIPTION = 'd_description';

    //网站元关键词
    const SITE_KEYWORDS = 'd_keywords';

    //网站顶部公告
    const SITE_ANNOUNCEMENT_TOP = 'd_tui';

    //网站顶部公告 折叠内容
    const SITE_ANNOUNCEMENT_TOP_COLLAPSE = 'd_tui_qq_collapse';

    //网站底部公告
    const SITE_ANNOUNCEMENT_BOTTOM = 'd_tui_bottom';

    //APP首页公告
    const APP_ANNOUNCEMENT = 'd_tui_android';

    //网站顶部公共代码
    const SITE_TOP_CODE_ENABLE = 'd_headcode_b';
    const SITE_TOP_CODE = 'd_headcode';
    //网站底部公共代码
    const SITE_BOTTOM_CODE_ENABLE = 'd_footcode_b';
    const SITE_BOTTOM_CODE = 'd_footcode';
    //网站底部流量统计代码
    const SITE_BOTTOM_TRACK_CODE_ENABLE = 'd_track_b';
    const SITE_BOTTOM_TRACK_CODE = 'd_track';

    //全站顶部主菜单下方广告位
    const SITE_TOP_ADSENSE_PC_ENABLE = 'd_adsite_01_b';
    const SITE_TOP_ADSENSE_PC = 'd_adsite_01';
    const SITE_TOP_ADSENSE_PHONE_ENABLE = 'Mobiled_adsite_01_b';
    const SITE_TOP_ADSENSE_PHONE = 'Mobiled_adsite_01';

    //分类页/标签页/最新发布页 热门排行下方广告位 (PC+手机端)
    const CATEGORY_TOP_ADSENSE_ENABLE = 'd_adindex_02_b';
    const CATEGORY_TOP_ADSENSE = 'd_adindex_02';

    //首页顶部主菜单下方广告位
    const HOME_TOP_ADSENSE_PC_ENABLE = 'd_adindex_00_b';
    const HOME_TOP_ADSENSE_PC = 'd_adindex_00';
    const HOME_TOP_ADSENSE_PHONE_ENABLE = 'Mobiled_adindex_00_b';
    const HOME_TOP_ADSENSE_PHONE = 'Mobiled_adindex_00';

    //首页幻灯片下方广告位
    const HOME_SLIDE_BOTTOM_ADSENSE_PC_ENABLE = 'd_adindex_01_b';
    const HOME_SLIDE_BOTTOM_ADSENSE_PC = 'd_adindex_01';
    const HOME_SLIDE_BOTTOM_ADSENSE_PHONE_ENABLE = 'Mobiled_adindex_01_b';
    const HOME_SLIDE_BOTTOM_ADSENSE_PHONE = 'Mobiled_adindex_01';

    //首页最新发布上方广告位
    const HOME_RECENTLY_LIST_TOP_ADSENSE_PC_ENABLE = 'd_adindex_03_b';
    const HOME_RECENTLY_LIST_TOP_ADSENSE_PC = 'd_adindex_03';
    const HOME_RECENTLY_LIST_TOP_ADSENSE_PHONE_ENABLE = 'Mobiled_adindex_03_b';
    const HOME_RECENTLY_LIST_TOP_ADSENSE_PHONE = 'Mobiled_adindex_03';

    //文章页标题下方广告位
    const POST_TITLE_BOTTOM_ADSENSE_PC_ENABLE = 'd_adpost_01_b';
    const POST_TITLE_BOTTOM_ADSENSE_PC = 'd_adpost_01';
    const POST_TITLE_BOTTOM_ADSENSE_PHONE_ENABLE = 'Mobiled_adpost_01_b';
    const POST_TITLE_BOTTOM_ADSENSE_PHONE = 'Mobiled_adpost_01';

    //文章页正文中间广告位
    const POST_CONTENT_ADSENSE_PC_ENABLE = 'd_adpost_02_b';
    const POST_CONTENT_ADSENSE_PC = 'd_adpost_02';
    const POST_CONTENT_ADSENSE_PHONE_ENABLE = 'Mobiled_adpost_02_b';
    const POST_CONTENT_ADSENSE_PHONE = 'Mobiled_adpost_02';

    //文章页评论区广告位
    const POST_COMMENT_ADSENSE_PC_ENABLE = 'd_adpost_03_b';
    const POST_COMMENT_ADSENSE_PC = 'd_adpost_03';
    const POST_COMMENT_ADSENSE_PHONE_ENABLE = 'Mobiled_adpost_03_b';
    const POST_COMMENT_ADSENSE_PHONE = 'Mobiled_adpost_03';

    


    //安卓APP首页幻灯片下方广告位
    const APP_ADSENSE_ENABLE = 'app_adindex_01_b';
    const APP_ADSENSE_TEXT = 'app_adindex_01_text';
    const APP_ADSENSE_LINK = 'app_adindex_01_link';

    /**
     * 获取admin元数据
     *
     * @param string $option_name 键名
     * @return string|bool 键值, 如果未找到则返回false
     */
    public static function get_option($option_name)
    {

        $result = get_option($option_name);
        //如果键值 是 字符串 进行额外反引用处理
        if (is_string($result))
        {
            $result = stripslashes($result);
        }

        return $result;
    }
}
