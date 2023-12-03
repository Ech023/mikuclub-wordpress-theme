
/**
 * 自定义文章用户类
 */

class MyAuthor {

    constructor(user) {

        this.id = user.id;
        this.user_login = user.user_login;
        this.display_name = user.display_name;
        this.user_href = user.user_href;
        this.user_image = user.user_image;

        //如果备用图床域名 为开启状态
        if (is_enable_backup_image_domain()) {
            //启动备用图片域名
            this.user_image = replace_image_src_to_backup_image_domain(this.user_image);
        }

        //修正链接里的域名
        this.user_href = replace_link_href_to_current_domain(this.user_href);
    }


}

class MyCommentAuthor extends MyAuthor {


    constructor(user) {
        super(user);

        this.user_level = user.user_level;
        this.user_badges = user.user_badges;

    }

}

