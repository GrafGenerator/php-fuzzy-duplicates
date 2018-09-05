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
use App\Abstractions\OperationsProcessing\OperationHandlersFactoryInterface;
use App\Abstractions\OperationsProcessing\OperationResultInterface;
use App\Abstractions\OperationsProcessing\OperationScopeFactoryInterface;
use App\Abstractions\OperationsProcessing\OperationScopeInterface;
use App\Abstractions\Readers\ClientReaderInterface;
use App\Abstractions\Services\CleanupDbServiceInterface;
use App\Abstractions\Services\GenerateClientDuplicateServiceInterface;
use App\Abstractions\Services\GenerateClientServiceInterface;
use App\Abstractions\Services\GetNamesServiceInterface;
use App\Entity\Client;
use App\Entity\StatisticsHelper;
use App\Helpers\TrackedOperationTrait;
use App\Model\Operations\Command\TestOperationCommand;
use App\Model\Operations\Command\GenerateDbOperationCommand;
use App\Model\Operations\Command\UpdateStatisticsOperationCommand;
use App\Model\Operations\Common\EmptyOperationResult;
use App\Model\Operations\Result\GenerateDbOperationResult;
use App\Model\Operations\Result\TestOperationResult;
use App\Operations\Common\IdentityRegistry;

final class GenerateDbOperationHandler implements OperationHandlerInterface
{
    use TrackedOperationTrait;

    /**
     * @var OperationScopeFactoryInterface
     */
    private $scopeFactory;
    /**
     * @var SqlExecutorInterface
     */
    private $sqlExecutor;
    /**
     * @var CleanupDbServiceInterface
     */
    private $cleanupDbService;
    /**
     * @var GetNamesServiceInterface
     */
    private $getNamesService;
    /**
     * @var OperationHandlersFactoryInterface
     */
    private $handlersFactory;
    /**
     * @var GenerateClientServiceInterface
     */
    private $generateClientService;
    /**
     * @var GenerateClientDuplicateServiceInterface
     */
    private $generateClientDuplicateService;
    /**
     * @var ClientReaderInterface
     */
    private $clientReader;

    public function __construct(
        OperationHandlersFactoryInterface $handlersFactory,
        OperationScopeFactoryInterface $scopeFactory,
        SqlExecutorInterface $sqlExecutor,
        CleanupDbServiceInterface $cleanupDbService,
        GetNamesServiceInterface $getNamesService,
        GenerateClientServiceInterface $generateClientService,
        GenerateClientDuplicateServiceInterface $generateClientDuplicateService,
        ClientReaderInterface $clientReader
    )
    {
        $this->scopeFactory = $scopeFactory;
        $this->sqlExecutor = $sqlExecutor;
        $this->cleanupDbService = $cleanupDbService;
        $this->getNamesService = $getNamesService;
        $this->handlersFactory = $handlersFactory;
        $this->generateClientService = $generateClientService;
        $this->generateClientDuplicateService = $generateClientDuplicateService;
        $this->clientReader = $clientReader;
    }

    public function handle(OperationCommandInterface $command): OperationResultInterface
    {
        $scope = $this->scopeFactory->for($this->getIdentity());

        /* @var GenerateDbOperationCommand $cmd */
        $cmd = $command;

        $this->startTracking();

        $names = $this->getNamesService->getNames();
        $this->cleanupDbService->cleanUp();

        $this->generateClients($scope, $cmd->getTotalCount(), $cmd->getDuplicatesCount(), $names);
        [$duplicatedPairs, $exactDuplicatesCount] = $this->generateDuplicates($scope, $cmd->getDuplicatesCount(), $names);

        $executionTime = $this->getElapsedTime();

        $result = new GenerateDbOperationResult($executionTime, $exactDuplicatesCount, $duplicatedPairs);

        $scope->complete();

        return GenericOperationHandlerResult::ok($result);
    }

    /**
     * @return HandlerIdentity
     */
    public function getIdentity()
    {
        return IdentityRegistry::getRegistry()->getGenerateDb();
    }

    /**
     * @param $scope OperationScopeInterface
     * @param int $totalCount
     * @param int $duplicatesCount
     * @param array $names
     */
    private function generateClients($scope, int $totalCount, int $duplicatesCount, array $names)
    {
        $repo = $scope->getRepo(Client::class);

        $updateStatisticsSvc = $this->handlersFactory->get(IdentityRegistry::getRegistry()->getUpdateStatistics());
        $statisticsCommand = new UpdateStatisticsOperationCommand($totalCount, $duplicatesCount);
        $updateStatisticsSvc->handle($statisticsCommand);

        $fairCount = $totalCount - $duplicatesCount;
        $startBirthDate = \DateTime::createFromFormat('d/m/Y', '1/1/1971');
        $endBirthDate = \DateTime::createFromFormat('d/m/Y', '1/12/2000');

        // generate new clients in batch
        $batchSize = 500;

        for ($i = 0; $i < $fairCount; ++$i){
            $client = $this->generateClientService->generate($names, $startBirthDate, $endBirthDate);

            $repo->add($client);

            $tempRecords[] = $client;

            if (($i % $batchSize) === 0) {
                $scope->commit();

                foreach ($tempRecords as $record){
                    $repo->detach($record);
                }

                $tempRecords = null;

                gc_enable();
                gc_collect_cycles();
            }
        }

        $scope->commit();
        $repo->clear();
    }

    /**
     * @param $scope OperationScopeInterface
     * @param int $duplicatesCount
     * @param array $names
     * @return array
     * @throws \Exception
     */
    private function generateDuplicates($scope, int $duplicatesCount, array $names)
    {
        $repo = $scope->getRepo(Client::class);
        $origins = $this->clientReader->getFirstN($duplicatesCount);

        $duplicatedPairs = [];
        $exactDuplicatesCount = 0;

        foreach ($origins as $origin) {
            $isExactDuplicate = rand(1, 100) > 50;
            $clientDuplicate = $this->generateClientDuplicateService->generate($origin, $isExactDuplicate);

            $repo->add($clientDuplicate);

            if($isExactDuplicate){
                ++$exactDuplicatesCount;
            }

            $duplicatedPairs[] = [
                'isExactDuplicate' => $isExactDuplicate,
                'original' => $origin,
                'duplicate' => $clientDuplicate,
            ];
        }

        $scope->commit();

        return array($duplicatedPairs, $exactDuplicatesCount);
    }
}