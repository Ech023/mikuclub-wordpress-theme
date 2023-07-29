
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

    }


}

class MyCommentAuthor extends  MyAuthor{


    constructor(user) {
        super(user);

        this.user_level = user.user_level;
        this.user_badges = user.user_badges;

    }

}

