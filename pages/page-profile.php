<?php

/*
template name: 用户个人信息页
*/

//如果未登陆 重定向回首页

use mikuclub\My_User;

use function mikuclub\breadcrumbs_component;
use function mikuclub\get_my_user_avatar;
use function mikuclub\get_user_black_list;
use function mikuclub\get_user_level;
use function mikuclub\get_user_points;
use function mikuclub\print_user_avatar;
use function mikuclub\redirect_for_not_logged;

redirect_for_not_logged();


get_header();


$user = wp_get_current_user();

?>

<div class="content page-profile">

    <header class="page-header">
        <h4 class="my-4">
            <?php echo breadcrumbs_component(); ?>
        </h4>

        <hr />

    </header>


    <div class="page-content my-3 ">


        <form class="row user-profile">

            <div class="col-12 col-md-6">

                <div class="mb-3">
                    <label class="form-label" for="user_login">用户ID </label>
                    <input type="text" class="form-control" id="user_login" value="<?php echo $user->user_login; ?>" readonly />
                    <small class="form-text text-muted">不可修改</small>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="user_email">邮箱 <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="user_email" name="email" value="<?php echo $user->user_email; ?>" required />
                    <small class="form-text text-muted">请确保填写了有效的邮箱地址, 将来可以用来找回密码</small>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="user_name">昵称 <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="user_name" name="user_name" value="<?php echo $user->display_name; ?>" autocomplete="off" maxlength="20" required />
                    <small class="form-text text-muted">公开显示的昵称 长度限制20字</small>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="description">个性签名</label>
                    <input type="text" class="form-control" id="description" name="description" value="<?php echo $user->user_description; ?>" autocomplete="off" maxlength="100" />
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">新密码</label>
                    <input type="password" class="form-control" id="password" name="password" autocomplete="new-password" />
                    <small class="form-text text-muted">如果不需要修改密码, 留空即可, 本密码不影响社交账号登陆</small>
                </div>





            </div>

            <div class="col-12 col-md-6">

                <div class="mb-3 row ">
                    <div class="col">
                        <label class="form-label" for="password">积分</label>
                        <div>
                            <?php echo get_user_points($user->ID); ?>
                        </div>
                    </div>
                    <div class="col">
                        <label class="form-label" for="password">等级</label>
                        <div>
                            <?php echo get_user_level($user->ID); ?>
                        </div>
                    </div>

                </div>

                <div class="mb-3 border-top"></div>

                <div class="mb-3">
                    <div class="mb-2">我的头像</div>
                    <div class="text-center">
                        <img src="<?php echo get_my_user_avatar($user->ID); ?>" class="img-fluid avatar rounded-circle" width="93" height="93" />
                    </div>

                </div>

                <div class="mb-3">
                    <div>

                        <label class="form-label" for="user_avatar">更改新的头像</label>
                        <small class="form-text text-muted">图片大小需要100 * 100像素以上, 支持JPG、PNG等格式, 图片需小于3M</small>
                        <input type="file" class="form-control" id="user_avatar" name="user_avatar" accept="image/png,image/jpg,image/jpeg,image/bmp,image/webp">
                    </div>


                </div>

                <div class="mb-3">
                    <div class="mb-2">绑定社交账号</div>
                    <?php echo open_social_bind_html(); ?>
                    <small class="form-text text-muted my-2">点击图标可以进行绑定/解绑操作</small>

                </div>


            </div>

            <div class="w-100 my-4 border-top"></div>



            <div class="col-12">
                <button type="submit" class="btn btn-miku w-100">
                    <span>保存</span>
                    <span class="spinner-border text-light spinner-border-sm" role="status" style="display:none;">
                </button>
            </div>

            <div class="col-12">
                <a class="mt-5 btn btn-danger d-inline-block d-md-none" href="<?php echo wp_logout_url(); ?>">
                    退出登陆
                </a>
                <p class="my-4">
                    如果需要删除账号, 请使用账号绑定的邮箱 写一封邮件到站长的邮箱地址 hexie2109@gmail.com
                </p>

            </div>

            <div class="w-100 my-4 border-top"></div>

            <div class="col-12">

                <?php

                $user_black_list = get_user_black_list($user->ID);

                $user_black_list_length = count($user_black_list);

                $user_black_list_element = '';
                foreach ($user_black_list as $black_user_id)
                {
                    $black_user = get_userdata($black_user_id);
                    //如果用户存在
                    if ($black_user)
                    {
                        //转换成自定义用户类
                        $black_user = new My_User($black_user);
                        $black_user_avatar = print_user_avatar($black_user->user_image);

                        $user_black_list_element .= <<<HTML
                            <div class="col border">
                                
                                    <div class="row align-items-center py-3 g-3">
                                        <div class="col-auto">
                                            <a href="{$black_user->user_href}" title="查看该用户主页" target="_blank">
                                                {$black_user_avatar}
                                            </a>
                                        </div>
                                        <div class="col">
                                            <div class="text-break">
                                                {$black_user->display_name}
                                            </div>
                                        </div>
                                        <div class="col-12 col-sm-auto">
                                            <a class="btn btn-secondary delete-user-black-list" href="javascript:void(0);" data-target-user-id="{$black_user_id}">从黑名单里移除</a>
                                        </div>
                                    </div>
                             
                            </div>
HTML;
                    }
                }
                

                echo <<<HTML
                     <div class="mb-3">黑名单管理 {$user_black_list_length}</div>
                     <div class="row row-cols-1 row-cols-lg-2 row-cols-xxl-3 overflow-x-hidden " style="max-height: 600px;">
                        {$user_black_list_element}
                    </div>
HTML;
                ?>


            </div>




        </form>


    </div>


</div>

<?php get_footer(); ?>