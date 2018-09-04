<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 04.09.18
 * Time: 21:43
 */

namespace App\Abstractions\OperationsProcessing;


interface OperationScopeFactoryInterface
{
    /**
     * @param HandlerIdentity $handlerIdentity
     * @return OperationScopeInterface
     */
    public function for(HandlerIdentity $handlerIdentity);
}