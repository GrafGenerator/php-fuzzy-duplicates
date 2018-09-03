<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 03.09.18
 * Time: 1:31
 */

namespace App\Abstractions\OperationsProcessing;


interface OperationHandlerServiceLocatorInterface
{
    public function registerHandler($handler, string $handlerClass);
}