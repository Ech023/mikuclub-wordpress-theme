/**
 * 自定义 UA信息类
 * 通过UAParser来解析ua获取对应数据
 * @link https://github.com/faisalman/ua-parser-js
 */
class MyUAParser {

    /**
     *
     * @param {string} userAgent
     */
    constructor(userAgent) {

        this.ua = '';
        this.browser = '';
        this.device = '';
        this.os = '';
        this.browserName = '';

        let result = UAParser(userAgent);

        if (result.hasOwnProperty('ua')) {
            this.ua = result.ua;
        }
        if (result.hasOwnProperty('browser') && result.browser.hasOwnProperty('name')) {
            this.browser = result.browser.name;
            this.browserName = this.getBrowserTranslate(result.browser.name);
        }
        if (result.hasOwnProperty('device') && result.device.hasOwnProperty('vendor')) {
            this.device = result.device.vendor;
        }
        if (result.hasOwnProperty('os') && result.os.hasOwnProperty('name')) {
            this.os = result.os.name;
        }

    }

    /**
     * 把特定浏览器名称翻译成中文
     * @param {string} browserName
     */
    getBrowserTranslate(browserName) {

        let browsers = {
            '2345Explorer': '2345',
            '360 Browser': '360',
            'Android Browser': '安卓',
            'Avant': '爱帆',
            'Facebook': '脸书',
            'Firefox': '火狐',
            'Maxthon': '傲游',
            'QQBrowser': 'QQ',
            'QQBrowserLite': 'QQ',
            'UCBrowser': 'UC',
            'WeChat': '微信',
            'BIDUBrowser': '百度',
            'Baidu': '百度',
            'baidu': '百度',
            'baiduboxapp': '百度',
            'LBBROWSER': '猎豹',
        };

        //如果不是空值, 遍历检测匹配
        if (browserName) {
            for (const key in browsers) {
                if (key === browserName) {
                    browserName = browsers[key];
                    break;
                }
            }
        }

        return browserName;
    }


}

