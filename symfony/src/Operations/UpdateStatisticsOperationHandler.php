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
use App\Abstractions\Services\ResetStatisticsServiceInterface;
use App\Entity\StatisticsHelper;
use App\Model\Operations\Command\TestOperationCommand;
use App\Model\Operations\Command\GenerateDbOperationCommand;
use App\Model\Operations\Common\EmptyOperationResult;
use App\Model\Operations\Result\TestOperationResult;
use App\Operations\Common\IdentityRegistry;
use App\Services\ResetStatisticsService;

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
    /**
     * @var ResetStatisticsServiceInterface
     */
    private $resetStatisticsService;

    public function __construct(
        OperationScopeFactoryInterface $scopeFactory,
        SqlExecutorInterface $sqlExecutor,
        ResetStatisticsServiceInterface $resetStatisticsService
    )
    {
        $this->scopeFactory = $scopeFactory;
        $this->sqlExecutor = $sqlExecutor;
        $this->resetStatisticsService = $resetStatisticsService;
    }

    public function handle(OperationCommandInterface $command): OperationResultInterface
    {
        $scope = $this->scopeFactory->for($this->getIdentity());

        /* @var GenerateDbOperationCommand $cmd */
        $cmd = $command;

        $this->resetStatisticsService->reset();

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