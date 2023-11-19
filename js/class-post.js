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

                <a class="card-link small" title="查看UP主空间" href="${this.post_author.user_href}" target="_blank">
                    ${this.post_author.display_name}
                </a>
            `;

        }


        output = `

         <div class="col card border-0 my-1 ${post_container_class}">
                    <div class="card-img-container position-relative">
                        <div class="position-absolute end-0 top-0 me-1 mt-1">
                            
                        </div>
                        
                        <div class="position-absolute end-0 bottom-0 me-1 mb-1">
                            <div class="right-badge bg-transparent-half text-light rounded small p-1">
                                <i class="fa-solid fa-eye"></i> ${this.post_views}
                            </div>
                        </div>
                  
                        <div>
                            <a class="" href="${this.post_href}" title="${this.post_title}" target="_blank">
                                      <img class="card-img-top" src="${this.post_image}" alt="${this.post_title}" />
                              </a>
                        </div>
          
                                                  
                    </div>
                    <div class="card-body  my-2 py-2 row g-0">
        
                         <div class="col-3 d-none d-sm-block d-lg-none d-xl-block">
                            ${author_avatar}
                        </div>
                        <div class="col-12 col-sm-9 col-lg-12 col-xl-9">
                     
                            <h6 class="post-title text-1-rows text-2-rows-sm small medium-bold-sm">
                                <a class="" href="${this.post_href}" title="${this.post_title}" target="_blank">
                                    ${this.post_title}
                                </a>
                            </h6>
                            
                            <div class="my-2">
                                ${author_name}
                            </div>
                            
                            
                            <div class="small d-none d-sm-block">
                                    <span class="me-1"><i class="fa-solid fa-clock"></i> ${this.post_date} </span>
                                    <span class="me-1"><i class="fa-solid fa-comments"></i> ${this.post_comments}</span>
                                    <span class="me-1 d-none"><i class="fa-solid fa-star"></i> ${this.post_likes}</span>
                                    <span class="d-none"><i class="fa-solid fa-heart"></i> ${this.post_favorites}</span>
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
                        <span class="small me-2">更新时间 ${this.post_modified_date}</span>
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
                        <span class="small me-2">更新时间 ${this.post_modified_date}</span>
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
                        <span class="small my-1 me-2">更新时间 ${this.post_modified_date}</span>
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
                    <button class="btn btn-danger my-1 mx-2 px-5 px-md-4 delete-post" type="button" data-post-id="${this.id}">
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

    constructor(postType) {
        super();
        this.postType = postType;
    }

    add(posts) {

        if (isNotEmptyArray(posts)) {

            posts.forEach((post) => {

                let myPostSlim;

                switch (this.postType) {
                    case POST_TYPE.post:
                        myPostSlim = new MyPostSlim(post);
                        break;
                    case POST_TYPE.favoritePost:
                        myPostSlim = new MyFavoritePost(post);
                        break;
                    case POST_TYPE.historyPost:
                        myPostSlim = new MyHistoryPost(post);
                        break;
                    case POST_TYPE.managePost:
                        myPostSlim = new MyManagePost(post);
                        break;

                }

                this.push(myPostSlim);
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



