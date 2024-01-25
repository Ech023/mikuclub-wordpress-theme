/**


 /**
 * 自定义 评论类
 */
class MyComment {
    /**
     * @param {Object}comment
     * @param {boolean}depth 嵌套评论
     * @param {boolean}isNew 嵌套评论
     */
    constructor(comment, depth, isNew) {

        this.comment_id = comment.comment_id;
        this.comment_post_id = comment.comment_post_id;
        this.comment_date = comment.comment_date;

        this.comment_content = comment.comment_content.trim();

        this.comment_approved = comment.comment_approved;
        this.comment_agent = new MyUAParser(comment.comment_agent);
        this.comment_parent = comment.comment_parent;
        this.author = new MyCommentAuthor(comment.author);

        this.children = [];

        this.comment_likes = comment.comment_likes || 0;
        //是否是置顶评论
        this.comment_is_sticky = comment.comment_is_sticky || 0;

        //是否是嵌套子评论
        this.depth = depth;

        //是否是新插入评论
        this.isNew = isNew;

        //如果有子评论
        if (Array.isArray(comment.children) && comment.children.length) {
            //遍历创建子评论的实例
            this.children = comment.children.map(childComment => {
                return new MyComment(childComment, true, false);
            });
        }


    }


    //输出html
    toHTML() {

        //判断当前用户是否是管理员
        const is_admin = MY_SITE.is_admin;
        //判断当前用户是否是评论人
        const is_comment_author = MY_SITE.user_id === parseInt(this.author.id)

        //判断当前用户是否是文章的作者
        const is_post_author = MY_SITE.user_id === MY_SITE.post_author_id;

        //判断当前评论是否是文章的作者发布
        const is_post_author_comment =  parseInt(this.author.id) === MY_SITE.post_author_id;
  

        //判断当前用户是否是高级用户 并且是 文章的作者
        const is_premium_user_and_post_author = MY_SITE.is_premium_user && is_post_author && false;
       


        //递归输出 子评论
        let childComments = '';
        if (this.children.length) {
            childComments = this.children.reduce((previousOutput, currentElement) => {
                previousOutput += currentElement.toHTML();
                return previousOutput;
            }, '');
        }

        // 默认头像大小
        let avatarSize = 40;
        //如果是子评论
        if (this.depth) {
            avatarSize = 40;
        }

        let authorAvatar = `
                <a class="" href="${this.author.user_href} " title="查看用户主空间" target="_blank">
                    <img class="avatar rounded-circle" src="${this.author.user_image}" width="${avatarSize}" height="${avatarSize}" alt="用户头像">
                </a>`;

        let authorDisplayName = this.author.display_name;
        //如果是UP主自己发的评论
        //添加高亮显示
        if (is_post_author_comment) {
            authorDisplayName = `
                <span class="px-1 me-1 border border-danger rounded text-danger">UP主</span>
                <span class="text-danger fw-bold">${authorDisplayName}</span>
            `;
        }

        //遍历勋章数组 累计输出html
        let authorBadges = '';
        if (isNotEmptyArray(this.author.user_badges)) {
            authorBadges = this.author.user_badges.reduce((previousOutput, currentElement) => {
                //如果有勋章信息
                previousOutput += `<span class="${currentElement['class']} rounded-1 m-1">${currentElement['title']}</span>`;

                return previousOutput;
            }, '');
        }

        //输出评论用户设备信息
        let deviceInfo = '';
        if (this.comment_agent.browser) {

            let browserIcon = '';
            switch (this.comment_agent.browser.toLowerCase()) {

                case 'chrome':
                case 'chromium':
                case 'chrome webview':

                    browserIcon = 'fa-brands fa-chrome';
                    break;
                case 'edge':
                    browserIcon = 'fa-brands fa-edge';
                    break;
                case 'firefox':
                    browserIcon = 'fa-brands fa-firefox-browser';
                    break;
                case 'qq':
                case 'qqbrowser':
                case 'qqbrowserlite':
                    browserIcon = 'fa-brands fa-qq';
                    break;
                case 'safari':
                case 'mobile safari':
                    browserIcon = 'fa-brands fa-safari';
                    break;
                default:
                    browserIcon = 'fa-brands fa-internet-explorer';
            }

            deviceInfo += `<span class="m-1"><i class="${browserIcon}"></i> ${this.comment_agent.browserName}</span>`;
        }
        if (this.comment_agent.os) {

            let systemIcon = '';
            switch (this.comment_agent.os.toLowerCase()) {

                case 'android':
                    systemIcon = 'fa-brands fa-android';
                    break;
                case 'linux':
                    systemIcon = 'fa-brands fa-linux';
                    break;
                case 'ubuntu':
                    systemIcon = 'fa-brands fa-ubuntu';
                    break;
                case 'mac os':
                case 'ios':
                    systemIcon = 'fa-brands fa-apple';
                    break;
                default:
                    systemIcon = 'fa-brands fa-windows';
            }
            deviceInfo += `<span class="m-1"><i class="${systemIcon}"></i> ${this.comment_agent.os}</span>`;
        }
        if (this.comment_agent.device) {
            deviceInfo += `<span class="m-1"><i class="fa-solid fa-mobile-alt"></i> ${this.comment_agent.device}</span>`;
        }

        //输出评论点赞功能
        let likeButtons = '';


        let addCommentLikesButtonClass = 'add_comment_likes';
        let deleteCommentLikesButtonClass = 'delete_comment_likes';

        //点赞按钮图标
        let addCommentLikesButtonIcon = 'fa-solid fa-thumbs-up';
        let deleteCommentLikesButtonIcon = 'fa-solid fa-thumbs-down';

        //如果用户已经点赞过 就更改图标样式  和 注销点赞按钮
        let arrayCommentLiked = getLocalStorage(LOCAL_STORAGE_KEY.commentLikes);

        if (arrayCommentLiked && arrayCommentLiked.includes(parseInt(this.comment_id))) {
            addCommentLikesButtonClass = 'text-miku disabled';
            addCommentLikesButtonIcon = 'fa-solid fa-thumbs-up';
        }

        //如果用户已经点踩过 就更改图标样式 和 注销踩按钮
        let arrayCommentDisLiked = getLocalStorage(LOCAL_STORAGE_KEY.commentDislikes);
        if (arrayCommentDisLiked && arrayCommentDisLiked.includes(parseInt(this.comment_id))) {
            deleteCommentLikesButtonClass = 'text-miku disabled';
            deleteCommentLikesButtonIcon = 'fa-solid fa-thumbs-down';
        }

        //如果评论作者和当前用户是同个人 禁用点赞功能, 避免点赞自己
        if (is_comment_author) {
            addCommentLikesButtonClass = 'disabled';
            deleteCommentLikesButtonClass = 'disabled';
        }

        likeButtons = `
        <div class="comment-likes">
            <a href="javascript:void(0);" class="text-dark-2 ${addCommentLikesButtonClass}" >
                <i class="${addCommentLikesButtonIcon}"></i> 点赞
            </a>
            <span class="mx-2 comment-likes-count">
                ${this.comment_likes}
            </span>
             <a href="javascript:void(0);" class="text-dark-2 ${deleteCommentLikesButtonClass}">
                <i class="${deleteCommentLikesButtonIcon}"></i> 踩
            </a>
        </div>
        `;


        //如果有登陆, 并且不是作者自己 才输出回复按钮
        let respondButton = '';
        if (MY_SITE.user_id > 0 && !is_comment_author) {
            respondButton = `<a class="respond_button btn btn-sm btn-light-2" href="javascript:void(0);" data-respond="${this.comment_id}" data-respond-name="${this.author.display_name}">回复</a>`;
        }


        let moreButton = '';
        let stickyButton = '';
        let deleteButton = '';
        let blackListButton = '';

        //如果是管理员 或者是 文章作者 输出置顶按钮
        if (is_admin || is_post_author) {

            //根据评论置顶状态输出
            const sticky_button_class = this.comment_is_sticky ? 'delete_sticky_comment' : 'add_sticky_comment';
            const sticky_button_text = this.comment_is_sticky ? '取消置顶' : '置顶';

            stickyButton = `
                <li>
                    <button class="dropdown-item small ${sticky_button_class}" data-comment-id="${this.comment_id}">${sticky_button_text}</button>
                </li>
            `;
        }

        //如果是管理员 或者是 高级的文章作者 或者是 评论人自己 输出删除按钮
        if (is_admin || is_premium_user_and_post_author || is_comment_author) {

            deleteButton = `
                <li>
                    <button class="dropdown-item small delete_comment" data-comment-id="${this.comment_id}">删除</button>
                </li>
                
            `;
        }

        //如果有登陆, 并且不是作者自己 输出 黑名单按钮
        if (MY_SITE.user_id > 0 && !is_comment_author) {

            let black_list_button_class = 'add_user_black_list';
            let black_list_button_text = '加入黑名单';
            //如果目标用户已经被拉黑
            if (MY_SITE.user_black_list.includes(parseInt(this.author.id))) {

                black_list_button_class = 'delete_user_black_list';
                black_list_button_text = '从黑名单里移除';
            }

            blackListButton = `
             <li><button class="dropdown-item small ${black_list_button_class}"  data-target-user-id="${this.author.id}">${black_list_button_text}</button></li>
         `;

        }

        //如果要输出删除按钮 或者 拉黑按钮
        if (stickyButton || deleteButton || blackListButton) {
            //显示更多菜单
            moreButton = `
                <div class="dropdown" >
                    <button class="btn btn-sm btn-light-2" data-bs-toggle="dropdown" title="更多操作">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                    <ul class="dropdown-menu">
                        ${stickyButton}
                        ${deleteButton}
                        ${blackListButton}
                        
                    </ul>
                </div>
            `;
        }


        let styleClass = '';
        let statusText = '';
        let statusTextClass = '';
        //如果触发了待审核关键词
        if (this.comment_approved === 'trash') {
            styleClass = 'rounded-1 border border-danger';
            statusTextClass = 'text-danger';
            // statusText = '该评论包含违禁词'; //不要提示用户
        }
        //如果触发了违禁词
        else if (parseInt(this.comment_approved) === 0) {
            styleClass = 'rounded-1 border border-danger ';
            statusTextClass = 'text-danger';
            statusText = '等待管理员审核后才会显示';
        }
        else if (this.isNew) {
            styleClass = 'rounded-1 border border-success';
            statusTextClass = 'text-success';
            statusText = '已发表';
        }
        else if (this.comment_is_sticky) {
            styleClass = 'rounded-1 border border-miku';
            statusTextClass = 'text-miku';
            statusText = '已置顶';
        }


        return `

            <div class="comment-item my-1 comment-item-${this.comment_id}" data-comment_id="${this.comment_id}">
            
                    <div class="row comment-body py-2 border-bottom ${this.depth ? 'ms-4 ms-md-5 ' : ''} ${styleClass}">
                    
                            <div class="col-12 col-sm-auto avatar-container text-center text-sm-start">
                                 ${authorAvatar}
                            </div>
                            <div class="col">
                                <div class="row align-items-center g-2">
                                    <div class="col-12">
                                        <div class="user-meta text-center text-sm-start">
                                            <a class="m-1 d-block d-sm-inline small" href="${this.author.user_href} " title="查看用户主空间" target="_blank">${authorDisplayName}</a>
                                            <span class="badge text-bg-miku rounded-1 m-1">${this.author.user_level}</span>
                                            ${authorBadges}
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="comment-content my-2 small" style="white-space: pre-line;" >${this.comment_content}</div>
                                    </div>
                                  
                                    <div class="col-auto fs-75 fs-sm-875">
                                        <span class="">${this.comment_date}</span>
                                    </div>
                                    <div class="col-auto fs-75 fs-875">
                                        ${deviceInfo}
                                    </div>
                                    <div class="my-2 w-100 d-lg-none"></div>
                                    <div class="col-auto fs-875">
                                        ${likeButtons}
                                    </div>
                                    <div class="col-auto ms-auto ms-sm-4 fs-875">
                                        ${respondButton}
                                    </div>
                                    <div class="col-auto ms-2 fs-875">
                                        ${moreButton}
                                    </div>
                                       
                                    
                                </div>
                                
                            </div>
                            <div class="col-12 col-md-1 my-2 text-center comment_status ${statusTextClass}">
                                ${statusText}
                            </div>

                    </div>
                    
                    <div class="children">
                                ${childComments}
                    </div>
                
            </div>
        `;

    }


}


/**
 * 自定义  评论列表类
 */

class MyCommentList extends Array {

    constructor() {
        super();
    }

    /**
     * 批量把评论列表转换成自定义评论类
     * @param {Array} commentList
     */
    add(commentList) {

        if (isNotEmptyArray(commentList)) {

            //移除重复的评论 (因为有置顶评论)
            //只对同个请求内的评论有效, 对整个列表来说无效
            let setCommentId = new Set();
            commentList = commentList.filter(function (item, pos, self) {
                //使用set的唯一性质 来储存遍历过的id
                let isExist = setCommentId.has(item.comment_id);
                setCommentId.add(item.comment_id);
                return !isExist;
            });

            commentList.forEach((comment) => {
                let myComment = new MyComment(comment, false, false);
                this.push(myComment);
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




