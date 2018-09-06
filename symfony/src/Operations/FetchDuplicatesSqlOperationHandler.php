<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 03.09.18
 * Time: 2:38
 */

namespace App\Operations;


use App\Abstractions\OperationsProcessing\GenericOperationHandlerResult;
use App\Abstractions\OperationsProcessing\HandlerIdentity;
use App\Abstractions\OperationsProcessing\OperationCommandInterface;
use App\Abstractions\OperationsProcessing\OperationHandlerInterface;
use App\Abstractions\OperationsProcessing\OperationHandlersFactoryInterface;
use App\Abstractions\OperationsProcessing\OperationResultInterface;
use App\Abstractions\OperationsProcessing\OperationScopeFactoryInterface;
use App\Abstractions\Readers\ClientReaderInterface;
use App\Abstractions\Readers\StatisticsHelperReaderInterface;
use App\Abstractions\Services\SsdeepHashesDbServiceInterface;
use App\Helpers\TrackedOperationTrait;
use App\Model\Operations\Command\FetchDuplicatesSqlOperationCommand;
use App\Model\Operations\Result\FetchDuplicatesOperationResult;
use App\Operations\Common\IdentityRegistry;

final class FetchDuplicatesSqlOperationHandler implements OperationHandlerInterface
{
    use TrackedOperationTrait;

    /**
     * @var OperationHandlersFactoryInterface
     */
    private $handlersFactory;
    /**
     * @var OperationScopeFactoryInterface
     */
    private $scopeFactory;
    /**
     * @var ClientReaderInterface
     */
    private $clientReader;
    /**
     * @var StatisticsHelperReaderInterface
     */
    private $statisticsHelperReader;
    /**
     * @var SsdeepHashesDbServiceInterface
     */
    private $ssdeepHashesDbService;


    public function __construct(
        OperationHandlersFactoryInterface $handlersFactory,
        OperationScopeFactoryInterface $scopeFactory,
        SsdeepHashesDbServiceInterface $ssdeepHashesDbService,
        ClientReaderInterface $clientReader,
        StatisticsHelperReaderInterface $statisticsHelperReader
    )
    {
        $this->scopeFactory = $scopeFactory;
        $this->clientReader = $clientReader;
        $this->statisticsHelperReader = $statisticsHelperReader;
        $this->ssdeepHashesDbService = $ssdeepHashesDbService;
        $this->handlersFactory = $handlersFactory;
    }

    /**
     * @param OperationCommandInterface $command
     * @return OperationResultInterface
     * @throws \Doctrine\DBAL\DBALException
     */
    public function handle(OperationCommandInterface $command): OperationResultInterface
    {
        $scope = $this->scopeFactory->for($this->getIdentity());

        /* @var FetchDuplicatesSqlOperationCommand $cmd */
        $cmd = $command;

        gc_enable();
        gc_collect_cycles();

        $this->startTracking();
        $this->ssdeepHashesDbService->setup();
        $buildHashesTime = $this->getElapsedTimeAndReset();

        $duplicatesResult = $this->clientReader->fetchDuplicatesIds($cmd->getMatchThreshold());
        $duplicatesSearchTime = $this->getElapsedTime();

        $this->ssdeepHashesDbService->tearDown();

        [$exactMatchesCount, $intendedMatchesCount] = $this->calculateStatistics($duplicatesResult);

        gc_enable();
        gc_collect_cycles();

        $result = new FetchDuplicatesOperationResult(
            $buildHashesTime,
            $duplicatesSearchTime,
            sizeof($duplicatesResult),
            $exactMatchesCount,
            $intendedMatchesCount,
            $duplicatesResult
        );

        $scope->complete();

        return GenericOperationHandlerResult::ok($result);
    }

    /**
     * @return HandlerIdentity
     */
    public function getIdentity()
    {
        return IdentityRegistry::getRegistry()->getFetchDuplicatesSql();
    }

    /**
     * @param $result
     * @return array
     */
    private function calculateStatistics($result): array {
        [$generatedCount, $generatedDuplicatesCount] = $this->statisticsHelperReader->getStatistics();

        $exactMatchesCount = 0;
        $intendedMatchesCount = 0;

        foreach ($result as $r){
            if(intval($r['compareResult']) === 100){
                ++$exactMatchesCount;
            }

            $id1 = intval($r['id1']);
            $id2 = intval($r['id2']);

            if($id1 <= $generatedDuplicatesCount && $id2 > $generatedCount - $generatedDuplicatesCount ||
                $id2 <= $generatedDuplicatesCount && $id1 > $generatedCount - $generatedDuplicatesCount) {
                ++$intendedMatchesCount;
            }
        }

        return array($exactMatchesCount, $intendedMatchesCount);
    }
}