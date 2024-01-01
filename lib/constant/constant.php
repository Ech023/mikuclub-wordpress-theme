<?php

namespace mikuclub\constant;

use ReflectionClass;

use function mikuclub\convert_link_to_https;

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
    const CSS_JS_VERSION = '1.16';

    //默认时间格式
    const DATE_FORMAT_SHORT = 'Y-m-d';
    const DATE_FORMAT = 'y-m-d H:i:s';
    const DATE_FORMAT_MYSQL = 'Y-m-d H:i:s';

    //启动文件缓存系统
    const ENABLE_FILE_CACHE_SYSTEM = true;
    //关闭文件缓存
    // const ENABLE_FILE_CACHE_SYSTEM = false;

    //主页 关注用户列表文章数量
    const HOME_MY_FOLLOWED_POST_LIST_LENGTH = 12;
    //主页 列表文章数量
    const HOME_POST_LIST_LENGTH = 6;


    //幻灯片里显示的文章数量
    //主体列表长度 和 辅助列表的长度
    const STICKY_POST_FIRST_LIST_LENGTH = 3;
    const STICKY_POST_SECONDARY_LIST_LENGTH = 8;

    //幻灯片(手动置顶)基础有效天数 (15天)
    const STICKY_POST_MANUAL_EXPIRED_DAY = 15;
    //幻灯片(高点赞)基础有效天数 (15天)
    const STICKY_POST_TOP_LIKE_EXPIRED_DAY = 7;

    //热门列表文章数量
    const HOT_POST_LIST_LENGTH = 6;
    //热门文章基础有效天数
    const HOT_POST_EXPIRED_DAY = 7;

    //默认文章列表数量
    const POST_LIST_LENGTH = 48;


    //内容页底部相关推荐里的文章数量
    const RELATED_POST_LIST_LENGTH = 6;


    //最新发布的论坛帖子列表长度
    const RECENT_FORUM_TOPIC_LIST_LENGTH = 8;

    //单页的评论数量
    const NUMBER_COMMENT_PER_PAGE = 40;
    //单页的高赞评论数量
    const NUMBER_TOP_LIKE_COMMENT_PER_PAGE = 3;

    //单页的回复评论数量
    const NUMBER_COMMENT_REPLY_PER_PAGE = 20;
    //单页的论坛回复数量
    const NUMBER_FORUM_REPLY_PER_PAGE = 20;

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
    const STATIC_MIKUCLUB_FUN = 'static.mikuclub.fun';
    const FILE1_MIKUCLUB_FUN = 'file1.mikuclub.fun';
    const FILE2_MIKUCLUB_FUN = 'file2.mikuclub.fun';
    const FILE3_MIKUCLUB_FUN = 'file3.mikuclub.fun';
    const FILE4_MIKUCLUB_FUN = 'file4.mikuclub.fun';
    const FILE5_MIKUCLUB_FUN = 'file5.mikuclub.fun';
    const FILE6_MIKUCLUB_FUN = 'file6.mikuclub.fun';


    //DEBUG专用域名
    const LOCALHOST = 'localhost/html';
    const HTML = 'html';

    //第三方域名
    const PAN_BAIDU_COM = 'pan.baidu.com';
    const ALIYUN_DRIVE_CHECK = 'https://api.aliyundrive.com/adrive/v3/share_link/get_share_by_anonymous';

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
            //debug专用
            // static::LOCALHOST,
            static::HTML,
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
    public static function reset_to_main_site_domain($url)
    {
        $array_search = array_merge(
            static::get_array_site_domain(),
            static::get_array_file_domain()
        );

        //当前原始主域名
        $origin_domain = Web_Domain::get_main_site_domain();

        //把链接修正回默认主站域名
        $url = str_replace($array_search, $origin_domain, $url);

        //确保链接为https格式
        $url = convert_link_to_https($url);

        // 移除HTTP或HTTPS协议部分，但保留双斜杠
        // $url = preg_replace("/^(https?:)?\/\//", "//", $url);

        return $url;
    }


}



/**
 * 站内分类ID
 */
class Category
{
    //歌姬PV区
    const DIVA = 7;
    //MMD区
    const MMD = 3;
    //其他区
    const OTHER = 1;
    //音乐区
    const MUSIC = 9;
    //图片区
    const IMAGE = 789;
    //演唱会
    const LIVE = 8;
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
    //视频番剧区
    const VIDEO = 9305;
    //舞蹈区
    const DANCE = 19828;

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
    const ADULT_IMAGE = 19829;
    //魔法漫画
    const ADULT_MANGA = 6678;
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
            static::ADULT_MANGA,
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
            static::DANCE,
            static::OTHER,

        ];
    }
}

/**
 * 网站菜单位置
 */
class Site_Menu
{

    const MAIN_MENU = 'nav';
    const LEFT_SIDE_MENU = 'left_side_menu';
    // const PAGE_MENU = 'pagemenu';
    const TOP_LEFT_MENU = 'top_left_menu';
    const BOTTOM_MENU = 'bottom_menu';

    /**
     * 获取菜单位置的名称
     * 
     * @param string $key
     * @return string
     */
    public static function get_description($key)
    {
        $result = '';
        switch ($key)
        {
            case static::MAIN_MENU:
                $result = '网站导航';
                break;
            case static::LEFT_SIDE_MENU:
                $result = '网站侧边栏菜单';
                break;
                // case static::PAGE_MENU:
                //     $result = '页面导航';
                //     break;
            case static::TOP_LEFT_MENU:
                $result = '顶部左菜单';
                break;
            case static::BOTTOM_MENU:
                $result = '底部菜单';
                break;
        }
        return $result;
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
    //未知类型
    const UNKNOWN = 'unknown';

    /**
     * 获取当前页面的类型
     * @return string 类型名
     */
    public static function get_current_type()
    {
        $result = static::UNKNOWN;

        if (is_home())
        {
            $result = static::HOME;
        }
        else if (is_single())
        {
            $result = static::SINGLE;
        }
        else if (is_page())
        {
            $result = static::PAGE;
        }
        else if (is_category())
        {
            $result = static::CATEGORY;
        }
        else if (is_tag())
        {
            $result = static::TAG;
        }
        else if (is_author())
        {
            $result = static::AUTHOR;
        }
        else if (is_search())
        {
            $result = static::SEARCH;
        }

        return $result;
    }
}

/**
 * 用来告诉JS, 需要使用的POST文章模板种类
 */
class Post_Template
{
    const DEFAULT = 'default';
    //收藏夹文章
    const FAVORITE_POST = 'favorite_post';
    //访问历史文章
    const HISTORY_POST = 'history_post';
    //投稿管理文章
    const MANAGE_POST = 'manage_post';
    //下载失效里的文章
    const FAIL_DOWNLOAD_POST = 'fail_download_post';
}

/**
 * 消息类型
 */
class Message_Type
{
    const PRIVATE_MESSAGE = 'private_message';
    const COMMENT_REPLY = 'comment_reply';
    const FORUM_REPLY = 'forum_reply';
}

/**
 * 下载链接类型
 */
class Download_Link_Type
{

    //百度网盘
    const BAIDU_PAN = 'baidu';
    //夸克
    const QUARK = 'quark';
    //阿里云盘
    const ALIYUN_DRIVE = 'aliyun_drive';
    //UC
    const UC_DRIVE = 'uc_drive';
    //蓝奏云
    const LANZOU = 'lanzou';
    //腾讯微云
    const TENCENT_WEIYUN = 'weiyun';
    //115
    const ONE_ONE_FIVE = '115_drive';
    //迅雷
    const XUNLEI = 'xunlei';
    //城通
    const CT_FILE = 'ct_drive';
    //曲奇
    const QUQI = 'quqi_drive';
    //电信天翼云
    const YUN_189 = '189_drive';
    //移动和彩云
    const YUN_139 = '139_drive';
    //磁力
    const MAGNET = 'magnet_link';
    //ONE DRIVE
    const ONE_DRIVE = 'one_drive';
    //MEGA 盘
    const MEGA = 'mega_drive';

    const PIKPAK = 'pikpak';

    /**
     * 解析下载链接来获取对应的下载类型
     *
     * @param string $link
     * @return string
     */
    public static function get_type_by_link($link)
    {
        $result = '';

        if ($link)
        {
            $array_drive_path = [
                'pan.baidu.com' => static::BAIDU_PAN,
                'quark' => static::QUARK,
                'aliyundrive' => static::ALIYUN_DRIVE,
                'drive.uc' => static::UC_DRIVE,
                'lanzou' => static::LANZOU,
                'weiyun' => static::TENCENT_WEIYUN,
                '115.com' => static::ONE_ONE_FIVE,
                'xunlei' => static::XUNLEI,
                //'t00y.com' => static::CT_FILE,
                'quqi' => static::QUQI,
                '189' => static::YUN_189,
                '139' => static::YUN_139,
                'magnet' => static::MAGNET,
                'sharepoint' => static::ONE_DRIVE,
                'mega' => static::MEGA,
                'pikpak' => static::PIKPAK,
            ];

            // 识别下载地址对应的网盘名称 一旦找到匹配的关键字，就可以结束循环
            foreach ($array_drive_path as $drive_path => $type)
            {
                if (stripos($link, $drive_path) !== false)
                {
                    $result = $type;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * 获取下载类型描述
     *
     * @param string $type
     * @return string
     */
    public static function get_description($type)
    {

        $result = '';

        switch ($type)
        {
            case static::BAIDU_PAN:
                $result = '百度网盘';
                break;
            case static::QUARK:
                $result = '夸克网盘';
                break;
            case static::ALIYUN_DRIVE:
                $result = '阿里云盘';
                break;
            case static::UC_DRIVE:
                $result = 'UC网盘';
                break;
            case static::LANZOU:
                $result = '蓝奏云';
                break;
            case static::TENCENT_WEIYUN:
                $result = '腾讯微云';
                break;
            case static::ONE_ONE_FIVE:
                $result = '115盘';
                break;
            case static::XUNLEI:
                $result = '迅雷云盘';
                break;
            case static::CT_FILE:
                $result = '城通盘';
                break;
            case static::QUQI:
                $result = '曲奇云盘';
                break;
            case static::YUN_189:
                $result = '天翼云';
                break;
            case static::YUN_139:
                $result = '和彩云';
                break;
            case static::MAGNET:
                $result = '磁力链接';
                break;
            case static::ONE_DRIVE:
                $result = 'OneDrive (要梯子)';
                break;
            case static::MEGA:
                $result = 'MEGA盘 (要梯子)';
                break;
            case static::PIKPAK:
                $result = 'PikPak盘 (要梯子)';
                break;
        }

        return $result;
    }
}
