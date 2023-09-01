<?php

namespace mikuclub\lib;

use ReflectionClass;

class Constant
{
    /**
     * 获取类的所有常量值
     *
     * @return array<int, mixed>
     */
    public static function get_to_array()
    {
        //获取类信息
        $object = new ReflectionClass(get_called_class());
        //转换成数组
        return array_values($object->getConstants());
    }
}

/**
 * 站内分类ID
 */
class Category extends Constant
{
    //其他区
    const OTHER = 1;
    //音乐区
    const MUSIC = 9;
    //动漫区
    const ANIME = 942;
    //软件区
    const SOFTWARE = 465;
    //游戏区
    const GAME = 182;
    //小说区
    const FICTION = 294;
    //教程区
    const TUTORIAL = 8621;
    //视频区
    const VIDEO = 9305;


    //魔法区(主分区)
    const ADULT_CATEGORY = 1120;
    //魔法MMD-3D区
    const ADULT_3D = 211;
    //魔法同人音声
    const ADULT_ASMR = 3055;
    //魔法写真
    const ADULT_PHOTO = 788;
    //魔法PC游戏
    const ADULT_PC_GAME = 1121;
    //魔法视频
    const ADULT_VIDEO = 1192;
    //魔法动画
    const ADULT_ANIME = 5998;
    //魔法图包
    const ADULT_IMAGE = 6678;
    //魔法小说
    const ADULT_FICTION = 6713;
    //魔法手机游戏/应用
    const ADULT_PHONE_GAME = 7476;

    /**
     * 获取所有成人分区的ID数组
     *
     * @return int[]
     */
    public static function get_array_adult()
    {
        return [
            static::ADULT_CATEGORY,
            static::ADULT_3D,
            static::ADULT_ASMR,
            static::ADULT_PHOTO,
            static::ADULT_PC_GAME,
            static::ADULT_VIDEO,
            static::ADULT_ANIME,
            static::ADULT_IMAGE,
            static::ADULT_FICTION,
            static::ADULT_PHONE_GAME,
        ];
    }

    /**
     * 获取所有不需要同步到微博的分类ID数组
     *
     * @return int[]
     */
    public static function get_array_not_weibo()
    {
        return [
            static::ADULT_CATEGORY,
            static::MUSIC,
            static::ANIME,
            static::SOFTWARE,
            static::GAME,
            static::FICTION,
            static::TUTORIAL,
            static::VIDEO,
            static::OTHER,

        ];
    }
}
