/**
 * 自定义 模态窗类型
 */
class MyModal {


    constructor() {

        this.parentElement = 'body';

        this.title = '';
        this.body = '';
        this.footer = '';
        //默认模态窗配置
        this.options = {
            backdrop: 'static',
            keyboard: false,
            focus: true,
            show: true,
        };

        this.size = 'modal-lg';

        this.modalClass = '';

    }

    /**
     显示模态窗口
     */
    show() {

        let $modal = $(`
        
            <div class="modal fade my-modal  ${this.modalClass}" tabindex="-1" >
              <div class="modal-dialog mt-5 ${this.size}">
                <div class="modal-content p-2">
                  <div class="modal-header">
                    <h5 class="modal-title">${this.title}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                      
                    </button>
                  </div>
                  <div class="modal-body">
                    ${this.body}
                  </div>
                  <div class="modal-footer">
                    ${this.footer}
                  </div>
                </div>
              </div>
            </div>
        `);

        //把模态窗插入到页面中
        $(this.parentElement).append($modal);
        
        //$modal.modal();

        //创建modal 对象
        var modal = new bootstrap.Modal($modal, this.options);
        //激活显示
        modal.show();

    }


}

/**
 * 私信模态窗
 */
class MyPrivateMessageModal extends MyModal {

    /**
     *
     * @param {number}recipientId
     * @param {string}recipientName
     */
    constructor(recipientId, recipientName) {
        super();

        this.modalClass = 'private-message-modal';
        this.recipientId = recipientId;
        this.title = `发送私信给 ${recipientName}`;
        this.body = `
        
                <div class="mb-3">
                    <label for="message-content" class="form-label"></label>
                   <textarea class="form-control message-content" name="message-content" rows="10" placeholder="私信内容..."></textarea>
                </div>
        `;

        this.footer = `
        
            <a class="btn btn-secondary d-none d-sm-block" href="${MY_SITE.home}/message?type=private_message" target="_blank">我的私信</a>
            <button type="button" class="btn btn-secondary me-auto d-none d-sm-block" data-bs-dismiss="modal">关闭</button>
            <button type="button" class="btn btn-miku w-50 send-private-message">
                <span>发送</span>
                <span class="spinner-border text-light spinner-border-sm" role="status" style="display:none; vertical-align: sub;">
                </span>
            </button>
            <input type="hidden" name="recipient_id" value="${this.recipientId}">
        `;


    }
}


/**
 * 在线播放模态窗
 */
class MyVideoModal extends MyModal {

    /**
     *
     * @param {string} iframeCode
     */
    constructor(iframeCode) {

        super();


        this.modalClass = 'video-modal';
        this.title = `在线播放`;

        //自定义大小
        this.size = 'modal-xl';

        this.body = `
                <div class="embed-responsive ratio ratio-16x9">
                    ${iframeCode}
                </div>
        `;

        this.footer = `
        
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">关闭</button>
    
        `;


    }

}

/**
 * 自定义头像剪切模态窗
 */
class MyImageCropperModal extends MyModal {

    /**
     * @param {string} imageData  图片数据 base64 data
     */
    constructor(imageData) {

        super();

        this.modalClass = 'image-cropper-modal';

        this.title = `更新头像`;
        //自定义大小
        this.size = 'modal-xl';

        this.body = `
                <div class="">
                        <img class="img-cropper img-fluid" src="${imageData}" alt="剪切图片" style="max-height: 50vh;"/>
                 </div>
                
             
        `;

        this.footer = `

            <div class="me-2">
                    预览
            </div> 
             <div class="preview me-auto">
    
            </div>

             <button type="button" class="btn btn-secondary  cropper-image">
                剪切
             </button>
             
  
        
            <button type="button" class="btn btn-miku  upload-image" disabled>
                <span>保存</span>
                <span class="spinner-border text-light spinner-border-sm" role="status" style="display:none; vertical-align: sub;">
                </span>
            </button>
        `;
    }

}


/**
 * 自定义投诉模态窗
 */
class MyReportModal extends MyModal {

    /**
     * @param {string} imageData  图片数据 base64 data
     */
    constructor(postId) {

        super();

        this.modalClass = 'report-modal';

        let reportType = [
            '不提供密码-强制加群/关注',
            '下载和描述不符',
            '收费',
            '侵权/禁转',
            '暴力/血腥内容',
            '其他'
        ];

        let reportTypeHTML = reportType.reduce((previousValue, currentValue, index) => {
            previousValue += `
                 <div class="form-check my-2">
                              <input class="form-check-input" type="radio" id="report_type_${index}" name="report_type" value="${currentValue}">
                              <label class="form-check-label" for="report_type_${index}">${currentValue}</label>
                        </div>
            `;
            return previousValue;
        }, '');


        this.title = `稿件投诉`;

        this.body = `
                <div class="">
                    <div class="mb-3">
                         <label class="form-label">
                           你觉得这个稿件有什么问题？<span class="text-danger">*</span>
                        </label>
                        ${reportTypeHTML}
                    </div>
                     <div class="mb-3">
                        <small class="form-text text-muted">如果只是下载地址失效了, 请点击反馈失效按钮, 不需要提交投诉</small>
                     </div>
                      <div class="mb-3">
                        <small class="form-text text-muted">如果是密码错误, 请先查看页面上的文件解压教程</small>
                     </div>
                    <div class="mb-3">
                        <label class="form-label">
                            问题描述 (请详细说明)<span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" name="report_description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">
                            联系方式 (可选)
                        </label>
                        <input type="text" class="form-control" name="report_contact" />
                        <small class="form-text text-muted">QQ号 或者 邮箱地址</small>
                    </div>
                    
                 </div>
                
             
        `;

        this.footer = `

            <button type="button" class="btn btn-secondary me-auto" data-bs-dismiss="modal">关闭</button>
        
            <button type="button" class="btn btn-miku  send-report">
                <span>提交</span>
                <span class="spinner-border text-light spinner-border-sm" role="status" style="display:none; vertical-align: sub;">
                </span>
            </button>
             <input type="hidden" name="post_id" value="${postId}" />
        `;
    }

}
