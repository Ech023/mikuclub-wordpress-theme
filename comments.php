<?php

namespace mikuclub;


use function mikuclub\get_post_comments;


$post_id = get_the_ID();

$user_id = get_current_user_id();
$post_comments = get_post_comments($post_id);
$comment_input_box = print_comment_input_box($post_id);

$comment_adsense = print_single_comment_adsense();


$output = <<<HTML

    <div class="comments-part my-2" id="comments-part">

        <h5 class="my-2 comments-part-title">{$post_comments} 条评论</h5>

        {$comment_input_box}

        {$comment_adsense}

        <div class="comment-list" data-post-id="{$post_id}" data-offset="0">
        </div>

        <div class="comment-list-end">
        </div>

    </div>

HTML;

echo $output;
