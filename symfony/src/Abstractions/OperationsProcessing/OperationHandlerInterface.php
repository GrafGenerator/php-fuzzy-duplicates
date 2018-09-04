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
    /**
     * @return HandlerIdentity
     */
    public function getIdentity();

    public function handle(OperationCommandInterface $command) : OperationResultInterface;
}