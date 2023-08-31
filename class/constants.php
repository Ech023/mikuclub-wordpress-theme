<?php

namespace mikuclub;

define('ANIME_CATEGORY_MAIN_ID', 942);
//成人内容主分类
define('ADULT_CATEGORY_MAIN_ID', 1120);
//成人内容分类ID
define('ADULT_CATEGORY_IDS', [
	1120,
	788,
	3055,
	211,
	1741,
	1121,
	1192,
	5998,
	6678,
	6713,
	7476,
]);
//不同步微博的分类ID
//音乐区, 动漫区, 软件区,  游戏区  和 图片区 和 其他区, 小说区
define('NOT_WEIBO_CATEGORY_IDS', [
	1120, //魔法区,
	9, //音乐区,
	942, //动漫区
	465, //软件区
	182, //游戏区
	1, // 其他区
	294, //小说区
	8621, //学习区
	9305, //视频区
]);


//文章列表显示的时间 格式, 年年-月月-日日
define('MY_DATE_FORMAT_SHORT', 'y-m-d');
define('MY_DATE_FORMAT', 'y-m-d H:i:s');


//缓存文件夹
define('CACHE_DIRECTORY', WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'cache_file' . DIRECTORY_SEPARATOR);
define('CACHE_GROUP_USER', 'user' . DIRECTORY_SEPARATOR);
define('CACHE_GROUP_POST', 'post' . DIRECTORY_SEPARATOR);
define('CACHE_GROUP_POSTS', 'posts' . DIRECTORY_SEPARATOR);
define('CACHE_GROUP_COMMENTS', 'comments' . DIRECTORY_SEPARATOR);
define('CACHE_GROUP_COMPONENTS', 'components' . DIRECTORY_SEPARATOR);

//缓存键名
define('USER_POST_COUNT', 'user_post_count');
define('USER_POST_TOTAL_VIEWS', 'user_post_total_views');
define('USER_POST_TOTAL_COMMENTS', 'user_post_total_comments');
define('USER_POST_TOTAL_LIKES', 'user_post_total_likes');
define('USER_COMMENT_COUNT', 'user_comment_count');
define('USER_LIKE_COUNT', 'my_rating_count');

define('STICKY_POSTS', 'sticky_posts');
define('POST_TAGS', 'post_tags');
define('POST_CATEGORIES', 'post_categories');
define('POST_CONTENT_PART_1', 'post_content_part_1');
define('POST_CONTENT_PART_2', 'post_content_part_2');


//文章键名
define('POST_VIEWS', 'views');
define('POST_LIKES', 'count_like');
define('POST_COMMENTS', 'count_comments');
define('POST_FAVORITES', 'count_favorite');
define('POST_FAIL_TIMES', 'fail_time');
define('POST_SHARES', 'count_sharing');

define('POST_DOWN', 'down');
define('POST_DOWN2', 'down2');
define('POST_PASSWORD', 'password');
define('POST_PASSWORD2', 'password2');

define('POST_PREVIEWS', 'previews');

define('POST_BILIBILI_VIDEO_INFO', 'bilibili_info');

define('POST_SHARE_TO_WEIBO', 'waiting_for_weibo');
define('POST_CATS', 'cats');
define('POST_MAIN_CAT', 'main_cat');
define('POST_SUB_CAT', 'sub_cat');

//文章状态名
define('POST_STATUS_PUBLISH', 'publish');
define('POST_STATUS_PENDING', 'pending');
define('POST_STATUS_DRAFT', 'draft');


//封面微缩图键名
define('POST_THUMBNAIL_ID', '_thumbnail_id');
define('POST_THUMBNAIL_SRC', '_thumbnail_src');

//图片地址数组键名
define('POST_IMAGES_THUMBNAIL_SRC', 'images_thumbnail_src');
define('POST_IMAGES_SRC', 'images_src');
define('POST_IMAGES_FULL_SRC', 'images_full_src');


//文章列表键名
define('POST_LIST', 'post_list');
define('HOT_POST_LIST', 'hot_post_list');
define('RELATED_POST_LIST', 'related_post_list');

//评论数据相关键名
define('COMMENT_REPLIES_COUNT', 'comment_replies_count');
define('COMMENT_PARENT_USER_ID', 'parent_user_id');
define('COMMENT_PARENT_USER_READ', 'parent_user_read');

define('COMMENT_LIKES', 'comment_likes');

//论坛回复相关键名
define('BBPRESS_TOPIC_AUTHOR_READ', '_bbp_topic_author_read');
define('BBPRESS_REPLY_AUTHOR_READ', '_bbp_reply_author_read');

//用户相关键名
define('MY_USER_FAVORITE_POST_LIST', 'favorite_post');
define('MY_USER_AVATAR', 'mm_user_avatar');
define('MY_USER_FOLLOWED', 'followed_users');
define('MY_USER_FANS_COUNT', 'users_fans_count');
define('MY_USER_BLACK_LIST', 'user_black_list');
define('MY_USER_BLACKED_COUNT', 'user_blacked_count');

define('ACTION_UPDATE_AVATAR', 'action_update_avatar');


//邮件相关键名
define('EMAIL_REJECT_POST', 'email_reject_post');

//分类相关键名
define('MAIN_CATEGORY_LIST', 'main_category_list');

//附件图片类型键名
define('ATTACHMENT_WP_USER_AVATAR', '_wp_attachment_wp_user_avatar');


//缓存过期时间
define('EXPIRED_1_MINUTE', 60); //60
define('EXPIRED_5_MINUTES', 60 * 5);
define('EXPIRED_10_MINUTES', 60 * 10);
define('EXPIRED_15_MINUTES', 60 * 15);
define('EXPIRED_30_MINUTES', 60 * 30);
define('EXPIRED_1_HOUR', 3600); //60 * 60
define('EXPIRED_2_HOURS', 3600 * 2);
define('EXPIRED_4_HOURS', 3600 * 4);
define('EXPIRED_6_HOURS', 3600 * 6);
define('EXPIRED_1_DAY', 86400); //60 * 60 * 24
define('EXPIRED_3_DAYS', 86400 * 3);
define('EXPIRED_7_DAYS', 86400 * 7);
define('EXPIRED_10_DAYS', 86400 * 10);


//自定义 查询参数键名
define('CUSTOM_ORDERBY', 'custom_orderby');
define('CUSTOM_ORDER_DATA_RANGE', 'custom_order_data_range');
define('AUTHOR_INTERNAL_SEARCH', 'author_internal_search');

//自定义消息类型
define('CUSTOM_PRIVATE_MESSAGE', 'private_message');
define('CUSTOM_COMMENT_REPLY', 'comment_reply');
define('CUSTOM_FORUM_REPLY', 'bbpress_reply');
//自定义消息计数
define('CUSTOM_PRIVATE_MESSAGE_COUNT', 'private_message_count');
define('CUSTOM_COMMENT_REPLY_COUNT', 'comment_replay_count');
define('CUSTOM_FORUM_REPLY_COUNT', 'bbpress_reply_count');

define('BLOCKED_USER_CHECK', 'blocked_user_check');


//wpforo论坛 主题预览原图的数组地址
define('WPFORO_TOPIC_ATTACH_SRC', 'wpforo_topic_attach_src');
//wpforo论坛 主题预览缩微图图的数组地址
define('WPFORO_TOPIC_ATTACH_THUMBNAIL_SRC', 'wpforo_topic_attach_thumbnail_src');

//网站支持的域名
define('ARRAY_SITE_DOMAIN', [
	'www.mikuclub.cc',
	'www.mikuclub.online',
	'www.mikuclub.win',
	'www.mikuclub.eu',
	'www.mikuclub.uk',
]);

//网站主域名
define('SITE_DOMAIN_MAIN', 'www.mikuclub.cc');

//CDN域名
define('CDN_MIKUCLUB_FUN', 'cdn.mikuclub.fun');
define('FILE1_MIKUCLUB_FUN', 'file1.mikuclub.fun');
define('FILE2_MIKUCLUB_FUN', 'file2.mikuclub.fun');
define('FILE3_MIKUCLUB_FUN', 'file3.mikuclub.fun');
define('FILE4_MIKUCLUB_FUN', 'file4.mikuclub.fun');
define('FILE5_MIKUCLUB_FUN', 'file5.mikuclub.fun');
define('FILE6_MIKUCLUB_FUN', 'file6.mikuclub.fun');

//静态文件自动CDN域名
define('ARRAY_FILE_DOMAIN', [
	FILE1_MIKUCLUB_FUN,
	FILE2_MIKUCLUB_FUN,
	FILE3_MIKUCLUB_FUN,
	FILE4_MIKUCLUB_FUN,
	FILE5_MIKUCLUB_FUN,
	FILE6_MIKUCLUB_FUN,
]);
