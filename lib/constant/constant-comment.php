<?php

namespace mikuclub;

/**
 * 评论的元数据 键名
 */
class Comment_Meta
{
    //评论收到的回复数
    const COMMENT_REPLIES_COUNT = 'comment_replies_count';
    //主评论的发送者
    const COMMENT_PARENT_USER_ID = 'parent_user_id';
    //判定用户是否已读 0 = 未读, 1 = 已读
    const COMMENT_PARENT_USER_READ = 'parent_user_read';
    //评论好评数
    const COMMENT_LIKES = 'comment_likes';

}
