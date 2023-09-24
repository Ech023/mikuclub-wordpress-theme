<?php

namespace mikuclub;

use mikuclub\constant\Config;

/**
 * 文件缓存系统
 */
class File_Cache
{
    /*缓存系统ROOT文件夹*/
    const ROOT_DIRECTORY = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'cache_file' . DIRECTORY_SEPARATOR;

    /*子文件夹*/
    const DIR_USER = 'user';
    const DIR_POST = 'post';
    const DIR_POSTS = 'posts';
    const DIR_COMMENTS = 'comments';
    const DIR_COMPONENTS = 'components';


    /*全站相关缓存 键名*/
    const SITE_POST_COUNT = 'count_total_post';
    const SITE_COMMENT_COUNT = 'count_total_comment';
    const SITE_CATEGORY_COUNT = 'count_total_category';
    const SITE_TAG_COUNT = 'count_total_tag';

    const SITE_FRIEND_LINK = 'friend_link';

    const MAIN_CATEGORY_LIST = 'main_category_list';


    /*用户相关缓存 键名*/
    //用户文章数
    const USER_POST_COUNT = 'user_post_count';
    //用户文章查看数
    const USER_POST_TOTAL_VIEW = 'user_post_total_views';
    //用户文章收到评论数
    const USER_POST_TOTAL_COMMENT = 'user_post_total_comments';
    //用户文章收到好评数
    const USER_POST_TOTAL_LIKE = 'user_post_total_likes';
    //用户文章收到差评数
    const USER_POST_TOTAL_UNLIKE = 'user_post_total_likes';
    //用户投稿退件邮件通知 (避免短时间发送多封邮件给同一个用户)
    const USER_REJECT_POST_EMAIL = 'email_reject_post';


    /*文章列表相关缓存 键名*/
    //普通文章列表
    const POST_LIST = 'post_list';
    //热门榜文章列表
    const HOT_POST_LIST = 'hot_post_list';
    //相关文章列表
    const RELATED_POST_LIST = 'related_post_list';

    /*文章相关缓存 键名*/
    const POST_CONTENT_PART_1 = 'post_content_part_1';
    const POST_CONTENT_PART_2 = 'post_content_part_2';

    //文章关键词
    const POST_META_DESCRIPTION = 'post_meta_description';



    /**
     * 读取缓存
     *
     * @param string $meta_key
     * @param string $sub_directory
     * @param int $expired 文件有效时间
     * @return mixed|null 如果不存在则返回NULL
     */
    public static function get_cache_meta($meta_key, $sub_directory, $expired)
    {
        $result = null;

        //如果缓存系统有激活
        //@phpstan-ignore-next-line
        if (Config::ENABLE_FILE_CACHE_SYSTEM)
        {

            //创建完整文件路径
            $file_path =  static::build_file_path($sub_directory, $meta_key);

            //如果缓存存在, 并且没有过期
            if (file_exists($file_path) && filemtime($file_path) + $expired > time())
            {
                //如果反序列化或者文件读取错误, 重设结果为空字符串
                $result = unserialize(file_get_contents($file_path));
                if ($result === false)
                {
                    $result = null;
                }
            }
        }

        return $result;
    }

    /**
     * 设置缓存
     *
     * @param string $meta_key
     * @param string $sub_directory
     * @param mixed $meta_value
     * @return void
     */
    public static function set_cache_meta($meta_key, $sub_directory, $meta_value)
    {
        //如果缓存系统有激活
        //@phpstan-ignore-next-line
        if (Config::ENABLE_FILE_CACHE_SYSTEM)
        {
            //创建完整文件路径
            $file_path =  static::build_file_path($sub_directory, $meta_key);
            //新建文件
            file_put_contents($file_path, serialize($meta_value));
        }
    }

    /**
     * 删除缓存
     *
     * @param string $meta_key
     * @param string $sub_directory
     * @return void
     */
    public static function delete_cache_meta($meta_key, $sub_directory = '')
    {
        //如果缓存系统有激活
        //@phpstan-ignore-next-line
        if (Config::ENABLE_FILE_CACHE_SYSTEM)
        {
            //创建完整文件路径
            $file_path =  static::build_file_path($sub_directory, $meta_key);

            //如果文件存在
            if (file_exists($file_path))
            {
                //删除它
                unlink($file_path);
            }
        }
    }



    /**
     * 清空文件缓存系统
     * @param string $sub_directory
     * @return void
     */
    public static function clean_cache($sub_directory = '')
    {
        $path = static::ROOT_DIRECTORY . $sub_directory;
        static::delete_recursive_file($path);
    }

    /**
     * 递归删除文件夹及其内容
     *
     * @param string $path
     * @return void
     */
    protected static function delete_recursive_file($path)
    {

        //如果不是目录, 中断递归回调
        if (!is_dir($path))
        {
            return;
        }

        //确保每个路径都包含斜杠
        if (substr($path, -1) !== DIRECTORY_SEPARATOR)
        {
            $path .= DIRECTORY_SEPARATOR;
        }

        //扫描一个文件夹内的所有文件夹和文件并返回数组
        $files = scandir($path);
        //遍历路径
        foreach ($files as $file)
        {
            //排除返回符
            if ($file != "." && $file != "..")
            {
                //子文件完整路径
                $file_path = $path . $file;
                //如果子文件还是文件夹
                if (is_dir($file_path))
                {
                    //递归删除子文件内的内容
                    static::delete_recursive_file($file_path); // 递归删除子目录
                    //删除空目录
                    rmdir($file_path);
                }
                //如果是文件
                else
                {
                    //删除文件
                    unlink($file_path);
                }
            }
        }
    }

    /**
     * 清楚文件名里的特殊字符
     *
     * @param string $file_name
     * @return string
     */
    protected static function sanitize_file_name($file_name)
    {
        return preg_replace('/[^a-z0-9_]+/', '-', strtolower($file_name));
    }

    /**
     * 构建完整的文件路径
     *
     * @param string $sub_directory
     * @param string $file_name
     * @return string
     */
    protected static function build_file_path($sub_directory, $file_name)
    {
        //如果存在子文件夹信息
        if ($sub_directory)
        {
            //创建子文件夹路径
            $sub_directory_path = static::ROOT_DIRECTORY . $sub_directory . DIRECTORY_SEPARATOR;
        }
        //如果不存在子文件夹
        else
        {
            //直接使用ROOT地址
            $sub_directory_path = static::ROOT_DIRECTORY;
        }

        //如果子分组文件夹不存在 就递归创建文件夹
        if (!file_exists($sub_directory_path))
        {
            mkdir($sub_directory_path, 0777, true);
        }

        //创建完整文件路径
        $file_path =  $sub_directory_path . static::sanitize_file_name($file_name);

        return $file_path;
    }
}
