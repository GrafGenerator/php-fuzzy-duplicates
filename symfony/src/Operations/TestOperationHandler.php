<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 03.09.18
 * Time: 2:38
 */

namespace App\Operations;


use App\Abstractions\Cqrs\SqlExecutorInterface;
use App\Abstractions\OperationsProcessing\GenericOperationHandlerResult;
use App\Abstractions\OperationsProcessing\HandlerIdentity;
use App\Abstractions\OperationsProcessing\OperationCommandInterface;
use App\Abstractions\OperationsProcessing\OperationHandlerInterface;
use App\Abstractions\OperationsProcessing\OperationResultInterface;
use App\Abstractions\OperationsProcessing\OperationScopeFactoryInterface;
use App\Entity\Client;
use App\Model\Operations\Command\TestOperationCommand;
use App\Model\Operations\Result\TestOperationResult;
use App\Operations\Common\IdentityRegistry;

final class TestOperationHandler implements OperationHandlerInterface
{

    /**
     * @var OperationScopeFactoryInterface
     */
    private $scopeFactory;
    /**
     * @var SqlExecutorInterface
     */
    private $sqlExecutor;

    public function __construct(
        OperationScopeFactoryInterface $scopeFactory,
        SqlExecutorInterface $sqlExecutor
    )
    {
        $this->scopeFactory = $scopeFactory;
        $this->sqlExecutor = $sqlExecutor;
    }

    public function handle(OperationCommandInterface $command): OperationResultInterface
    {
        $scope = $this->scopeFactory->for($this->getIdentity());

        $repo = $scope->getRepo(Client::class);

        $this->sqlExecutor->execute("");

        /* @var TestOperationCommand $cmd */
        $cmd = $command;

        $result = new TestOperationResult($cmd->getValue());

        $scope->complete();

        return GenericOperationHandlerResult::ok($result);
    }

    /**
     * @return HandlerIdentity
     */
    public function getIdentity()
    {
        return IdentityRegistry::getRegistry()->getTest();
    }
}