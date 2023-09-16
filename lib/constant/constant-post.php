<?php

namespace mikuclub\constant;

/**
 * 文章的元数据 键名
 */
class Post_Meta
{
    //文章的点击数
    const POST_VIEWS = 'views';
    //文章的好评数
    const POST_LIKE = 'count_like';
    //文章的差评数
    const POST_UNLIKE = 'count_unlike';
    //文章的评论数
    const POST_COMMENT_COUNT = 'count_comments';
    //文章的收藏数
    const POST_FAVORITE_COUNT = 'count_favorite';
    //文章的失效反馈数
    const POST_FAIL_TIME = 'fail_time';
    //文章的分享次数
    const POST_SHARE_COUNT = 'count_sharing';

    //下载栏1
    const POST_DOWN = 'down';
    //下载栏2
    const POST_DOWN2 = 'down2';
    //密码栏1
    const POST_PASSWORD = 'password';
    //密码栏2
    const POST_PASSWORD2 = 'password2';

    //预览图ID数组
    const POST_PREVIEWS = 'previews';
    //相关的B站视频数据
    const POST_BILIBILI_VIDEO_INFO = 'bilibili_info';
    //判断是否加入微博待发送列表
    const POST_SHARE_TO_WEIBO = 'waiting_for_weibo';
    //分类ID数组
    const POST_CATS = 'cats';
    //主分类ID
    const POST_MAIN_CAT = 'main_cat';
    //子分类ID
    const POST_SUB_CAT = 'sub_cat';

    //封面缩微图ID
    const POST_THUMBNAIL_ID = '_thumbnail_id';
    //封面缩微图URL地址
    const POST_THUMBNAIL_SRC = '_thumbnail_src';

    //所有图片缩微图URL地址数组
    const POST_IMAGES_THUMBNAIL_SRC = 'images_thumbnail_src';
    //所有中等图片URL地址数组
    const POST_IMAGES_SRC = 'images_src';
    //所有原图URL地址数组
    const POST_IMAGES_FULL_SRC = 'images_full_src';

    //判断文章附件是否是用户头像
    const ATTACHMENT_WP_USER_AVATAR = '_wp_attachment_wp_user_avatar';
}

class Post_Query
{
    const CUSTOM_ORDERBY = 'custom_orderby';
    const CUSTOM_ORDER_DATA_RANGE = 'custom_order_data_range';
    const AUTHOR_INTERNAL_SEARCH = 'author_internal_search';
}

/**
 * 文章的状态
 */
class Post_Status
{
    //公开
    const PUBLISH = 'publish';
    //待审
    const PENDING = 'pending';
    //草稿
    const DRAFT = 'draft';
}

/**
 * 文章评价等级
 */
class Post_Feedback_Rank extends Constant
{
    const POSITIVE = '好评如潮';
    const VERY_POSITIVE = '特别好评';
    const MOSTLY_POSITIVE = '多半好评';
    const MIXED = '褒贬不一';
    const MOSTLY_NEGATIVE = '多半差评';
    const NEGATIVE = '差评如潮';
    const NONE = '暂无评分';

    /**
     * 根据好评和差评数量计算出评价等级
     *
     * @param int $positive_count
     * @param int $negative_count
     * @return string
     */
    public static function get_rank($positive_count, $negative_count)
    {
        $result = static::NONE;

        $ratio = 0;

        // 检查是否有评价
        if ($positive_count > 0 || $negative_count > 0)
        {

            // 计算评价比例
            $ratio = $positive_count / ($positive_count + $negative_count) * 100;
            //只保留整数
            $ratio = intval($ratio);

            // 根据比例返回不同的结果
            if ($ratio <= 20)
            {
                $result = static::NEGATIVE;
            }
            elseif ($ratio <= 40)
            {
                $result = static::MOSTLY_NEGATIVE;
            }
            elseif ($ratio <= 70)
            {
                $result = static::MIXED;
            }
            elseif ($ratio <= 80)
            {
                $result = static::MOSTLY_POSITIVE;
            }
            elseif ($ratio <= 95)
            {
                $result = static::VERY_POSITIVE;
            }
            else
            {
                $result = static::POSITIVE;
            }
        }

        $result .= ' ' . $ratio . '%';

        return $result;
    }
}
