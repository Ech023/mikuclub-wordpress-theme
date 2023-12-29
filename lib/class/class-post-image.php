<?php

namespace mikuclub;

use Exception;
use mikuclub\constant\Post_Meta;
use mikuclub\constant\Web_Domain;

/**
 * 文章的附件图片
 */
class Post_Image
{

    //图片大小
    const SIZE_THUMBNAIL = 'thumbnail';
    const SIZE_LARGE = 'large';
    const SIZE_FULL = 'full';


    /**
     * 获取文章相关的预览图id数组
     * 可能存在双重序列化问题
     *
     * @param int $post_id
     *
     * @return int[]
     */

    public static function get_array_image_id($post_id)
    {

        //获取文章图片id数组/矩阵
        $array_preview_img_id = get_post_meta($post_id, Post_Meta::POST_PREVIEWS);

        //只有在不是空值的情况下才会继续
        if (is_array($array_preview_img_id) && count($array_preview_img_id) > 0)
        {
            $first_element = $array_preview_img_id[0];

            //通过第一个元素判断 如果不是 数字, 说明数组还有一层序列号需要解除
            if (!is_numeric($first_element))
            {
                //如果第一个元素是序列化的字符串
                if (is_serialized($first_element))
                {
                    //是序列化的话 就再进行一次反序列化来获取 int数组
                    $array_preview_img_id = unserialize($first_element);
                }
                //如果第一个元素是array, 说明是用矩阵格式保存
                else if (is_array($first_element) && count($first_element) > 0 && is_numeric($first_element[0]))
                {
                    $array_preview_img_id = $first_element;
                }
            }
        }

        return $array_preview_img_id;
    }


    /**
     * 获取文章缩微图ID
     *
     * @param int $post_id
     * @return int
     */
    public static function get_thumbnail_id($post_id)
    {

        $thumbnail_id = get_post_meta($post_id, Post_Meta::POST_THUMBNAIL_ID, true);

        //如果是空的
        if (empty($thumbnail_id))
        {
            $thumbnail_id = static::set_thumbnail_id($post_id);
        }

        return intval($thumbnail_id);
    }


    /**
     * 设置文章缩微图ID
     *
     * @param int $post_id
     * @return int
     */
    public static function set_thumbnail_id($post_id)
    {

        $thumbnail_id = 0;

        //获取文章图片id数组
        $array_image_id = Post_Image::get_array_image_id($post_id);
        //如果不是空的数组
        if (count($array_image_id) > 0)
        {
            //把第一个元素设置为微缩图id
            $thumbnail_id = $array_image_id[0];
            update_post_meta($post_id, Post_Meta::POST_THUMBNAIL_ID, $thumbnail_id);
        }

        return $thumbnail_id;
    }


    /**
     * 获取文章缩微图地址
     *
     * @param int $post_id
     * @return string
     */
    public static function get_thumbnail_src($post_id)
    {

        $thumbnail_src = get_post_meta($post_id, Post_Meta::POST_THUMBNAIL_SRC, true);
        //如果没有相关元数据
        if (empty($thumbnail_src))
        {
            //重新获取新的微缩图地址
            $thumbnail_src = static::set_thumbnail_src($post_id);
        }

        // $thumbnail_src = fix_image_domain_with_file_5($thumbnail_src); 
        //随机选择一个cdn主机
        switch ($post_id % 3)
        {
            case 0:
                $thumbnail_src = fix_image_domain_with_file_5($thumbnail_src);
                break;
            case 1:
                $thumbnail_src = fix_image_domain_with_file_5($thumbnail_src);
                break;
            case 2:
                $thumbnail_src = fix_image_domain_with_file_1($thumbnail_src);
                break;
        }

        return $thumbnail_src;
    }

    /**
     * 设置文章缩微图地址
     *
     * @param int $post_id
     *
     * @return string
     */
    public static function set_thumbnail_src($post_id)
    {
        $thumbnail_src = '';

        //获取缩微图地址数组
        $array_thumbnail_img_src = Post_Image::get_array_image_thumbnail_src($post_id);

        //如果存在微缩图地址数组
        if (count($array_thumbnail_img_src) > 0)
        {

            //获取第一张图片
            $thumbnail_src = $array_thumbnail_img_src[0];

            //更新封面预览图地址
            update_post_meta($post_id, Post_Meta::POST_THUMBNAIL_SRC, $thumbnail_src);

            // $array_search = array_merge(Web_Domain::get_array_site_domain(), Web_Domain::get_array_file_domain());

            //获取当前网址
            // $origin_domain = Web_Domain::get_main_site_domain();

            //去除协议名称
            // $origin_domain = str_replace(['http:', 'https:', '/'], '', $origin_domain);

            // //把图片地址修正回默认主站域名
            // $thumbnail_src = str_replace($array_search, $origin_domain, $thumbnail_src);


        }


        //返回封面缩微图地址
        return $thumbnail_src;
    }


    /**
     * 获取对应文章图片的缩微图大小地址数组
     *
     * @param int $post_id
     * @return string[]
     */
    public static function get_array_image_thumbnail_src($post_id)
    {

        //就从文章元数据中读取本地图片地址
        $array_image_thumbnail_src = get_post_meta($post_id, Post_Meta::POST_IMAGES_THUMBNAIL_SRC, true);
        // 如果还未储存过相关地址
        if (empty($array_image_thumbnail_src))
        {
            //重新获取
            $array_image_thumbnail_src = Post_Image::set_array_image_src($post_id, Post_Meta::POST_IMAGES_THUMBNAIL_SRC);
        }

        // $array_image_thumbnail_src = fix_https_prefix($array_image_thumbnail_src);

        $array_image_thumbnail_src = fix_image_domain_with_file_5($array_image_thumbnail_src);

        return $array_image_thumbnail_src;
    }

    /**
     * 获取对应文章图片的large大小地址数组
     *
     * @param int $post_id
     * @return string[]
     */
    public static function get_array_image_large_src($post_id)
    {

        //就从文章元数据中读取本地图片地址
        $array_image_large_src = get_post_meta($post_id, Post_Meta::POST_IMAGES_SRC, true);
        // 如果还未储存过相关地址
        if (empty($array_image_large_src))
        {
            //重新获取
            $array_image_large_src = Post_Image::set_array_image_src($post_id, Post_Meta::POST_IMAGES_SRC);
        }

        // $array_image_large_src = fix_https_prefix($array_image_large_src);

        //随机选择一个cdn主机
        switch ($post_id % 3)
        {
            case 0:
                $array_image_large_src = fix_image_domain_with_file_2($array_image_large_src);
                break;
            case 1:
                $array_image_large_src = fix_image_domain_with_file_3($array_image_large_src);
                break;
            case 2:
                $array_image_large_src = fix_image_domain_with_file_4($array_image_large_src);
                break;
        }

        return $array_image_large_src;
    }


    /**
     * 获取对应文章图片的原图大小地址数组
     *
     * @param int $post_id
     *
     * @return string[]
     */
    public static function get_array_image_full_src($post_id)
    {

        //就从文章元数据中读取本地图片地址
        $array_image_full_src = get_post_meta($post_id, Post_Meta::POST_IMAGES_FULL_SRC, true);
        // 如果还未储存过相关地址
        if (empty($array_image_full_src))
        {
            //重新获取
            $array_image_full_src = Post_Image::set_array_image_src($post_id, Post_Meta::POST_IMAGES_FULL_SRC);
        }

        // $array_image_full_src = fix_https_prefix($array_image_full_src);

        //$array_image_full_src = fix_image_domain_with_file_4($array_image_full_src);


        //随机选择一个cdn主机
        switch ($post_id % 3)
        {
            case 0:
                $array_image_full_src = fix_image_domain_with_file_2($array_image_full_src);
                break;
            case 1:
                $array_image_full_src = fix_image_domain_with_file_3($array_image_full_src);
                break;
            case 2:
                $array_image_full_src = fix_image_domain_with_file_4($array_image_full_src);
                break;
                // case 3:
                //     $array_image_full_src = fix_image_domain_with_file_4($array_image_full_src);
                //     break;
        }

        return $array_image_full_src;
    }

    /**
     * 设置对应大小的图片的地址
     *
     * @param int $post_id
     * @param string $meta_name 储存数据名
     *
     * @return string[]
     * @throws Exception
     */
    public static function set_array_image_src($post_id, $meta_name)
    {

        $size = '';
        //根据键名获取对应的大小
        switch ($meta_name)
        {
            case Post_Meta::POST_IMAGES_THUMBNAIL_SRC:
                $size = static::SIZE_THUMBNAIL;
                break;

            case Post_Meta::POST_IMAGES_SRC:
                $size = static::SIZE_LARGE;
                break;
            case Post_Meta::POST_IMAGES_FULL_SRC:
                $size = static::SIZE_FULL;
                break;

            default:
                throw new Exception('无效图片键名');
        }

        //获取图片id数组
        $array_image_id = static::get_array_image_id($post_id);

        //把图片id数组转换成图片url地址
        $array_image_src = array_map(function ($image_id) use ($size)
        {
            //获取附件图片的url地址
            $attachment_image_src = wp_get_attachment_image_src($image_id, $size);

            $result = $attachment_image_src[0] ?? '';
            return $result;
        }, $array_image_id);

        //从数组里移除空url的元素
        $array_image_src = array_values(array_filter($array_image_src, function ($src)
        {
            return $src !== '';
        }));

        //如果图片数组是空的, 尝试直接从文章内容里抓取图片
        if (count($array_image_src) === 0)
        {
            $array_image_src[] = static::find_first_image_in_content($post_id);
        }

        //修正链接域名
        $array_image_src = array_map(function ($src)
        {
            return Web_Domain::reset_to_main_site_domain_and_remove_protocol($src);
        }, $array_image_src);

        //确保数组为索引数组
        // $array_image_src = array_values($array_image_src);

        //存储图片地址数组到数据库
        update_post_meta($post_id, $meta_name, $array_image_src);

        //返回图片地址数组
        return $array_image_src;
    }



    /**
     * 更新全部大小的图片地址
     *
     * @param int $post_id
     * @return void
     */
    public static function update_all_array_image_src($post_id)
    {

        foreach ([
            Post_Meta::POST_IMAGES_THUMBNAIL_SRC,
            Post_Meta::POST_IMAGES_SRC,
            Post_Meta::POST_IMAGES_FULL_SRC
        ] as $meta_name)
        {
            static::set_array_image_src($post_id, $meta_name);
        }
    }



    /**
     * 从内容中提取第一张图片, 如果没有则输出随机图片
     *
     * @param int $post_id
     * @return string
     */
    protected static function find_first_image_in_content($post_id)
    {

        $post_content = get_post_field('post_content', $post_id);

        //从文章内容描述里匹配第一个图片
        $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post_content, $matches);

        //获取该图片 src
        $img_src = $matches[1][0] ?? '';

        //如果文章内容和元数据里都没有图片，则显示随机图片
        if (empty($img_src))
        {
            $random = mt_rand(1, 10);
            $img_src = get_template_directory_uri() . '/img/random-thumbnail/' . $random . '.jpg';
        }

        return $img_src;
    }
}
