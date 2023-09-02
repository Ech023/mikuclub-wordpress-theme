<?php

namespace mikuclub\lib;

/**
 * 文章评价等级
 */
class Post_feedback_rank extends Constant
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
