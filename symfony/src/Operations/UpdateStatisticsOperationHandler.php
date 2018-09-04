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
use App\Entity\StatisticsHelper;
use App\Model\Operations\Command\TestOperationCommand;
use App\Model\Operations\Command\UpdateStatisticsOperationCommand;
use App\Model\Operations\Common\EmptyOperationResult;
use App\Model\Operations\Result\TestOperationResult;
use App\Operations\Common\IdentityRegistry;

final class UpdateStatisticsOperationHandler implements OperationHandlerInterface
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

        /* @var UpdateStatisticsOperationCommand $cmd */
        $cmd = $command;

        // clear existing statistics
        $this->sqlExecutor->execute("TRUNCATE TABLE statistics_helper");

        $repo = $scope->getDefaultRepo();

        $clientsCountStatistics = new StatisticsHelper();
        $clientsCountStatistics->setName('total');
        $clientsCountStatistics->setValue($cmd->getTotalCount());

        $duplicatesCountStatistics = new StatisticsHelper;
        $duplicatesCountStatistics->setName('duplicates');
        $duplicatesCountStatistics->setValue($cmd->getDuplicatesCount());

        $repo->add($clientsCountStatistics);
        $repo->add($duplicatesCountStatistics);

        $scope->complete();

        return GenericOperationHandlerResult::ok(EmptyOperationResult::create());
    }

    /**
     * @return HandlerIdentity
     */
    public function getIdentity()
    {
        return IdentityRegistry::getRegistry()->getUpdateStatistics();
    }
}