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
        this.post_comments = post.post_comments;

        this.post_favorites = post.post_favorites;
        this.post_shares = post.post_shares;

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

            <div class="col">
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
                                ${this.post_date}
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

        return `

          <div class="row my-2">
            
                <div class="col-12 col-md-2">
                    <a href="${this.post_href}" target="_blank">
                        <img class="img-fluid" src="${this.post_image}" alt="${this.post_title}" />
                    </a>
                </div>
                <div class="col col-md-8 mt-3 mt-md-0">
                     <div >
                        <a class="" title="${this.post_title}" href="${this.post_href}" target="_blank">
                            ${this.post_title}
                        </a>
                    </div>
                    <div class="mt-2">
                        <a class="small" title="查看UP主空间" href="${this.post_author.user_href}" target="_blank">
                            作者:  ${this.post_author.display_name}
                        </a>
                    </div>
                    <div class="mt-2 d-none d-md-block">
                        <span class="small me-2">发布时间 ${this.post_date}</span>
                        <span class="small me-2">最后修改 ${this.post_modified_date}</span>
                    </div>
                </div>
                <div class="col-auto col-md-2 text-center  mt-3 mt-md-0">
                    <button class="btn btn-secondary delete-favorite" type="button" data-post-id="${this.id}">
                        <span>取消收藏</span>
                        <span class="spinner-border spinner-border-sm" style="display: none"></span>
                    </button>
                </div>
            

            </div>
        
        `;


    }

}


/**
 * 历史记录列表文章
 */
class MyHistoryPost extends MyPostSlim {

    toHTML() {

        return `

          <div class="row my-2">
            
                <div class="col-12 col-md-2">
                    <a href="${this.post_href}" target="_blank">
                        <img class="img-fluid" src="${this.post_image}" alt="${this.post_title}" />
                    </a>
                </div>
                <div class="col col-md-8 mt-3 mt-md-0">
                     <div >
                        <a class="" title="${this.post_title}" href="${this.post_href}" target="_blank">
                            ${this.post_title}
                        </a>
                    </div>
                    <div class="mt-2">
                        <a class="small" title="查看UP主空间" href="${this.post_author.user_href}" target="_blank">
                            作者:  ${this.post_author.display_name}
                        </a>
                    </div>
                    <div class="mt-2 d-none d-md-block">
                        <span class="small me-2">发布时间 ${this.post_date}</span>
                        <span class="small me-2">最后修改 ${this.post_modified_date}</span>
                    </div>
                </div>
                <div class="col-auto col-md-2 text-center  mt-3 mt-md-0">
                </div>
            

            </div>
        
        `;


    }

}

/**
 * 投稿管理列表文章
 */
class MyManagePost extends MyPostSlim {

    toHTML() {


        let postStatusText = '';
        let postStatusColor = '';
        switch (this.post_status) {

            case 'publish':
                postStatusText = '已发布';
                postStatusColor = 'text-success';
                break;
            case 'pending':
                postStatusText = '等待审核';
                postStatusColor = 'text-danger';
                break;
            case 'draft':
                postStatusText = '草稿';
                break;
        }


        return `

          <div class="row my-3">
            
                <div class="col-12 col-md-2">
                    <a href="${this.post_href}" target="_blank">
                        <img class="img-fluid" src="${this.post_image}" alt="${this.post_title}" />
                    </a>
                </div>
                <div class="col col-md-6 my-2 my-md-0">
                     <div >
                        <a class="" title="${this.post_title}" href="${this.post_href}" target="_blank">
                            ${this.post_title}
                        </a>
                    </div>
                    <div class="mt-2">
                        <span class="small">分类 <b>${this.post_cat_name}</b></span>
                    </div>
                     <div class="mt-2">
                        <span class="small my-1 me-2">发布时间 ${this.post_date}</span>
                        <span class="small my-1 me-2">最后修改 ${this.post_modified_date}</span>
                    </div>
                    <div>
                        <span class="small my-1 me-2">点击 ${this.post_views}</span>
                        <span class="small my-1 me-2">评论 ${this.post_comments}</span>
                        <span class="small my-1 me-2">点赞 ${this.post_likes}</span>
                        <span class="small my-1 me-2">收藏 ${this.post_favorites}</span>
                        <span class="small my-1 me-2">分享 ${this.post_shares}</span>
                    </div>
                    
                </div>
                <div class="col-auto col-md-2 text-center ${postStatusColor} large fw-bold my-2 my-md-0">
                    ${postStatusText}
                </div>
                <div class="col-12 col-md-2 text-center">
                    <a class="btn btn-secondary my-1 mx-2 px-5 px-md-4 " href="${MY_SITE.home}/edit?pid=${this.id}" target="_blank">编辑</a>
                    <button class="btn btn-danger my-1 mx-2 px-5 px-md-4 delete_post" type="button" data-post-id="${this.id}">
                        <span>删除</span>
                        <span class="spinner-border spinner-border-sm" style="display: none"></span>
                    </button>
                </div>
                
                <div class="col-12">
                    <hr/>
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



