<?php

namespace mikuclub;

use Exception;
use Throwable;

/**
 * 数据不存在 异常
 */
class Empty_Exception extends Exception
{
    /**
     *@param string $message — [optional] The Exception message to throw.
     *@param int $code — [optional] The Exception code.
     *@param null|Throwable $previous [optional] The previous throwable used for the exception chaining.
     *@return mixed
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = $message . ' 不存在';

        parent::__construct($message, $code, $previous);
    }
}
