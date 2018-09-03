<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 03.09.18
 * Time: 2:38
 */

namespace App\Operations;


use App\Abstractions\OperationsProcessing\GenericOperationHandlerResult;
use App\Abstractions\OperationsProcessing\OperationCommandInterface;
use App\Abstractions\OperationsProcessing\OperationHandlerInterface;
use App\Abstractions\OperationsProcessing\OperationResultInterface;
use App\Model\Operations\Command\TestOperationCommand;
use App\Model\Operations\Result\TestOperationResult;

final class TestOperationHandler implements OperationHandlerInterface
{

    public function handle(OperationCommandInterface $command): OperationResultInterface
    {
        /* @var TestOperationCommand $cmd */
        $cmd = $command;

        $result = new TestOperationResult($cmd->getValue());

        return GenericOperationHandlerResult::ok($result);
    }
}