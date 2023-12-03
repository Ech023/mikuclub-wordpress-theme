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


        //如果有作者信息
        if (this.author) {
            author_avatar = `

                <a href="${this.author.user_href}" title="查看UP主空间" target="_blank">
                    <img class="avatar rounded-circle" src="${this.author.user_image}" width="40" height="40" alt="用户头像">
                </a>`;
        }
        if (!this.status) {

            unread = `
                <span class="badge text-bg-miku">
                    未读
                </span>
            `;

        }

        //内容移除html标签
        this.content = this.content.replace(/(<([^>]+)>)/ig, '');

        return `

            <div class="card message-item border-bottom-0 rounded-0 ">
                    <div class="card-header row bg-transparent  align-items-center border-0 py-3 cursor_pointer" data-bs-toggle="collapse" data-bs-target="#sender-id-${this.sender_id}">
                            <div class="col-6 col-md-2 ">
                                ${author_avatar}
                                <span class="mx-2 display-name">${this.author.display_name}</span>
                            </div>
                            <div class="col-6 col-md-2">
                                <i class="fa-solid fa-clock"></i> ${this.date}
                            </div>
                            <div class="col-9 col-md-6 text-truncate mt-2 mt-md-0">
                                ${this.content} 
                            </div>
                            <div class="col-1">
                                ${unread}
                            </div>
                            <div class="col-1">
                                <i class="fa-solid fa-chevron-right"></i>
                            </div>

                    </div>
                    
                     <div class="collapse " id="sender-id-${this.sender_id}" data-sender="${this.sender_id}"  data-bs-parent="#accordion" >
                            <div class="card-body border-top py-5 overflow-auto" style="max-height: 90vh;">
                            
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
                        <div class="border mx-2 my-1 d-inline-block rounded p-3">
                            ${this.content}
                        </div>
                        <div class=" mx-2 my-1 ">
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


        let author_avatar = `

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

        let parentPostLink = '';
        if (this.comment_post_href) {
            parentPostLink = `
                        <a class="stretched-link " href="${this.comment_post_href}#comments-part" target="_blank" title="查看来源页面"></a>
            `;

        }

        return `

            <div class="card message-item border-bottom-0 rounded-0 ">
                    <div class="card-header row bg-transparent  align-items-center border-0 py-3 cursor_pointer"  >
                            <div class="col-6 col-md-2 ">
                                ${author_avatar}
                                <span class="mx-2 display-name">${this.author.display_name}</span>
                            </div>
                            <div class="col-6 col-md-2">
                                <i class="fa-solid fa-clock"></i> ${this.comment_date}
                            </div>
                            <div class="col-9 col-md-6 text-truncate mt-2 mt-md-0">
                                <div class="">${this.comment_content}</div>
                                <div class="small mt-3">评论来源: ${this.comment_post_title}</div> 
                            </div>
                            <div class="col-1">
                                ${unread}
                            </div>
                            <div class="col-1">
                                <i class="fa-solid fa-chevron-right"></i>
                            </div>
                           ${parentPostLink}
                    </div>
                    
            </div>
        
        `;

    }


}
/*
class MyForumReply {

    constructor(forumReply) {

        this.id = forumReply.id;
        this.post_content = forumReply.post_content;
        this.post_date = forumReply.post_date;
        this.parent_post_title = forumReply.parent_post_title;
        this.parent_post_href = forumReply.parent_post_href;

        this.post_author = new MyAuthor(forumReply.post_author);

        this.parent_user_read = forumReply.parent_user_read;


    }

    //输出html
    toHTML() {


        let author_avatar = `

                <a href="${this.post_author.user_href}" title="查看用户空间" target="_blank">
                    <img class="avatar rounded-circle" src="${this.post_author.user_image}" width="40" height="40" alt="用户头像">
                </a>`;

        let unread = '';
        //如果是未读
        if (this.parent_user_read !== '') {
            unread = `
                <span class="badge text-bg-miku">
                    未读
                </span>
            `;
        }

        let parentPostLink = '';
        if (this.parent_post_href) {
            parentPostLink = `
                 <a class="stretched-link " href="${this.parent_post_href}" target="_blank" title="查看来源页面"></a>
            `;
        }

        //内容移除html标签
        this.post_content = this.post_content.replace(/(<([^>]+)>)/ig, '');


        return `

            <div class="card message-item border-bottom-0 rounded-0 " >
                    <div class="card-header row bg-transparent  align-items-center border-0 py-3 cursor_pointer"  >
                             <div class="col-6 col-md-2 ">
                                ${author_avatar}
                                <span class="mx-2 display-name">${this.post_author.display_name}</span>
                            </div>
                           <div class="col-6 col-md-2">
                                <i class="fa-solid fa-clock"></i> ${this.post_date}
                            </div>
                             <div class="col-9 col-md-6 text-truncate mt-2 mt-md-0">
                                <div class="">${this.post_content}</div>
                                <div class="small mt-3">帖子来源: ${this.parent_post_title}</div> 
                            </div>
                            <div class="col-1">
                                ${unread}
                            </div>
                            <div class="col-1">
                                <i class="fa-solid fa-chevron-right"></i>
                            </div>
                            ${parentPostLink}

                    </div>
                    
            </div>
        
        `;

    }
}*/


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
                    /*
                    case MESSAGE_TYPE.forumReply:
                        myPrivateMessage = new MyForumReply(message);
                        break;*/
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




