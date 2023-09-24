<?php

namespace mikuclub;

use Exception;
use Throwable;

/**
 *  无效类型 异常
 */
class Invalid_Type_Exception extends Exception
{

    // /**
    //  *@param string $message — [optional] The Exception message to throw.
    //  *@param int $code — [optional] The Exception code.
    //  *@param null|Throwable $previous [optional] The previous throwable used for the exception chaining.
    //  *@return mixed
    //  */
    // public function __construct($message = "", $code = 0, Throwable $previous = null)
    // {
    //     $message = $message . ' 无效类型';

    //     parent::__construct($message, $code, $previous);
    // }
}
