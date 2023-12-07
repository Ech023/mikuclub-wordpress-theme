/// <reference path="../common/base.js" />
/// <reference path="../function.js" />
/// <reference path="../function-ajax.js" />

const POST_FEEDBACK_RANK = {

    positive: '好评如潮',
    very_positive: '特别好评',
    mostly_positive: '多半好评',
    mixed: '褒贬不一',
    mostly_negative: '多半差评',
    negative: '差评如潮',
    none: '暂无评分',

    /**
     * 根据好评和差评数量计算出评价等级
     * @param {number} positive_count 
     * @param {number} negative_count 
     * @returns {string}
     */
    get_rank(positive_count, negative_count) {

        let result = this.none;

        let ratio = 0;

        positive_count = parseInt(positive_count);
        negative_count = parseInt(negative_count);

        // 检查是否有评价
        if (positive_count > 0 || negative_count > 0) {
            // 计算评价比例
            ratio = (positive_count / (positive_count + negative_count)) * 100;
            //只保留整数
            ratio = parseInt(ratio);

            // 根据比例返回不同的结果
            if (ratio <= 20) {
                result = this.negative;
            } else if (ratio <= 40) {
                result = this.mostly_negative;
            } else if (ratio <= 70) {
                result = this.mixed;
            } else if (ratio <= 80) {
                result = this.mostly_positive;
            } else if (ratio <= 95) {
                result = this.very_positive;
            } else {
                result = this.positive
            }
        }

        

        result += " " + ratio + "%";

        return result;

    }



}