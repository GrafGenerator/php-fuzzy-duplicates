<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 03.09.18
 * Time: 1:02
 */

namespace App\Abstractions\OperationsProcessing;


interface OperationHandlerInterface
{
    public function handle(OperationCommandInterface $command) : OperationResultInterface;
}