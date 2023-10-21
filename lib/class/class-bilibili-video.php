<?php

namespace mikuclub;

use mikuclub\constant\Expired;
use mikuclub\constant\Post_Meta;
use WP_Error;

/**
 * 获取bilibili视频信息
 */
class Bilibili_Video
{
    //b站视频信息接口
    const API_URL = 'https://api.bilibili.com/x/web-interface/view';



    /**
     *通过B站API获取视频相关信息
     *
     * @param int $post_id
     * @param string|null $aid
     * @param string|null $bvid
     *
     * @return array<string, string>|WP_Error
     */
    public static function get_video_meta($post_id, $aid = null, $bvid = null)
    {


        // $result = File_Cache::get_cache_meta_with_callback(
        //     Post_Meta::POST_BILIBILI_VIDEO_INFO,
        //     File_Cache::DIR_POST . DIRECTORY_SEPARATOR . $post_id,
        //     Expired::EXP_7_DAYS,
        //     function () use ($post_id, $aid, $bvid)
        //     {

        //尝试从数据库查询
        $result = get_post_meta($post_id, Post_Meta::POST_BILIBILI_VIDEO_INFO, true);

        //都没有 则重新远程请求
        if (empty($result))
        {
            //根据参数生成请求数组
            $query_params = [];
            if ($aid)
            {
                $query_params['aid'] = $aid;
            }
            else if ($bvid)
            {
                $query_params['bvid'] = $bvid;
            }

            //把参数数组转换成url字符串
            $query_string = http_build_query($query_params);

            $response = wp_remote_get(static::API_URL . '?' . $query_string);

            $body = wp_remote_retrieve_body($response);
            if (!$body)
            {
                return new WP_Error(500, __FUNCTION__ . ' : 请求失败');
            }

            $body = json_decode($body, true);
            //如果相关数据不存在
            if (!isset($body['data']) || !isset($body['data']['aid']) || !isset($body['data']['bvid']) || !isset($body['data']['pages']) || count($body['data']['pages']) == 0 || !isset($body['data']['pages'][0]['cid']) || !$body['data']['pages'][0]['cid'])
            {
                return new WP_Error(500, __FUNCTION__ . ' : 获取AID,BVID 和 CID失败');
            }

            $result = [
                'aid' => $body['data']['aid'],
                'bvid' => $body['data']['bvid'],
                'cid' => $body['data']['pages'][0]['cid'],
            ];

            //保存到数据库
            update_post_meta($post_id, Post_Meta::POST_BILIBILI_VIDEO_INFO, $result);
        }

        return $result;
        //     }
        // );

        // return $result;
    }

    /**
     * 删除bilibili解析的元数据
     *
     * @param int $post_id
     * @return void
     */
    public static function delete_video_meta($post_id)
    {

        //从数据库里删除数据
        delete_post_meta($post_id, Post_Meta::POST_BILIBILI_VIDEO_INFO);

        // $cache_key = Post_Meta::POST_BILIBILI_VIDEO_INFO . '_' . $post_id;
        //删除文件缓存
        // File_Cache::delete_cache_meta($cache_key, File_Cache::DIR_POST . DIRECTORY_SEPARATOR . $post_id);
    }
}
