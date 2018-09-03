<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 03.09.18
 * Time: 0:38
 */

namespace App\Abstractions\OperationsProcessing;


interface OperationHandlersFactoryInterface
{
    public function get(HandlerIdentity $identity): OperationHandlerInterface;
}