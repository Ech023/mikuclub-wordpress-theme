/**
 * 自定义 模态窗类型
 */
class MyModal {


    constructor() {

        //根元素
        this.root_element = 'body';

        this.title = '';
        this.body = '';
        this.footer = '';

        //默认模态窗配置
        this.options = {
            backdrop: 'static',
            focus: true,
            keyboard: false,
            //show: true,
        };

        this.modal_container_class = '';
        this.modal_class = '';
        this.modal_header_class = '';
        this.modal_footer_class = '';

        this.modal;
        this.$modal_element;

    }

    /**
     * 创建模态对象, 并且加载到DOM里
     * @param {function|null} show_callback 显示时触发
     * @param {function|null} shown_callback 显示后触发
     * @param {function|null} hide_callback 消失时触发
     * @param {function|null} hidden_callback 笑死后触发
     * @returns {MyModal}
     */
    create(show_callback = null, shown_callback = null, hide_callback = null, hidden_callback = null) {

        this.$modal_element = $(`
        
            <div class="modal fade my-modal  ${this.modal_container_class}" tabindex="-1" >
            <div class="modal-dialog mt-5 ${this.modal_class}">
                <div class="modal-content p-2">
                <div class="modal-header ${this.modal_header_class}">
                    <h5 class="modal-title">${this.title}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>
                </div>
                <div class="modal-body">
                    ${this.body}
                </div>
                <div class="modal-footer ${this.modal_footer_class}">
                    ${this.footer}
                </div>
                </div>
            </div>
            </div>
        `);

        //把模态窗插入到页面中
        $(this.root_element).append(this.$modal_element);

        //绑定 显示事件
        this.$modal_element.on('show.bs.modal', () => {
            if (isFunction(show_callback)) {
                show_callback();
            }
        });

        //绑定 显示完成事件
        this.$modal_element.on('shown.bs.modal', () => {
            if (isFunction(shown_callback)) {
                shown_callback();
            }
        });

        //绑定 消失时间事件
        this.$modal_element.on('hide.bs.modal', () => {
            if (isFunction(hide_callback)) {
                hide_callback();
            }
        });

        //绑定 消失后事件
        this.$modal_element.on('hidden.bs.modal', () => {

            if (isFunction(hide_callback)) {
                hidden_callback();
            }

            //删除模态实例
            this.modal.dispose();
            //从DOM中移除
            this.$modal_element.remove();
        });

        //创建modal 对象
        this.modal = new bootstrap.Modal(this.$modal_element, this.options);

        return this;
    }

    /**
     显示模态窗口
     * @returns {MyModal}
     */
    show() {
        //激活显示
        this.modal.show();
        return this;
    }

    /**
    隐藏模态窗口
    * @returns {MyModal}
    */
    hide() {
        //激活显示
        this.modal.hide();
        return this;
    }


}

/**
 * 私信模态窗
 */
class MyPrivateMessageModal extends MyModal {

    /**
     *
     * @param {number} recipient_id
     * @param {string} recipient_name
     */
    constructor(recipient_id, recipient_name) {

        super();

        this.modal_container_class = 'private-message-modal';
        this.recipient_id = recipient_id;
        this.title = `发送私信给 ${recipient_name}`;

        //自定义大小
        this.modal_class = 'modal-lg';

        this.body = `
            <div class="mb-3">
                <label for="message-content" class="form-label"></label>
                <textarea class="form-control message-content" name="message-content" rows="10" placeholder="私信内容..."></textarea>
            </div>
        `;

        this.footer = `
        
            <a class="btn btn-secondary d-none d-sm-block" href="${MY_SITE.home}/message?type=private_message" target="_blank">
                我的私信
            </a>
            <button type="button" class="btn btn-secondary me-auto d-none d-sm-block" data-bs-dismiss="modal">关闭</button>
            <button type="button" class="btn btn-miku w-50 send_private_message">
                发送
            </button>
            <input type="hidden" name="recipient_id" value="${this.recipient_id}">
        `;


    }

    /**
     * @returns {MyPrivateMessageModal}
     */
    create() {

        const model = super.create();

        this.$modal_element.find('.send_private_message').on('click', () => {
            this.on_click_send_private_message();
        });

        return model;

    }

    /**
     * 发送私信
     */
    on_click_send_private_message() {

        const message_content = this.$modal_element.find('textarea.message-content').val().trim();

        if (!this.recipient_id) {
            TOAST_SYSTEM.add('缺少收件人ID', TOAST_TYPE.error);
            return;
        }

        //如果内容为空
        if (!message_content) {
            TOAST_SYSTEM.add('消息内容不能为空', TOAST_TYPE.error);
            return;
        }

        const data = {
            content: message_content,
            recipient_id: this.recipient_id,
        };



        send_post(
            URLS.privateMessage,
            data,
            () => {
                show_loading_modal();
            },
            () => {
                //创建通知弹窗
                TOAST_SYSTEM.add('消息已发送', TOAST_TYPE.success);
                this.hide();
            },
            defaultFailCallback,
            () => {
                hide_loading_modal();
            }

        );

    }



}


/**
 * 在线播放模态窗
 */
class MyVideoModal extends MyModal {

    /**
     *
     * @param {string} iframe_code
     */
    constructor(iframe_code) {

        super();


        this.modal_container_class = 'video-modal';
        this.title = `在线播放`;

        //自定义大小
        this.modal_class = 'modal-xl';

        this.body = `
            <div class="embed-responsive ratio ratio-16x9">
                ${iframe_code}
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
     * @param {string} image_data  图片数据 base64 data
     */
    constructor(image_data) {

        super();

        this.modal_container_class = 'image-cropper-modal';

        this.title = `更新头像`;
        //自定义大小
        this.modal_class = 'modal-xl';

        this.body = `
            <div class="">
                <img class="img-cropper img-fluid" src="${image_data}" alt="剪切图片" style="max-height: 50vh;"/>
            </div>
        `;

        this.footer = `

            <div class="me-2">
                预览
            </div> 
            <div class="preview me-auto">
            </div>

            <button type="button" class="btn btn-secondary cropper_image">
                剪切
            </button>

            <button type="button" class="btn btn-miku upload_image" disabled>
                保存
            </button>
        `;
    }


    /**
     * @returns {MyImageCropperModal}
     */
    create() {

        const shown_callback = () => {
            this.init_cropper();
        };



        const model = super.create(null, shown_callback);

        this.$modal_element.find('.cropper_image').on('click', () => {
            this.on_click_cropper_image();
        });

        this.$modal_element.find('.upload_image').on('click', () => {
            this.on_click_upload_image();
        });


        return model;

    }

    /**
     * 初始化创建cropper 剪切图片库实例
     */
    init_cropper() {

        const $image = this.$modal_element.find('img.img-cropper');

        $image.cropper({
            aspectRatio: 1,
            viewMode: 2,
            minCropBoxWidth: 100,
            minCropBoxHeight: 100,
        });

    }

    /**
     * 剪切图片
     */
    on_click_cropper_image() {

        const $image = this.$modal_element.find('img.img-cropper');
        const $upload_button = this.$modal_element.find('button.upload_image');

        // 获取剪切对象
        const cropper = $image.data('cropper');
        const canvas = cropper.getCroppedCanvas({ width: 100, height: 100, imageSmoothingQuality: 'high', fillColor: 'white' });
        $(canvas).addClass('rounded-circle');

        //输出预览
        const $preview_element = this.$modal_element.find('.preview');
        $preview_element.empty().append(canvas);

        //激活上传按钮
        $upload_button.removeAttr('disabled');

    }

    /**
     * 上传裁剪后的图片
     */
    on_click_upload_image() {


        const img_data_url = this.$modal_element.find('.preview canvas').get(0).toDataURL();

        //请求主体
        const data = new FormData();
        //随机文件名
        const file_name = Date.now() + '.jpg';
        data.append('file', dataURLtoBlob(img_data_url), file_name);
        data.append('action_update_avatar', true);

        const success_callback = (response) => {
            if (response && response.hasOwnProperty('guid') && response.guid.hasOwnProperty('rendered')) {

                //创建通知弹窗
                TOAST_SYSTEM.add('头像更新成功', TOAST_TYPE.success);
                this.hide();

                // 刷新当前页面
                location.reload();
            }
        }

        send_file(
            URLS.media,
            data,
            () => {
                show_loading_modal();
            },
            success_callback,
            defaultFailCallback,
            () => {
                hide_loading_modal();
            }

        );


    }


}


/**
 * 自定义投诉模态窗
 */
class MyPostReportModal extends MyModal {

    /**
     * @param {string} post_id  图片数据 base64 data
     */
    constructor(post_id) {

        super();

        this.modal_container_class = 'report-modal';

        this.title = `稿件投诉`;

        this.modal_class = '';

        this.post_id = post_id;

        const reportType = [
            '不提供密码-强制加群/关注',
            '下载和描述不符',
            '收费',
            '侵权/禁转',
            '暴力/血腥内容',
            '其他'
        ];

        const reportTypeHTML = reportType.reduce((previousValue, currentValue, index) => {
            previousValue += `
                 <div class="form-check my-2">
                              <input class="form-check-input" type="radio" id="report_type_${index}" name="report_type" value="${currentValue}">
                              <label class="form-check-label" for="report_type_${index}">${currentValue}</label>
                        </div>
            `;
            return previousValue;
        }, '');

        this.body = `
                <div>
                    <div class="mb-3">
                        <div class="text-muted small">- 如果只是下载地址失效了, 请点击反馈失效按钮, 不需要提交投诉</div>
                        <div class="text-muted small">
                            - 如果是密码错误/解压错误, 请先查看描述, 之后再看看解压教程, 都无果的话再尝试评论留言
                        </div>
                    </div>
                    <div class="mb-3">
                         <label class="form-label">
                            投诉类型<span class="text-danger">*</span>
                        </label>
                        ${reportTypeHTML}
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">
                            你觉得这个稿件有什么问题？<span class="text-danger">*</span>
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
        
            <button type="button" class="btn btn-miku send_report">提交</button>
 
        `;
    }


    /**
     * @returns {MyPostReportModal}
     */
    create() {

        const model = super.create();

        this.$modal_element.find('.send_report').on('click', () => {
            this.send_post_report();
        });

        return model;

    }

    /**
     * 发送文章投诉
     */
    send_post_report() {


        const $reportTypeRadio = this.$modal_element.find('input[name="report_type"]:checked');

        //如果未选中任何投诉类型
        // if (!$reportTypeRadio.length) {
        //     TOAST_SYSTEM.add('请先选择投诉类型', TOAST_TYPE.error);
        //     return;
        // }

        //获取表单内容
        const report_type = $reportTypeRadio.val();
        if (!report_type) {
            TOAST_SYSTEM.add('请先选择投诉类型', TOAST_TYPE.error);
            return;
        }


        const report_description = this.$modal_element.find('textarea[name="report_description"]').val().trim();
        //如果未填写描述
        if (!report_description) {
            TOAST_SYSTEM.add('请描述具体问题', TOAST_TYPE.error);
            return;
        }

        const report_contact = this.$modal_element.find('input[name="report_contact"]').val().trim();

        //查询参数
        const data = {
            report_type,
            post_id: this.post_id,
            report_description,
        };

        if (report_contact) {
            data.report_contact = report_contact;
        }


        send_post(
            URLS.messageReport,
            data,
            () => {
                show_loading_modal();
            },
            () => {
                //创建通知弹窗
                TOAST_SYSTEM.add('投诉成功, 管理员将会在48小时内审核处理', TOAST_TYPE.success);
                this.hide();
            },
            defaultFailCallback,
            () => {
                hide_loading_modal();
            }

        );

    }

}

/**
 * 确认模态窗
 */
class ConfirmModal extends MyModal {

    /**
     */
    constructor(text) {

        super();


        this.modal_container_class = 'confirm-modal';
        this.modal_class = '';
        this.modal_header_class = 'd-none';

        // this.title = '确认';

        this.body = `
            <div class="text-center">
               <div>${text}</div>
            </div>
        `;

        this.footer = `

           
            <button type="button" class="btn btn-primary px-4 me-2 confirm">确定</button>
            <button type="button" class="btn btn-secondary px-4 cancel">取消</button>

        `;

    }

    /**
    * @param {function|null} confirm_callback 点击确认的时候触发的回调
    * @param {function|null} cancel_callback 点击取消的时候触发的回调
    * @returns {ConfirmModal}
    */
    create(confirm_callback, cancel_callback) {

        const model = super.create();

        this.$modal_element.find('.confirm').on('click', () => {

            this.hide();

            if (isFunction(confirm_callback)) {
                confirm_callback();
            }
        });

        this.$modal_element.find('.cancel').on('click', () => {

            this.hide();

            if (isFunction(cancel_callback)) {
                cancel_callback();
            }
        });


        return model;

    }

}

/**
 * 加载模态窗
 */
class MyLoadingModal extends MyModal {

    /**
     */
    constructor() {

        super();


        this.modal_container_class = 'loading-modal overflow-y-hidden';
        this.modal_class = 'modal-sm modal-dialog-centered';
        this.modal_header_class = 'd-none';
        this.modal_footer_class = 'd-none';

        this.body = `
            <div class="text-center">
                <div class="my-4 spinner-border fs-4" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;

    }

    /**
     * @returns {MyLoadingModal}
     */
    create() {

        return super.create(
            () => {
                //自定义z-index数值 和 移除 显示动画
                this.$modal_element.css('z-index', 1080);
                this.$modal_element.removeClass('fade');
            },
            () => {
                //自定义背景板 z-index数值
                this.$modal_element.next('.modal-backdrop').css('z-index', 1070);
            }
        );

    }





}