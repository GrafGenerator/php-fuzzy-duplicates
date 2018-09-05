<?php

namespace App\Controller;

use App\Abstractions\OperationsProcessing\OperationHandlersFactoryInterface;
use App\Helpers\JmsJsonTrait;
use App\Model\Api\GenerateDbApiRequest;
use App\Model\Operations\Command\TestOperationCommand;
use App\Model\Operations\Command\GenerateDbOperationCommand;
use App\Operations\Common\IdentityRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Psr\Log\LoggerInterface;
use App\Entity;

class ClientController extends Controller
{
    use JmsJsonTrait;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var OperationHandlersFactoryInterface
     */
    private $handlersFactory;

    public function __construct(
        LoggerInterface $logger,
        OperationHandlersFactoryInterface $handlersFactory
    )
    {
        $this->logger = $logger;
        $this->handlersFactory = $handlersFactory;
    }

    public function index()
    {
        return phpinfo();
    }

    /**
     * @Route("/test", name="test", methods={"POST"})
     * @param GenerateDbApiRequest $request
     * @return Response
     */
    public function test(GenerateDbApiRequest $request){
        $sampleValue = $request->getSampleValue();

        /*
        $handler = $this->handlersFactory->get(IdentityRegistry::getRegistry()->getUpdateStatistics());
        $command = new UpdateStatisticsOperationCommand(333, 111);
        */

        $handler = $this->handlersFactory->get(IdentityRegistry::getRegistry()->getTest());
        $command = new TestOperationCommand($sampleValue);

        $result = $handler->handle($command);

        return $this->jmsJson($result);
    }

    /**
     * @Route("/generateDb", name="generateDb", methods={"POST"})
     * @param GenerateDbApiRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function generateDb(GenerateDbApiRequest $request){
        $handler = $this->handlersFactory->get(IdentityRegistry::getRegistry()->getGenerateDb());
        $command = new GenerateDbOperationCommand($request->getTotalCount(), $request->getDuplicatesCount());

        $result = $handler->handle($command);

        return $this->jmsJson($result);
    }

    /**
     * @Route("/fetchDuplicatesSql", name="fetchDuplicatesSql", methods={"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function fetchDuplicatesSql(Request $request){
        $matchThreshold = $request->get("matchThreshold") ?? 90;
        $doctrine = $this->getDoctrine();

        $doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);

        gc_enable();
        gc_collect_cycles();

        $repo = $doctrine->getRepository(Entity\Client::class);
        $statisticsRepo = $doctrine->getRepository(Entity\StatisticsHelper::class);

        list($generatedCount, $generatedDuplicatesCount) = $statisticsRepo->getStatistics();

        $time_start = microtime(true);
        $repo->setupHashes();
        $buildHashesTime = microtime(true) - $time_start;

        $time_start = microtime(true);
        $result = $repo->fetchDuplicatesIds($matchThreshold);
        $duplicatesSearchTime = microtime(true) - $time_start;

        $repo->teardownHashes();

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

        gc_enable();
        gc_collect_cycles();

        return $this->json([
            'hashBuildTime' => $buildHashesTime,
            'duplicatesSearchTime' => $duplicatesSearchTime,
            'duplicatesCount' => sizeof($result),
            'exactDuplicatesCount' => $exactMatchesCount,
            'intendedDuplicatesCount' => $intendedMatchesCount,
            'result' => $result
        ]);
    }

    /**
     * @Route("/fetchDuplicatesPhp", name="fetchDuplicatesPhp", methods={"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function fetchDuplicatesPhp(Request $request){
        $matchThreshold = $request->get("matchThreshold") ?? 90;
        $doctrine = $this->getDoctrine();

        $doctrine->getManager()->getConnection()->getConfiguration()->setSQLLogger(null);

        gc_enable();
        gc_collect_cycles();

        $repo = $doctrine->getRepository(Entity\Client::class);
        $statisticsRepo = $doctrine->getRepository(Entity\StatisticsHelper::class);

        list($generatedCount, $generatedDuplicatesCount) = $statisticsRepo->getStatistics();

        $time_start = microtime(true);
        $repo->setupHashes();
        $buildHashesTime = microtime(true) - $time_start;

        $time_start = microtime(true);
        $result = $repo->getHashesToCompareIterable();

        $compareResults = [];
        $exactMatchesCount = 0;
        $intendedMatchesCount = 0;

        $batchSize = 500;
        $current = 0;

        while($r = $result->fetch()){
            $cr = ssdeep_fuzzy_compare("$r->getHash1()", "$r    ->getHash2()");
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

            if((current % $batchSize) === 0){
                $tempObjects = null;

                gc_enable();
                gc_collect_cycles();
            }
        }

        $duplicatesSearchTime = microtime(true) - $time_start;

        $repo->teardownHashes();

        gc_enable();
        gc_collect_cycles();

        return $this->json([
            'hashBuildTime' => $buildHashesTime,
            'duplicatesSearchTime' => $duplicatesSearchTime,
            'duplicatesCount' => sizeof($compareResults),
            'exactDuplicatesCount' => $exactMatchesCount,
            'intendedDuplicatesCount' => $intendedMatchesCount,
            'result' => $compareResults
        ]);
    }
}
