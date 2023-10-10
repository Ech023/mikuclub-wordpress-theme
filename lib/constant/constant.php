<?php

namespace mikuclub\constant;

use ReflectionClass;

class Constant
{
    /**
     * 获取类的所有常量值
     *
     * @return array<int, mixed>
     */
    public static function get_to_array()
    {
        //获取类信息
        $object = new ReflectionClass(get_called_class());
        //转换成数组
        return array_values($object->getConstants());
    }
}

/**
 * 网站全局设置
 */
class Config
{
    //CSS和JS版本号
    const CSS_JS_VERSION = '1.00';

    //默认时间格式
    const DATE_FORMAT_SHORT = 'y-m-d';
    const DATE_FORMAT = 'y-m-d H:i:s';
    const DATE_FORMAT_MYSQL = 'Y-m-d H:i:s';

    //启动文件缓存系统
    const ENABLE_FILE_CACHE_SYSTEM
    // = true;
    = false;
    //关闭文件缓存
    //const ENABLE_FILE_CACHE_SYSTEM = false;


    //幻灯片里显示的文章数量
    const STICKY_POST_LIST_LENGTH = 6;
    //内容页底部相关推荐里的文章数量
    const RELATED_POST_LIST_LENGTH = 8;

    //最新发布的论坛帖子列表长度
    const RECENT_FORUM_TOPIC_LIST_LENGTH = 8;

    //单页的评论数量
    const NUMBER_COMMENT_PER_PAGE = 30;
    //单页的高赞评论数量
    const NUMBER_TOP_LIKE_COMMENT_PER_PAGE = 3;

    //单页的回复评论数量
    const NUMBER_COMMENT_REPLY_PER_PAGE = 20;

    //单页的私信人数量
    const NUMBER_PRIVATE_MESSAGE_LIST_PER_PAGE = 20;
    //单页和单个收件人之间的私信数量
    const NUMBER_PRIVATE_MESSAGE_LIST_WITH_ONE_SENDER_PER_PAGE = 50;
}

/**
 * 网站相关的域名
 */
class Web_Domain
{
    //网站相关域名
    const MIKUCLUB_CC = 'www.mikuclub.cc';
    const MIKUCLUB_ONLINE = 'www.mikuclub.online';
    const MIKUCLUB_WIN = 'www.mikuclub.win';
    const MIKUCLUB_EU = 'www.mikuclub.eu';
    const MIKUCLUB_UK = 'www.mikuclub.uk';

    //CDN相关域名
    const CDN_MIKUCLUB_FUN = 'cdn.mikuclub.fun';
    const FILE1_MIKUCLUB_FUN = 'file1.mikuclub.fun';
    const FILE2_MIKUCLUB_FUN = 'file2.mikuclub.fun';
    const FILE3_MIKUCLUB_FUN = 'file3.mikuclub.fun';
    const FILE4_MIKUCLUB_FUN = 'file4.mikuclub.fun';
    const FILE5_MIKUCLUB_FUN = 'file5.mikuclub.fun';
    const FILE6_MIKUCLUB_FUN = 'file6.mikuclub.fun';

    /**
     * 获取当前主域名
     * @return string
     */
    public static function get_main_site_domain()
    {
        return static::MIKUCLUB_CC;
    }

    /**
     * 获取支持直接网站的域名数组
     * @return string[]
     */
    public static function get_array_site_domain()
    {
        return [
            static::MIKUCLUB_CC,
            static::MIKUCLUB_ONLINE,
            static::MIKUCLUB_WIN,
            static::MIKUCLUB_EU,
            static::MIKUCLUB_UK,
        ];
    }

    /**
     * 获取FILE-CDN相关的域名数组
     * @return string[]
     */
    public static function get_array_file_domain()
    {
        return [
            static::FILE1_MIKUCLUB_FUN,
            static::FILE2_MIKUCLUB_FUN,
            static::FILE3_MIKUCLUB_FUN,
            static::FILE4_MIKUCLUB_FUN,
            static::FILE5_MIKUCLUB_FUN,
            //static::FILE6_MIKUCLUB_FUN,
        ];
    }

    /**
     * 把链接还原成主域名 并且移除HTTP或HTTPS协议部分
     *
     * @param string $url
     * @return string 
     */
    public static function reset_to_main_site_domain_and_remove_protocol($url)
    {
        $array_search = array_merge(
            static::get_array_site_domain(),
            static::get_array_file_domain()
        );

        //当前原始主域名
        $origin_domain = Web_Domain::get_main_site_domain();

        //把链接修正回默认主站域名
        $url = str_replace($array_search, $origin_domain, $url);

        // 移除HTTP或HTTPS协议部分，但保留双斜杠
        $url = preg_replace("/^(https?:)?\//", "//", $url);

        return $url;
    }
}



/**
 * 站内分类ID
 */
class Category
{
    //其他区
    const OTHER = 1;
    //音乐区
    const MUSIC = 9;
    //动漫区
    const ANIME = 942;
    //软件区
    const SOFTWARE = 465;
    //游戏区
    const GAME = 182;
    //小说区
    const FICTION = 294;
    //教程区
    const TUTORIAL = 8621;
    //视频区
    const VIDEO = 9305;


    //魔法区(主分区)
    const ADULT_CATEGORY = 1120;
    //不包含魔法区
    const NO_ADULT_CATEGORY = -1120;


    //魔法MMD-3D区
    const ADULT_3D = 211;
    //魔法同人音声
    const ADULT_ASMR = 3055;
    //魔法写真
    const ADULT_PHOTO = 788;
    //魔法PC游戏
    const ADULT_PC_GAME = 1121;
    //魔法视频
    const ADULT_VIDEO = 1192;
    //魔法动画
    const ADULT_ANIME = 5998;
    //魔法图包
    const ADULT_IMAGE = 6678;
    //魔法小说
    const ADULT_FICTION = 6713;
    //魔法手机游戏/应用
    const ADULT_PHONE_GAME = 7476;


    /**
     * 获取所有成人分区的ID数组
     *
     * @return int[]
     */
    public static function get_array_adult()
    {
        return [
            static::ADULT_CATEGORY,
            static::ADULT_3D,
            static::ADULT_ASMR,
            static::ADULT_PHOTO,
            static::ADULT_PC_GAME,
            static::ADULT_VIDEO,
            static::ADULT_ANIME,
            static::ADULT_IMAGE,
            static::ADULT_FICTION,
            static::ADULT_PHONE_GAME,
        ];
    }

    /**
     * 获取禁止同步到微博的分类ID数组
     *
     * @return int[]
     */
    public static function get_array_not_weibo()
    {
        return [
            static::ADULT_CATEGORY,
            static::MUSIC,
            static::ANIME,
            static::SOFTWARE,
            static::GAME,
            static::FICTION,
            static::TUTORIAL,
            static::VIDEO,
            static::OTHER,

        ];
    }
}

/**
 * 常用过期时间常量 (基础单位是 秒)
 */
class Expired
{
    const EXP_1_MINUTE = 60;
    const EXP_5_MINUTE = 60 * 5;
    const EXP_10_MINUTE = 60 * 10;
    const EXP_15_MINUTE = 60 * 15;
    const EXP_30_MINUTE = 60 * 30;
    const EXP_1_HOUR = 3600; //60 * 60
    const EXP_2_HOURS = 3600 * 2;
    const EXP_4_HOURS = 3600 * 4;
    const EXP_6_HOURS = 3600 * 6;
    const EXP_1_DAY = 86400; //60 * 60 * 24
    const EXP_3_DAYS = 86400 * 3;
    const EXP_7_DAYS = 86400 * 7;
    const EXP_10_DAYS = 86400 * 10;
    const EXP_1_MONTH = 86400 * 30;
    const EXP_6_MONTHS = 86400 * 30 * 6;
    const EXP_1_YEAR = 86400 * 365;

    /**
     * 获取首页过期时间
     *
     * @return int
     */
    public static function get_home_exp_time()
    {
        return static::EXP_15_MINUTE;
    }
}

/**
 * 页面类型
 */
class Page_Type
{

    //主页
    const HOME = 'home';
    //分类页
    const CATEGORY = 'category';
    //标签页
    const TAG = 'tag';
    //搜索页
    const SEARCH = 'search';
    //作者页
    const AUTHOR = 'author';
    //文章页
    const SINGLE = 'single';
    //标准页
    const PAGE = 'page';

    /**
     * 获取当前页面的类型
     * @return string 类型名
     */
    public static function get_current_type()
    {
        $result = 'unknown';

        if (is_home())
        {
            $result = 'home';
        }
        else if (is_single())
        {
            $result = 'single';
        }
        else if (is_page())
        {
            $result = 'page';
        }
        else if (is_category())
        {
            $result = 'category';
        }
        else if (is_tag())
        {
            $result = 'tag';
        }
        else if (is_author())
        {
            $result = 'author';
        }
        else if (is_search())
        {
            $result = 'search';
        }

        return $result;
    }
}

/**
 * 消息类型
 */
class Message_Type
{
    const PRIVATE_MESSAGE = 'private_message';
    const COMMENT_REPLY = 'comment_reply';
}