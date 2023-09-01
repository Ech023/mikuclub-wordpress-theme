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

    /**
     * 根据好评和差评数量计算出评价等级
     *
     * @param int $positive_count
     * @param int $negative_count
     * @return string 好评和差评为0 则返回空字符串
     */
    public static function get_rank($positive_count, $negative_count)
    {
        $result = '';

        // 检查是否有评价
        if ($positive_count > 0 || $negative_count > 0)
        {

            // 计算评价比例
            $ratio = $positive_count / ($positive_count + $negative_count);

            // 根据比例返回不同的结果
            if ($ratio <= 0.2)
            {
                $result = static::NEGATIVE;
            }
            elseif ($ratio <= 0.4)
            {
                $result = static::MOSTLY_NEGATIVE;
            }
            elseif ($ratio <= 0.7)
            {
                $result = static::MIXED;
            }
            elseif ($ratio <= 0.8)
            {
                $result = static::MOSTLY_POSITIVE;
            }
            elseif ($ratio <= 0.95)
            {
                $result = static::VERY_POSITIVE;
            }
            else
            {
                $result = static::POSITIVE;
            }
        }

        return $result;
    }
}
