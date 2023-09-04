<?php

namespace mikuclub;

//导入初始常量
require_once 'constant' . DIRECTORY_SEPARATOR . 'constant.php';

//导入后台管理员页面
require_once 'admin/config.php';

//获取主题lib文件夹路径
$theme_lib_directory = get_template_directory()  . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;

$array_file_path_to_require = array_merge(
    //导入常亮
    glob($theme_lib_directory . 'constant' . DIRECTORY_SEPARATOR . '*.php'),
    //导入类
    glob($theme_lib_directory . 'class' . DIRECTORY_SEPARATOR . '*.php'),
    //导入函数
    glob($theme_lib_directory . 'function' . DIRECTORY_SEPARATOR . '*.php'),
    //导入HTML组件
    glob($theme_lib_directory . 'component' . DIRECTORY_SEPARATOR . '*.php')
);
foreach ($array_file_path_to_require as $file_path)
{
    require_once $file_path;
}
