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
use App\Model\ComparisonDto;
use App\Model\Operations\Command\FetchDuplicatesPhpOperationCommand;
use App\Model\Operations\Result\FetchDuplicatesOperationResult;
use App\Operations\Common\IdentityRegistry;
use Doctrine\DBAL\Driver\Statement;

final class FetchDuplicatesPhpOperationHandler implements OperationHandlerInterface
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

        /* @var FetchDuplicatesPhpOperationCommand $cmd */
        $cmd = $command;

        gc_enable();
        gc_collect_cycles();

        $this->startTracking();
        $this->ssdeepHashesDbService->setup();
        $buildHashesTime = $this->getElapsedTimeAndReset();

        $iterableResult = $this->clientReader->getHashesToCompareIterable();
        [$exactMatchesCount, $intendedMatchesCount, $compareResult] = $this->calculateDuplicatesIterable(
            $iterableResult,
            $cmd->getMatchThreshold()
        );
        $duplicatesSearchTime = $this->getElapsedTime();

        $this->ssdeepHashesDbService->tearDown();

        gc_enable();
        gc_collect_cycles();

        $result = new FetchDuplicatesOperationResult(
            $buildHashesTime,
            $duplicatesSearchTime,
            sizeof($compareResult),
            $exactMatchesCount,
            $intendedMatchesCount,
            $compareResult
        );

        $scope->complete();

        return GenericOperationHandlerResult::ok($result);
    }

    /**
     * @return HandlerIdentity
     */
    public function getIdentity()
    {
        return IdentityRegistry::getRegistry()->getFetchDuplicatesPhp();
    }

    /**
     * @param Statement $iterableResult
     * @param int $matchThreshold
     * @return array
     */
    private function calculateDuplicatesIterable(Statement $iterableResult, int $matchThreshold): array {
        [$generatedCount, $generatedDuplicatesCount] = $this->statisticsHelperReader->getStatistics();

        $compareResults = [];
        $exactMatchesCount = 0;
        $intendedMatchesCount = 0;

        $batchSize = 500;
        $current = 0;


        while($r = $iterableResult->fetch()){
            /* @var ComparisonDto $r */
            $cr = ssdeep_fuzzy_compare($r->getHash1(), $r->getHash2());
            if($cr > $matchThreshold){
                $id1 = intval($r->getId1());
                $id2 = intval($r->getId2());

                $compareResults[] = [
                    'id1' => $id1,
                    'id2' => $id2,
                    'compareResult' => $cr
                ];

                if($id1 <= $generatedDuplicatesCount && $id2 > $generatedCount - $generatedDuplicatesCount ||
                    $id2 <= $generatedDuplicatesCount && $id1 > $generatedCount - $generatedDuplicatesCount) {
                    ++$intendedMatchesCount;
                }
            }

            if($cr === 100){
                ++$exactMatchesCount;
            }

            $tempObjects[] = $r;
            $current++;

            if(($current % $batchSize) === 0){
                $tempObjects = null;

                gc_enable();
                gc_collect_cycles();
            }
        }

        return array($exactMatchesCount, $intendedMatchesCount, $compareResults);
    }
}