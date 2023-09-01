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
        const is_admin = parseInt(MY_SITE.is_admin) ? true : false;
        //判断当前用户是否是评论人
        const is_comment_author = parseInt(MY_SITE.user_id) === parseInt(this.author.id)
        //判断当前用户是否是文章的作者
        const is_post_author = parseInt(MY_SITE.post_author_id) === parseInt(this.author.id);
        //判断当前用户是否是高级用户 并且是 文章的作者
        const is_premium_user_and_post_author = parseInt(MY_SITE.is_premium_user) && is_post_author;



        //递归输出 子评论
        let childComments = '';
        if (this.children.length) {
            childComments = this.children.reduce((previousOutput, currentElement) => {
                previousOutput += currentElement.toHTML();
                return previousOutput;
            }, '');
        }

        // 默认头像大小
        let avatarSize = 50;
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
        if (is_post_author) {
            authorDisplayName = `
                <span class="px-1  me-1 border border-danger rounded text-danger small ">UP主</span>
                <span class="text-danger">${authorDisplayName}</span>
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
            deviceInfo += `<span class="m-1"><i class="fas fa-mobile-alt"></i> ${this.comment_agent.device}</span>`;
        }

        //输出评论点赞功能
        let likeButtons = '';


        let addCommentLikesButtonClass = 'add-comment-likes';
        let deleteCommentLikesButtonClass = 'delete-comment-likes';

        //点赞按钮图标
        let addCommentLikesButtonIcon = 'fa-solid fa-thumbs-up';
        let deleteCommentLikesButtonIcon = 'fa-solid fa-thumbs-down';

        //如果用户已经点赞过 就更改图标样式  和 注销点赞按钮
        let arrayCommentLiked = getLocalStorage(LOCAL_STORAGE_KEY.commentLikes);

        if (arrayCommentLiked && arrayCommentLiked.includes(parseInt(this.comment_id))) {
            addCommentLikesButtonClass = 'text-miku disabled';
            addCommentLikesButtonIcon = 'fas fa-thumbs-up';
        }

        //如果用户已经点踩过 就更改图标样式 和 注销踩按钮
        let arrayCommentDisLiked = getLocalStorage(LOCAL_STORAGE_KEY.commentDislikes);
        if (arrayCommentDisLiked && arrayCommentDisLiked.includes(parseInt(this.comment_id))) {
            deleteCommentLikesButtonClass = 'text-miku disabled';
            deleteCommentLikesButtonIcon = 'fas fa-thumbs-down';
        }

        //如果评论作者和当前用户是同个人 禁用点赞功能, 避免点赞自己
        if (is_comment_author) {
            addCommentLikesButtonClass = 'disabled';
            deleteCommentLikesButtonClass = 'disabled';
        }

        likeButtons = `
        <div class="comment-likes">
            <a href="javascript:void(0);" class="text-muted ${addCommentLikesButtonClass}" >
                <i class="${addCommentLikesButtonIcon}"></i> 点赞
            </a>
            <span class="mx-2 comment-likes-count">
                ${this.comment_likes}
            </span>
             <a href="javascript:void(0);" class="text-muted ${deleteCommentLikesButtonClass}">
                <i class="${deleteCommentLikesButtonIcon}"></i> 踩
            </a>
        </div>
        `;


        //如果有登陆, 并且不是作者自己 才输出回复按钮
        let respondButton = '';
        if (MY_SITE.user_id > 0 && !is_comment_author) {
            respondButton = `<a class="respond-button text-muted" href="javascript:void(0);" data-respond="${this.comment_id}" data-respond-name="${this.author.display_name}">回复</a>`;
        }


        let moreButton = '';
        let deleteButton = '';
        let blackListButton = '';

        //如果是管理员 或者是 高级的文章作者 或者是 评论人自己 输出删除按钮
        if (is_admin || is_premium_user_and_post_author || is_comment_author) {

            deleteButton = `
                <li><a class="dropdown-item small delete-button" href="javascript:void(0);" data-comment-id="${this.comment_id}">删除</a></li>
                <li><hr class="dropdown-divider"></li>
            `;
        }

        //如果有登陆, 并且不是作者自己 输出 黑名单按钮
        if (MY_SITE.user_id > 0 && !is_comment_author) {

            let black_list_button_class = 'add-user-black-list';
            let black_list_button_text = '加入黑名单';
            //如果目标用户已经被拉黑
            if (MY_SITE.user_black_list.includes(String(this.author.id))) {

                black_list_button_class = 'delete-user-black-list';
                black_list_button_text = '从黑名单里移除';
            }

            blackListButton = `
             <li><a class="dropdown-item small ${black_list_button_class}" href="javascript:void(0);" data-target-user-id="${this.author.id}">${black_list_button_text}</a></li>
         `;


        }

        //如果要输出删除按钮 或者 拉黑按钮
        if (deleteButton || blackListButton) {
            //显示更多菜单
            moreButton = `
                <div class="dropdown" >
                    <a class="p-2" href="javascript:void(0);" role="button" data-bs-toggle="dropdown" title="更多操作">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </a>
                    <ul class="dropdown-menu">
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
            styleClass = 'rounded border border-danger';
            statusTextClass = 'text-danger';
            statusText = '该评论包含违禁词';
        }
        //如果触发了违禁词
        else if (parseInt(this.comment_approved) === 0) {
            styleClass = 'rounded border border-warning';
            statusTextClass = 'text-warning';
            statusText = '等待管理员审核后才会显示';
        }
        else if (this.isNew) {
            styleClass = 'rounded border border-success';
            statusText = '已发表';
        }


        return `

            <div class="my-2 comment-item comment-item-${this.comment_id}" data-comment_id="${this.comment_id}">
            
                    <div class="row comment-body   border-bottom ${this.depth ? 'ms-2 ms-md-5 ' : ''} ${styleClass}">
                    
                            <div class="col-auto col-md-1 my-2 avatar-container">
                                 ${authorAvatar}
                            </div>
                            <div class="col col-md-10 my-2">
                                <div class="user-meta">
                                    <a class="m-1 d-block d-sm-inline" href="${this.author.user_href} " title="查看用户主空间" target="_blank">${authorDisplayName}</a>
                                    <span class="badge bg-miku rounded-1 m-1">${this.author.user_level}</span>
                                    ${authorBadges}
                                </div>
                                <div class="comment-content my-3" style="white-space: pre-line;" >${this.comment_content}</div>
                                <div class="comment-meta small  text-muted row g-2">
                                    <div class="col-auto">
                                        <span class="">${this.comment_date}</span>
                                    </div>
                                    <div class="col-auto">
                                        ${deviceInfo}
                                    </div>
                                    <div class="m-0 w-100 d-lg-none"></div>
                                    <div class="col-auto">
                                        ${likeButtons}
                                    </div>
                                    <div class="col-auto ms-4">
                                        ${respondButton}
                                     </div>
                                     <div class="col-auto ms-auto ms-md-4">
                                        ${moreButton}
                                     </div>
                                </div>
                                
                            </div>
                            <div class="col-12 col-md-1 my-2 text-center ${statusTextClass}">
                                ${statusText}
                            </div>

                    </div>
                    
                    <div class="children my-2">
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




