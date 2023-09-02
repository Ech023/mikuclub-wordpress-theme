/**
 * 自定义 消息通知弹窗
 */
class MyToast {


    constructor(title, message, type) {

        this.title = title || 'unknown';
        this.message = message || 'unknown';
        this.type = type;
        this.duration = 5000;

        this.autohide = true;

        //如果是长时间弹窗 设置成30秒
        if(type === TOAST_TYPE.continue){
            this.duration = 30 * 1000;
        }

    }

    /**
     * 输出弹窗html代码
     * @returns {string}
     */
    toHTML() {

        let icon = '';
        let colorClass = '';
        //根据类型配置图标和颜色
        switch (this.type) {
            case TOAST_TYPE.error :
                icon = 'fa-solid fa-exclamation-triangle';
                colorClass = 'text-danger';
                break;
            case TOAST_TYPE.success :
                icon = 'fa-solid fa-check-circle';
                colorClass = 'text-miku';
                break;
            case TOAST_TYPE.continue:
                icon = 'fa-solid fa-spinner fa-spin';
                colorClass = 'text-primary';
        }

        return `
        
          <div class="toast p-1 bg-white" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="${this.duration}" data-bs-autohide="${this.autohide}">
            <div class="toast-header">
            
                  <i class="${icon}  ${colorClass} fa-lg me-2"></i>
                  <strong class="${colorClass} me-auto h5 mb-0">${this.title}</strong>
                  <small class="">${new Date().format('hh:mm:ss')}</small>
                  <button type="button" class="mb-1 btn-close" data-bs-dismiss="toast" aria-label="Close">
                  </button>
             
            </div>
            <div class="toast-body">
              ${this.message}
            </div>
          </div>
        
        
        `;
    }


}

/**
 * 自定义 消息弹窗管理
 */
class MyToastSystem {

    constructor() {
        this.parentElement = 'body';
        this.toastContainerClass = 'my-toast-system';
        this.toastContainerHTML = '<div class="my-toast-system position-fixed end-0 top-0 m-3"></div>';
        this.toastContainer = '';
    }


    /**
     * 添加显示新弹窗
     * @param message 内容
     * @param type 弹窗类型
     */
    add(message, type) {

        //如果还未初始化弹窗容器
        if (!this.toastContainer) {
            //创建插入到页面中
            this.toastContainer = $(this.toastContainerHTML);
            $(this.parentElement).append(this.toastContainer);
        }

        let title;
        if (type === TOAST_TYPE.error) {
            title = '错误';
        } else if(type === TOAST_TYPE.success){
            title = '成功';
        }
        else{
            title ='正在提交';
        }

        //创建新弹窗元素
        let $toast = $(new MyToast(title, message, type).toHTML());
        //插入到页面
        $(`${this.parentElement} .${this.toastContainerClass}`).append($toast);
        //显示弹窗
        $toast.toast('show');

    }

}


