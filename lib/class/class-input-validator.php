<?php

namespace mikuclub;

use Exception;

/**
 * 输入数据 验证器
 */
class Input_Validator
{
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOL = 'bool';
    const TYPE_STRING = 'string';
    const TYPE_ARRAY = 'array';
    const TYPE_ARRAY_INT = 'array_int';


    /**
     * 过滤数值
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    public static function filter_value($value, $type)
    {
        $result = null;

        switch ($type)
        {

            case static::TYPE_INT:
                $result = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
                break;

            case static::TYPE_FLOAT:
                $result = filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
                break;

            case static::TYPE_BOOL:
                $result = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                break;

            case static::TYPE_STRING:
                $result = htmlentities(trim($value));
                break;

            case static::TYPE_ARRAY:
                if (is_array($value))
                {
                    $result = $value;
                }
                break;

            case static::TYPE_ARRAY_INT:
                if (is_array($value))
                {
                    //遍历数组的元素
                    $result = array_map(function (int $element)
                    {
                        //转换成int
                        return filter_var($element, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
                    }, $value);
                    //如果有元素转换失败
                    if (in_array(null, $result))
                    {
                        //直接取消结果
                        $result = null;
                    }
                }
                break;
        }

        return $result;
    }

    /**
     * 从数组里提取对应类型的数值
     *
     * @param mixed $array 数据数组
     * @param string $key 键名
     * @param string $type 数据类型
     * @param bool $mandatory 是否为必须参数
     * @return mixed
     * @throws Null_Exception|Invalid_Type_Exception
     */
    public static function get_array_value($array, $key, $type, $mandatory = false)
    {

        $result = null;

        //数组有指定键名 并且不是NULL
        if (isset($array[$key]))
        {

            //过滤数值
            $result = static::filter_value($array[$key], $type);
            if ($result === null)
            {
                //弹出 NULL 异常
                throw new Invalid_Type_Exception($key . '  类型错误');
            }
        }
        //否则 如果为不存 或者 数值为null + 是必须参数
        else if ($result === null && $mandatory === true)
        {
            //弹出 NULL 异常
            throw new Null_Exception($key . '  为NULL');
        }

        return $result;
    }


    /**
     * 从$_REQUEST数组里提取对应类型的数值
     *
     * @param string $key 键名
     * @param string $type 数据类型
     * @param bool $mandatory 是否为必须参数
     * @return mixed
     */
    public static function get_request_value($key, $type, $mandatory = false)
    {
        return static::get_array_value($_REQUEST, $key, $type, $mandatory);
    }
}
