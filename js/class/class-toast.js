/**
 * 自定义 消息通知弹窗
 */
class MyToast {

    static TYPE_ERROR = 'error';
    static TYPE_SUCCESS = 'success';
    static TYPE_CONTINUE = 'continue';

    /**
     * 
     * @param {string} title 
     * @param {string} message 
     * @param {string} type 
     */
    constructor(title, message, type) {

        //根元素
        this.root_element = 'body';
        //提示框的容器
        this.container_class_name = 'my-toast-container';

        /**
         * @type {MyToast}
         */
        this.toast;

        //默认配置
        this.options = {
            animation: true,
            autohide: true,
            delay: 5000,
            //show: true,
        };



        this.title = title || 'unknown';
        this.message = message || 'unknown';
        this.type = type;

        //如果是长时间弹窗 设置成30秒
        if (this.type === MyToast.TYPE_CONTINUE) {
            this.options.delay = 30 * 1000;
        }

    }

    /**
     * 创建提示框, 并加载到DOM里
     * @returns {MyToast}
     */
    create() {

        let icon = '';
        let color_class = '';

        //根据类型配置图标和颜色
        switch (this.type) {
            case MyToast.TYPE_ERROR:
                icon = 'fa-solid fa-exclamation-triangle';
                color_class = 'text-danger';
                break;
            case MyToast.TYPE_SUCCESS:
                icon = 'fa-solid fa-check-circle';
                color_class = 'text-miku';
                break;
            case MyToast.TYPE_CONTINUE:
                icon = 'fa-solid fa-spinner fa-spin';
                color_class = 'text-primary';
        }

        const $element = $(`

            <div class="toast my-toast mb-2 p-1 bg-light-1" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class="${icon} ${color_class} fa-lg me-2"></i>
                    <span class="${color_class} me-auto h5 mb-0">${this.title}</span>
                    <span class="">${new Date().format('hh:mm:ss')}</span>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close">
                    </button>
                </div>
                <div class="toast-body">
                    ${this.message}
                </div>
            </div>

        `);

        //如果容器元素不存在
        let $container_element = $('.' + this.container_class_name);
        if ($container_element.length === 0) {
            $container_element = $('<div class="my-toast-container position-fixed end-0 top-0 mt-2 mx-3 mx-md-4" style="z-index: 1100;"></div>');
            //创建一个新的容器 并添加到ROOT里
            $(this.root_element).append($container_element);
        }

        //把提示框插入到容器内
        $container_element.append($element);

        /**
         * 弹窗显示时触发
         */
        $element.on('show.bs.toast', () => {

            //如果提示框已经超过3个, 提前移除第一个提示框
            const $my_toast_element = $('body .' + this.container_class_name + ' .my-toast');
            if ($my_toast_element.length > 3) {
                $my_toast_element.first().remove();
            }

        });

        /**
         * 弹窗消失时触发
         */
        $element.on('hidden.bs.toast', () => {
            //从DOM中移除
            $element.remove();
        });

        //在元素上 创建 toast 对象
        this.toast = new bootstrap.Toast($element, this.options);

        return this;
    }

    /**
     * 显示提示框
     * @returns {MyToast}
     */
    show() {
        this.toast.show();
        return this;
    }

    /**
     * 创建提示框 显示成功提示
     * @param {string} message
     */
    static show_success(message) {
        new MyToast('成功', message, MyToast.TYPE_SUCCESS).create().show();
    }

    /**
     * 创建提示框 显示错误提示
     * @param {string} message
    */
    static show_error(message) {
        new MyToast('错误', message, MyToast.TYPE_ERROR).create().show();
    }

    /**
    * 创建提示框 显示加载中提示
    * @param {string} message
       */
    static show_continue(message) {
        new MyToast('提交中', message, MyToast.TYPE_CONTINUE).create().show();
    }
}

