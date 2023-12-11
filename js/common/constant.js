/// <reference path="../common/base.js" />
/// <reference path="../function.js" />
/// <reference path="../function-ajax.js" />

const POST_LIST_LENGTH = 48;
const MAX_POST_LIST_LENGTH = POST_LIST_LENGTH * 2;

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

const COMMENT_SMILES = {
    ':neutral:': 'icon_neutral.gif',
    ':???:': 'icon_confused.gif',
    ':mrgreen:': 'icon_mrgreen.gif',
    ':twisted:': 'icon_twisted.gif',
    ':arrow:': 'icon_arrow.gif',
    ':shock:': 'icon_eek.gif',
    ':smile:': 'icon_smile.gif',
    ':cool:': 'icon_cool.gif',
    ':evil:': 'icon_evil.gif',
    ':grin:': 'icon_biggrin.gif',
    ':idea:': 'icon_idea.gif',
    ':oops:': 'icon_redface.gif',
    ':razz:': 'icon_razz.gif',
    ':roll:': 'icon_rolleyes.gif',
    ':wink:': 'icon_wink.gif',
    ':cry:': 'icon_cry.gif',
    ':eek:': 'icon_surprised.gif',
    ':lol:': 'icon_lol.gif',
    ':mad:': 'icon_mad.gif',
    ':sad:': 'icon_sad.gif',
    '8-)': 'icon_01.gif',
    '8-O': 'icon_02.gif',
    ':-(': 'icon_03.gif',
    ':-)': 'icon_04.gif',
    ':-?': 'icon_05.gif',
    ':-D': 'icon_06.gif',
    ':-P': 'icon_07.gif',
    ':-o': 'icon_08.gif',
    ':-x': 'icon_09.gif',
    ':-|': 'icon_10.gif',
    ';-)': 'icon_11.gif',
    '8O': 'icon_eek.gif',
    ':(': 'icon_sad.gif',
    ':)': 'icon_smile.gif',
    ':?': 'icon_confused.gif',
    ':D': 'icon_biggrin.gif',
    ':P': 'icon_razz.gif',
    ':o': 'icon_surprised.gif',
    ':x': 'icon_mad.gif',
    ':|': 'icon_neutral.gif',
    ';)': 'icon_wink.gif',
    ':!:': 'icon_exclaim.gif',
    ':?:': 'icon_question.gif',
};


const POST_STATUS = {
    publish: 'publish',
    pending: 'pending',
    draft: 'draft',
    trash: 'trash',

    /**
     * @param {string} status 
     * @returns {string}
     */
    get_description(status) {

        let result = '';
        switch (status) {
            case this.publish:
                result = '已公开';
                break;
            case this.pending:
                result = '等待审核';
                break;
            case this.draft:
                result = '草稿';
                break;
            case this.trash:
                result = '已删除';
                break;
        }

        return result;
    },

    /**
     * @param {string} status 
     * @returns {string}
     */
    get_text_color_class(status) {
        let result = '';
        switch (status) {
            case this.publish:
                result = 'text-success';
                break;
            case this.pending:
                result = 'text-danger';
                break;
            case this.draft:
                result = 'text-dark-2';
                break;
            case this.trash:
                result = 'text-dark-2';
                break;
        }

        return result;
    }
}