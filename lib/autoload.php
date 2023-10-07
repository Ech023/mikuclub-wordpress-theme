<?php

namespace mikuclub;

//导入初始常量
require_once 'constant' . DIRECTORY_SEPARATOR . 'constant.php';

//获取主题lib文件夹路径
$theme_lib_directory = get_template_directory()  . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;

$array_file_path_to_require = array_merge(
    //导入常亮
    glob($theme_lib_directory . 'constant' . DIRECTORY_SEPARATOR . '*.php'),
    //导入旧类
    glob($theme_lib_directory . 'old_class' . DIRECTORY_SEPARATOR . '*.php'),
    //导入类
    // glob($theme_lib_directory . 'class' . DIRECTORY_SEPARATOR . '*.php'),
    //导入错误类
    // glob($theme_lib_directory . 'exception' . DIRECTORY_SEPARATOR . '*.php'),
    //导入函数
    glob($theme_lib_directory . 'function' . DIRECTORY_SEPARATOR . '*.php'),
    //导入旧函数
    glob($theme_lib_directory . 'old_function' . DIRECTORY_SEPARATOR . '*.php'),
    //导入钩子和动作
    glob($theme_lib_directory . 'action' . DIRECTORY_SEPARATOR . '*.php'),
    //导入API接口
    glob($theme_lib_directory . 'api' . DIRECTORY_SEPARATOR . '*.php'),
    //导入旧HTML组件
    glob($theme_lib_directory . 'old_component' . DIRECTORY_SEPARATOR . '*.php'),
    //导入HTML组件
    glob($theme_lib_directory . 'component' . DIRECTORY_SEPARATOR . '*.php')
);

foreach ($array_file_path_to_require as $file_path)
{
    require_once $file_path;
}

//registrare la funzione autoload da eseguire quando invoca una classe
spl_autoload_register(function ($class) use ($theme_lib_directory)
{
    //rimuovere eventuale namespace dalla nome di classe
    $array_file_name = explode('\\', $class);
    $file_name = array_pop($array_file_name);

    //替换成小写
    $file_name = strtolower($file_name);
    //把下划线 替换成破折号
    $file_name = 'class-' . str_replace("_", "-", $file_name);

    //cercare class file nei percorsi specificati
    foreach (array_merge(
        //glob($theme_lib_directory . 'old_class' . DIRECTORY_SEPARATOR . $file_name . '.php'),
        glob($theme_lib_directory . 'class' . DIRECTORY_SEPARATOR . $file_name . '.php'),
        glob($theme_lib_directory . 'model' . DIRECTORY_SEPARATOR . $file_name . '.php'),
        glob($theme_lib_directory . 'exception' . DIRECTORY_SEPARATOR . $file_name . '.php')
    ) as $php_file)
    {
        //effettua require se trova 
        require_once $php_file;
    }
});
