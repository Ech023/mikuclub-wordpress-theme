<?php

namespace mikuclub;
use mikuclub\User_Capability;

//检查是否是黑名单用户
User_Capability::prevent_blocked_user();


$head_output = print_head_component();
$body_start_output = print_body_start_component();
$body_header_output = print_body_header_component();

echo <<<HTML

    {$head_output}
    {$body_start_output}
    {$body_header_output}

    <section class=" mh-75vh mx-auto px-3 px-sm-5">
        <div class="content">

HTML;
