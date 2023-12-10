/// <reference path="../common/constant.js" />

/**
 * 自定义 文章类
 */

class MyPostSlim {


    constructor(post) {

        this.id = post.id;
        this.post_title = post.post_title;
        this.post_href = post.post_href;
        this.post_image = post.post_image;
        this.post_views = post.post_views;
        this.post_likes = post.post_likes;
        this.post_unlike = post.post_unlike;
        this.post_comments = post.post_comments;

        this.post_favorites = post.post_favorites;
        this.post_shares = post.post_shares;
        this.post_fail_times = post.post_fail_times;

        this.post_status = post.post_status;

        this.post_cat_id = post.post_cat_id;
        this.post_cat_name = post.post_cat_name;
        this.post_cat_href = post.post_cat_href;
        this.post_date = post.post_date;
        this.post_modified_date = post.post_modified_date;

        //如果有作者信息 才创建对应的js实例
        if (post.post_author) {
            this.post_author = new MyAuthor(post.post_author);
        }

        //如果备用图床域名 为开启状态
        if (is_enable_backup_image_domain()) {
            //启动备用图片域名
            this.post_image = replace_image_src_to_backup_image_domain(this.post_image);
        }

         //修正链接里的域名
        this.post_href = replace_link_href_to_current_domain(post.post_href);
    }

    /**
     * 如果作者在用户的黑名单里, 输出遮罩类名 用来遮挡文章
     * @returns {string}
     */
    setBlackPostMaskClass() {

        let class_name = '';

        //如果有作者信息
        if (this.post_author) {

            //如果作者在用户的黑名单里
            if (MY_SITE.user_black_list.includes(parseInt(this.post_author.id))) {
                //添加遮罩类名
                class_name = 'black-user-post-mask';
            }
        }

        return class_name;

    }

    //输出html
    toHTML() {

        let output = '';
        let author_avatar = '';
        let author_name = '';
        let post_container_class = this.setBlackPostMaskClass();

        //如果有作者信息
        if (this.post_author) {

            author_avatar += `

                <a href="${this.post_author.user_href}" title="查看UP主空间" target="_blank">
                    <img class="avatar rounded-circle" src="${this.post_author.user_image}" width="40" height="40" alt="用户头像">
                </a>`;

            author_name += `

                <a class="card-link small text-dark-2" title="查看UP主空间" href="${this.post_author.user_href}" target="_blank">
                    ${this.post_author.display_name}
                </a>
            `;

        }


        output = `

            <div class="col post_element">
                <div class="card border-0 my-1 ${post_container_class}">
                    <div class="card-img-container position-relative">
                    
                        <div class="position-absolute end-0 bottom-0 me-1 mb-1 text-light fs-75">
                            <div class="d-none d-sm-inline-block bg-transparent-half rounded p-1">
                                <i class="fa-solid fa-thumbs-up"></i> ${this.post_likes}
                            </div>
                            <div class="d-none d-sm-inline-block bg-transparent-half rounded p-1">
                                <i class="fa-solid fa-comments"></i> ${this.post_comments}
                            </div>
                            <div class="d-inline-block bg-transparent-half rounded p-1">
                                <i class="fa-solid fa-eye"></i> ${this.post_views}
                            </div>
					    </div>
                        
                        <div>
                            <a class="" href="${this.post_href}" title="${this.post_title}" target="_blank">
                                 <img class="card-img-top bg-light-2" src="${this.post_image}" alt="${this.post_title}" />
                              </a>
                        </div>
 
                    </div>
                    <div class="row my-2 align-items-center">

                        <div class="col-12 mb-2">
                            <div class="post-title text-3-rows">
                                <a class="fs-75 fs-sm-875" href="${this.post_href}" title="${this.post_title}" target="_blank">
                                    ${this.post_title}
                                </a>
                            </div>
                            
                        </div>
                        <div class="col-auto d-none d-md-block">
                            ${author_avatar}
                        </div>
                        <div class="col">

                            <div class="text-1-rows">
                            ${author_name}
                            </div>
                            <div class="fs-75 d-none d-md-block text-dark-2">
                                ${this.post_modified_date}
                            </div>

                        </div>
        
                    </div>
                </div>
            </div>
        
        `;

        return output;

    }

}

/**
 * 收藏列表文章
 */
class MyFavoritePost extends MyPostSlim {

    toHTML() {

        let output = '';
        let author_avatar = '';
        let author_name = '';
        let post_container_class = this.setBlackPostMaskClass();

        //如果有作者信息
        if (this.post_author) {

            author_avatar += `

                <a href="${this.post_author.user_href}" title="查看UP主空间" target="_blank">
                    <img class="avatar rounded-circle" src="${this.post_author.user_image}" width="40" height="40" alt="用户头像">
                </a>`;

            author_name += `

                <a class="card-link small text-dark-2" title="查看UP主空间" href="${this.post_author.user_href}" target="_blank">
                    ${this.post_author.display_name}
                </a>
            `;

        }


        output = `

            <div class="col post_element">
                <div class="card border-0 my-1 ${post_container_class}">
                    <div class="card-img-container position-relative">

                        <div class="position-absolute end-0 top-0 me-1 mt-1 text-light fs-75">
                            <div class="d-none d-sm-inline-block bg-transparent-half rounded p-1">
                                 <span>${this.post_cat_name}</span>
                            </div>
                        </div>
                    
                    
                        <div class="position-absolute end-0 bottom-0 me-1 mb-1 text-light fs-75">
                            <div class="d-none d-sm-inline-block bg-transparent-half rounded p-1">
                                <i class="fa-solid fa-thumbs-up"></i> ${this.post_likes}
                            </div>
                            <div class="d-none d-sm-inline-block bg-transparent-half rounded p-1">
                                <i class="fa-solid fa-comments"></i> ${this.post_comments}
                            </div>
                            <div class="d-inline-block bg-transparent-half rounded p-1">
                                <i class="fa-solid fa-eye"></i> ${this.post_views}
                            </div>
					    </div>
                        
                        <div>
                            <a class="" href="${this.post_href}" title="${this.post_title}" target="_blank">
                                 <img class="card-img-top bg-light-2" src="${this.post_image}" alt="${this.post_title}" />
                              </a>
                        </div>
 
                    </div>
                    <div class="row my-2 align-items-center">

                        <div class="col-12 mb-2">
                            <div class="post-title text-3-rows">
                                <a class="fs-75 fs-sm-875" href="${this.post_href}" title="${this.post_title}" target="_blank">
                                    ${this.post_title}
                                </a>
                            </div>
                            
                        </div>
                        <div class="col-auto d-none d-md-block">
                            ${author_avatar}
                        </div>
                        <div class="col">

                            <div class="text-1-rows">
                            ${author_name}
                            </div>
                            <div class="fs-75 d-none d-md-block text-dark-2">
                                ${this.post_modified_date}
                            </div>

                        </div>
                        <div class=""></div>
                        <div class="col-auto mt-2 fw-bold small">
                            状态: <span class="fw-bold ${POST_STATUS.get_text_color_class(this.post_status)}">${POST_STATUS.get_description(this.post_status)}</span>
                        </div>
                        <div class="col-auto mt-2 ms-auto">
                            <button class="btn btn-sm btn-light-2 delete_favorite" data-post_id="${this.id}">
                                取消收藏
                            </button>
                        </div>
        
                    </div>
                </div>
            </div>
        
        `;

        return output;


    }

}


/**
 * 历史记录列表文章
 */
class MyHistoryPost extends MyPostSlim {

    toHTML() {

        let output = '';
        let author_avatar = '';
        let author_name = '';
        let post_container_class = this.setBlackPostMaskClass();

        //如果有作者信息
        if (this.post_author) {

            author_avatar += `

                <a href="${this.post_author.user_href}" title="查看UP主空间" target="_blank">
                    <img class="avatar rounded-circle" src="${this.post_author.user_image}" width="40" height="40" alt="用户头像">
                </a>`;

            author_name += `

                <a class="card-link small text-dark-2" title="查看UP主空间" href="${this.post_author.user_href}" target="_blank">
                    ${this.post_author.display_name}
                </a>
            `;

        }


        output = `

            <div class="col post_element">
                <div class="card border-0 my-1 ${post_container_class}">
                    <div class="card-img-container position-relative">

                        <div class="position-absolute end-0 top-0 me-1 mt-1 text-light fs-75">
                            <div class="d-none d-sm-inline-block bg-transparent-half rounded p-1">
                                 <span>${this.post_cat_name}</span>
                            </div>
                        </div>
                    
                    
                        <div class="position-absolute end-0 bottom-0 me-1 mb-1 text-light fs-75">
                            <div class="d-none d-sm-inline-block bg-transparent-half rounded p-1">
                                <i class="fa-solid fa-thumbs-up"></i> ${this.post_likes}
                            </div>
                            <div class="d-none d-sm-inline-block bg-transparent-half rounded p-1">
                                <i class="fa-solid fa-comments"></i> ${this.post_comments}
                            </div>
                            <div class="d-inline-block bg-transparent-half rounded p-1">
                                <i class="fa-solid fa-eye"></i> ${this.post_views}
                            </div>
					    </div>
                        
                        <div>
                            <a class="" href="${this.post_href}" title="${this.post_title}" target="_blank">
                                 <img class="card-img-top bg-light-2" src="${this.post_image}" alt="${this.post_title}" />
                              </a>
                        </div>
 
                    </div>
                    <div class="row my-2 align-items-center">

                        <div class="col-12 mb-2">
                            <div class="post-title text-3-rows">
                                <a class="fs-75 fs-sm-875" href="${this.post_href}" title="${this.post_title}" target="_blank">
                                    ${this.post_title}
                                </a>
                            </div>
                            
                        </div>
                        <div class="col-auto d-none d-md-block">
                            ${author_avatar}
                        </div>
                        <div class="col">

                            <div class="text-1-rows">
                            ${author_name}
                            </div>
                            <div class="fs-75 d-none d-md-block text-dark-2">
                                ${this.post_modified_date}
                            </div>

                        </div>
                        <div class=""></div>
                        <div class="col-auto mt-2 fw-bold small">
                            状态: <span class="fw-bold ${POST_STATUS.get_text_color_class(this.post_status)}">${POST_STATUS.get_description(this.post_status)}</span>
                        </div>
                      
        
                    </div>
                </div>
            </div>
        
        `;

        return output;


    }

}

/**
 * 投稿管理列表文章
 */
class MyManagePost extends MyPostSlim {

    toHTML() {

        //如果文章已经是草稿了, 就隐藏草稿按钮
        const draft_button_class = this.post_status === POST_STATUS.draft ? 'd-none' : '';

        return `

            <div class="col-12 my-2 post_element manage_post_element">
                <div class="row pb-2 border-bottom">
                    
                        <div class="col-12 col-lg-auto text-center text-lg-start">
                            <a href="${this.post_href}" target="_blank">
                                <img class="img-fluid bg-light-2" src="${this.post_image}" alt="${this.post_title}" />
                            </a>
                        </div>
                        <div class="col my-2 my-lg-0">
                            <div class="row g-2 align-items-center">
                                <div class="col-12">
                                    <a class="fw-bold" title="${this.post_title}" href="${this.post_href}" target="_blank">
                                        ${this.post_title}
                                    </a>
                                </div>
                                <div class="mt-2 border-bottom"></div>
                                <div class="col-auto">
                                    <i class="fa-solid fa-layer-group me-2"></i>
                                </div>
                                <div class="col-auto">
                                    <span class="small">分类: <span class="fw-bold">${this.post_cat_name} </span></span>
                                </div>
                                <div class="col-auto">
                                    <span class="small">状态: <span class="fw-bold ${POST_STATUS.get_text_color_class(this.post_status)}">${POST_STATUS.get_description(this.post_status)}</span></span>
                                </div>
                                <div class="m-0"></div>
                                <div class="col-auto">
                                    <i class="fa-solid fa-clock me-2"></i>
                                </div>
                                <div class="col-auto">
                                    <span class="small">发布时间: ${this.post_date}</span>
                                </div>
                                <div class="col-auto">
                                    <span class="small">最后修改: ${this.post_modified_date}</span>
                                </div>
                                <div class="m-0"></div>
                                <div class="col-auto">
                                    <i class="fa-solid fa-square-poll-vertical me-2"></i>
                                </div>
                                <div class="col-auto">
                                    <span class="small">${POST_FEEDBACK_RANK.get_rank(this.post_likes, this.post_unlike)}</span>
                                </div>
                                <div class="col-auto">
                                    <span class="small">好评: ${this.post_likes}</span>
                                </div>
                                <div class="col-auto">
                                    <span class="small">差评: ${this.post_unlike}</span>
                                </div>
                                <div class="col-auto">
                                    <span class="small">点击: ${this.post_views}</span>
                                </div>
                                <div class="col-auto">
                                    <span class="small">评论: ${this.post_comments}</span>
                                </div>
                                <div class="col-auto">
                                    <span class="small">收藏: ${this.post_favorites}</span>
                                </div>
                                <div class="col-auto">
                                    <span class="small">分享: ${this.post_shares}</span>
                                </div>
                                <div class="col-auto">
                                    <span class="small">下载失效反馈: ${this.post_fail_times}</span>
                                </div>
                               
                            </div>
                        </div>
                      
                        <div class="col-12 col-lg-3 text-end">

                            <a class="btn btn-sm btn-miku px-4" href="${MY_SITE.home}/edit?pid=${this.id}" target="_blank">编辑</a>
                            <button class="btn btn-sm btn-light-2 px-4 draft_post ${draft_button_class}" type="button" data-post-id="${this.id}">
                                转为草稿
                            </button>
                            <button class="btn btn-sm btn-danger px-4 delete_post" type="button" data-post-id="${this.id}">
                                删除
                            </button>
                        </div>
                        
                    </div>
                </div>
        
        `;


    }


}


/**
 * 自定义 文章列表类
 */

class MyPostSlimList extends Array {

    constructor(post_template) {
        super();
        this.post_template = post_template;
    }

    add(posts) {

        if (isNotEmptyArray(posts)) {

            posts.forEach((post) => {

                let new_post;

                switch (this.post_template) {
                    case POST_TEMPLATE.default:
                        new_post = new MyPostSlim(post);
                        break;
                    case POST_TEMPLATE.favoritePost:
                        new_post = new MyFavoritePost(post);
                        break;
                    case POST_TEMPLATE.historyPost:
                        new_post = new MyHistoryPost(post);
                        break;
                    case POST_TEMPLATE.managePost:
                        new_post = new MyManagePost(post);
                        break;

                }

                this.push(new_post);
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



