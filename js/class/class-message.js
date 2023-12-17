/**
 * 自定义 私信类
 */
class MyPrivateMessage {

    constructor(message) {

        this.id = message.id;
        this.sender_id = message.sender_id;
        this.recipient_id = message.recipient_id;
        this.content = message.content;
        this.respond = message.respond;
        this.status = message.status;
        this.date = message.date;

        //如果有作者信息 才创建对应的js实例
        if (message.author) {
            this.author = new MyAuthor(message.author);
        }

    }

    //输出html
    toHTML() {

        let author_avatar = '';
        let unread = '';
        let unread_text = '';


        //如果有作者信息
        if (this.author) {
            author_avatar = `

                <a href="${this.author.user_href}" title="查看UP主空间" target="_blank">
                    <img class="avatar rounded-circle" src="${this.author.user_image}" width="40" height="40" alt="用户头像">
                </a>`;
        }


        if (this.status === 0) {
            unread_text = '未读';
        }
        else if (this.status === 2) {
            unread_text = '对方未读';
        }
        else if (this.status === 3) {
            unread_text = '对方已读';
        }

        unread = `
                <span class="badge text-bg-miku">${unread_text}</span>
        `;

        //内容移除html标签
        this.content = this.content.replace(/(<([^>]+)>)/ig, '');

        return `

            <div class="message-item border-bottom accordion-item" data-sender-id="${this.sender_id}">
                    <div class="row align-items-center gx-2 p-2 cursor_pointer" data-bs-toggle="collapse" data-bs-target="#sender-id-${this.sender_id}" >
                            <div class="col-12 col-md-2">
                                ${author_avatar}
                                <span class="mx-2 display-name small">${this.author.display_name}</span>
                            </div>
                            <div class="col mt-2 mt-md-0">
                                <div>
                                    ${this.content} ${unread}
                                </div>
                                <div class="small text-dark-2 mt-2">
                                    时间: ${this.date}
                                </div>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-sm btn-light-2">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>

                    </div>
                    
                    <div class="accordion-collapse collapse" id="sender-id-${this.sender_id}" data-bs-parent="#private-message-accordion" >
                            <div class="accordion-body border-top py-5 overflow-auto" style="max-height: 90vh;">
                            </div>
                    </div>
            </div>
        
        `;

    }

}

/**
 * 自定义私信类 和特定收件人
 */
class MyPrivateMessageWithOneSender extends MyPrivateMessage {

    toHTML() {

        let itemDirection;
        let textDirection;

        //如果是用户自己发送的私信
        if (MY_SITE.user_id === parseInt(this.sender_id)) {
            //改变元素位置
            itemDirection = 'justify-content-end';
            textDirection = 'text-end';
        }
        //否则 默认为左边定位
        else {
            itemDirection = 'justify-content-start';
            textDirection = 'text-start';
        }

        return `

            <div class="row ${itemDirection} my-2">
                    <div class="col-10 ${textDirection}">
                        <div class="d-inline-block bg-light-2 p-2 rounded-1">
                            ${this.content}
                        </div>
                        <div class="small text-dark-2 mt-1">
                            ${this.date}
                        </div>
                    </div>
            </div>
        `;
    }

}

/**
 * 自定义 评论回复类
 */
class MyCommentReply {
    constructor(comment) {

        this.comment_id = comment.comment_id;
        this.comment_content = comment.comment_content;
        this.comment_date = comment.comment_date;
        this.comment_parent = comment.comment_parent;
        this.comment_post_id = comment.comment_post_id;

        this.comment_parent_user_read = comment.comment_parent_user_read;
        this.comment_post_title = comment.comment_post_title;
        this.comment_post_href = comment.comment_post_href;

        this.author = new MyAuthor(comment.author);

    }

    //输出html
    toHTML() {


        const author_avatar = `

                <a class="" href="${this.author.user_href} " title="查看用户主空间" target="_blank">
                    <img class="avatar rounded-circle" src="${this.author.user_image}" width="40" height="40" alt="用户头像">
                </a>`;

        let unread = '';
        //如果是未读
        if (!this.comment_parent_user_read) {
            unread = `
                <span class="badge text-bg-miku">
                    未读
                </span>
            `;
        }


        return `

            <div class="message-item border-bottom">
                    <div class="row align-items-center p-2 gx-2"  >
                            <div class="col-12 col-md-2">
                                ${author_avatar}
                                <span class="mx-2 display-name small">${this.author.display_name}</span>
                            </div>
                            <div class="col mt-2 mt-md-0">
                                <div>
                                    ${this.comment_content}  ${unread}
                                </div>
                                <div class="mt-2">
                                    <a class="small text-dark-2 btn btn-sm btn-light-2" href="${this.comment_post_href}#comments-part" target="_blank" title="查看来源页面">
                                    来源链接: ${this.comment_post_title}
                                    </a>
                                </div> 
                                <div class="small text-dark-2 mt-2">
                                    时间: ${this.comment_date}
                                </div>
                            </div>
                            <div class="col-auto">
                                <a class="btn btn-sm btn-light-2" href="${this.comment_post_href}#comments-part" target="_blank" title="查看来源页面">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            </div>
                    </div>
                    
            </div>
        
        `;

    }


}

class MyForumReply {

    constructor(forum_reply) {

        this.postid = forum_reply.postid;
        this.parentid = forum_reply.parentid;
        this.forumid = forum_reply.forumid;
        this.topicid = forum_reply.topicid;
        this.userid = forum_reply.userid;
        this.title = forum_reply.title;
        this.body = forum_reply.body;
        this.created = forum_reply.created;
        this.modified = forum_reply.modified;
        this.post_href = forum_reply.post_href;

        this.author = new MyAuthor(forum_reply.author);

    }

    //输出html
    toHTML() {


        const author_avatar = `

        <a class="" href="${this.author.user_href} " title="查看用户主空间" target="_blank">
            <img class="avatar rounded-circle" src="${this.author.user_image}" width="40" height="40" alt="用户头像">
        </a>`;


        return `

            <div class="message-item border-bottom">
                    <div class="row align-items-center p-2 gx-2"  >
                            <div class="col-12 col-md-2">
                                ${author_avatar}
                                <span class="mx-2 display-name small">${this.author.display_name}</span>
                            </div>
                            <div class="col mt-2 mt-md-0">
                                <div>
                                    ${this.body} 
                                </div>
                                <div class="mt-2">
                                    <a class="small text-dark-2 btn btn-sm btn-light-2" href="${this.post_href}" target="_blank" title="查看来源页面">
                                    来源链接: ${this.title}
                                    </a>
                                </div> 
                                <div class="small text-dark-2 mt-2">
                                    时间: ${this.created}
                                </div>
                            </div>
                            <div class="col-auto">
                                <a class="btn btn-sm btn-light-2" href="${this.post_href}" target="_blank" title="查看来源页面">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            </div>
                    </div>
                    
            </div>

        `;

    }
}


/**
 * 自定义  消息列表类 (支持 私信, 评论回复 和论坛回复)
 */

class MyMessageList extends Array {

    constructor(messageType) {
        super();
        this.messageType = messageType;
    }

    /**
     * 批量把消息数据列表转换成自定义类
     * @param {Array} messageList
     */
    add(messageList) {

        if (isNotEmptyArray(messageList)) {

            messageList.forEach((message) => {

                let myPrivateMessage;

                switch (this.messageType) {
                    case MESSAGE_TYPE.privateMessage:
                        myPrivateMessage = new MyPrivateMessage(message);
                        break;
                    case MESSAGE_TYPE.privateMessageWithOneSender:
                        myPrivateMessage = new MyPrivateMessageWithOneSender(message);
                        break;
                    case MESSAGE_TYPE.commentReply:
                        myPrivateMessage = new MyCommentReply(message);
                        break;
                    case MESSAGE_TYPE.forumReply:
                        myPrivateMessage = new MyForumReply(message);
                        break;
                }

                this.push(myPrivateMessage);
            });
        }

    }

    toHTML() {

        //有内容的情况下
        let output = '';

        if (this.length) {
            //循环累积输出所有文章
            output = this.reduce((previousOutput, currentElement) => {
                previousOutput += currentElement.toHTML();
                return previousOutput;
            }, '');
        }

        return output;

    }

}




