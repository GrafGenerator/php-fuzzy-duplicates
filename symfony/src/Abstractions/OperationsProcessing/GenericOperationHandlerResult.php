<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 03.09.18
 * Time: 22:39
 */

namespace App\Abstractions\OperationsProcessing;


class GenericOperationHandlerResult implements OperationResultInterface
{
    /**
     * @var bool
     */
    private $success;
    /**
     * @var mixed|null
     */
    private $result;

    private function __construct(bool $success, $result)
    {

        $this->success = $success;
        $this->result = $result;
    }

    public function getIsSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * @return mixed|null
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param $result
     * @return GenericOperationHandlerResult
     */
    public static function ok($result) {
        return new GenericOperationHandlerResult(true, $result);
    }

    /**
     * @return GenericOperationHandlerResult
     */
    public static function fail() {
        return new GenericOperationHandlerResult(false, null);
    }
}