<?php

namespace App\Controller;

use DateTime;
use Doctrine\DBAL\FetchMode;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Psr\Log\LoggerInterface;
use App\Entity;

class ClientController extends Controller
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("/test", name="test")
     */
    public function index()
    {
    }

    /**
     * @Route("/testSql", name="testSql", methods={"GET"})
     */
    public function testSql(){
        $doctrine = $this->getDoctrine();
        $repo = $doctrine->getRepository(Entity\Client::class);

        $rawResult = $repo->getHashes();
        $result = $rawResult;
        $compResult = [];

        $original = array_shift($result)['hash'];
        foreach ($result as $d){
            $compareResult = ssdeep_fuzzy_compare($original, $d['hash']);

            $compResult[] = [
                'compareResult' => $compareResult,
                'originalHash' => $original,
                'hashToCompare' => $d
            ];
        }

        return $this->json([
            'count' => sizeof($rawResult),
            'rawData' => $rawResult,
            'comparison' => $compResult
        ]);
    }

    /**
     * @Route("/generateDb", name="generateDb", methods={"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function generateDb(Request $request){
        $time_start = microtime(true);

        $names = $this->getNames();
        $doctrine = $this->getDoctrine();

        $this->clearAllClients($doctrine);

        $totalCount = $request->get("clients") ?? 10000000;
        $duplicatesCount = $request->get("intendedDuplicates") ?? 100;

        list($duplicatedPairs, $exactDuplicatesCount) = $this->generateClients($doctrine, $totalCount, $duplicatesCount, $names);

        $executionTime = microtime(true) - $time_start;

        return $this->json([
            'executionTime' => $executionTime,
            'exactDuplicatesCount' => $exactDuplicatesCount,
            'duplicatePairs' => $duplicatedPairs,
        ]);
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

    private function getNames(){
        $finder = new Finder();
        $finder->files()->in(__DIR__ . "/../Data");

        $names = [
            'male' => [
                'name' => null,
                'surname' => null,
                'patronymic' => null,
            ],
            'female' => [
                'name' => null,
                'surname' => null,
                'patronymic' => null,
            ],
        ];

        foreach ($finder as $file) {
            $fileName = $file->getBasename("." . $file->getExtension());
            $parts = explode("-", $fileName);
            $namesInFile = explode("\n", $file->getContents());
            $names[$parts[0]][$parts[1]] = array_values($namesInFile);
        }

        return $names;
    }

    private function clearAllClients(\Doctrine\Common\Persistence\ManagerRegistry $doctrine){
        $clientRepo = $doctrine->getRepository(Entity\Client::class);
        $statisticsRepo = $doctrine->getRepository(Entity\StatisticsHelper::class);

        $clientRepo->clearClients();
        $statisticsRepo->clearStatistics();
    }

    private function generateClients(\Doctrine\Common\Persistence\ManagerRegistry $doctrine, int $totalCount, int $duplicatesCount, array $names) : array
    {
        $em = $doctrine->getManager();
        $statisticsRepo = $doctrine->getRepository(Entity\StatisticsHelper::class);

        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $statisticsRepo->setStatistics($totalCount, $duplicatesCount);

        $fairCount = $totalCount - $duplicatesCount;
        $startBirthDate = DateTime::createFromFormat('d/m/Y', '1/1/1971');
        $endBirthDate = DateTime::createFromFormat('d/m/Y', '1/12/2000');

        // generate new clients in batch
        $batchSize = 500;

        for ($i = 0; $i < $fairCount; ++$i){
            $client = $this->generateNewClient($names, $startBirthDate, $endBirthDate);

            $em->persist($client);

            $tempRecords[] = $client;

            if (($i % $batchSize) === 0) {
                $em->flush();

                foreach ($tempRecords as $record){
                    $em->detach($record);
                }

                $tempRecords = null;

                gc_enable();
                gc_collect_cycles();
            }
        }

        $em->flush();
        $em->clear();

        # generate duplicates
        $letters = mb_convert_encoding("абвгдеёжзийклмнопрстуфхцчшщъыьэюя", "UTF-8");
        $lettersCount = mb_strlen($letters);
        $repo = $doctrine->getRepository(Entity\Client::class);
        $origins = $repo->getFirstN($duplicatesCount);

        $duplicatedPairs = [];
        $exactDuplicatesCount = 0;

        foreach ($origins as $origin) {
            $isExactDuplicate = rand(1, 100) > 50;
            $clientDuplicate = $this->generateClientDuplicate($origin, $isExactDuplicate, $letters, $lettersCount);

            $em->persist($clientDuplicate);

            if($isExactDuplicate){
                ++$exactDuplicatesCount;
            }

            $duplicatedPairs[] = [
                'isExactDuplicate' => $isExactDuplicate,
                'original' => $origin,
                'duplicate' => $clientDuplicate,
            ];
        }

        $em->flush();
        //$em->clear();

        return array($duplicatedPairs, $exactDuplicatesCount);
    }

    private function randomDateInRange(DateTime $start, DateTime $end) {
        $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());
        $randomDate = new DateTime();
        $randomDate->setTimestamp($randomTimestamp);
        return $randomDate;
    }

    /**
     * @param array $names
     * @param $startBirthDate
     * @param $endBirthDate
     * @return Entity\Client
     */
    private function generateNewClient(array $names, $startBirthDate, $endBirthDate): Entity\Client
    {
        $sexSelector = rand(1, 100) > 50 ? "male" : "female";
        $name = $names[$sexSelector]["name"][array_rand($names[$sexSelector]["name"])];
        $surname = $names[$sexSelector]["surname"][array_rand($names[$sexSelector]["surname"])];
        $patronymic = $names[$sexSelector]["patronymic"][array_rand($names[$sexSelector]["patronymic"])];
        $birthDate = $this->randomDateInRange($startBirthDate, $endBirthDate);
        $passportSeries = rand(5000, 6999);
        $passportNumber = rand(1, 999999);

        $client = new Entity\Client;
        $client->setFullName($surname . " " . $name . " " . $patronymic);
        $client->setBirthDate($birthDate);
        $client->setPassportSeries(sprintf("%'.04d", $passportSeries));
        $client->setPassportNumber(sprintf("%'.06d", $passportNumber));

        return $client;
    }

    /**
     * @param $origin
     * @param $letters
     * @param $lettersCount
     * @return Entity\Client
     * @throws \Exception
     */
    private function generateClientDuplicate(Entity\Client $origin, $isExactDuplicate, $letters, $lettersCount): Entity\Client
    {
        $mutationLevel = rand(1, 100);

        $passportNumber = intval($origin->getPassportNumber());
        if (!$isExactDuplicate && $mutationLevel > 90) {
            $passportNumber = intval($origin->getPassportNumber()) + pow(rand(1, 9), rand(0, 5));

            if ($passportNumber > 999999) {
                $passportNumber = $passportNumber - 1000000;
            }
        }

        $passportSeries = intval($origin->getPassportSeries());
        if (!$isExactDuplicate && $mutationLevel > 80) {
            $passportSeries = intval($origin->getPassportSeries()) + pow(rand(1, 9), rand(0, 3));

            if ($passportSeries > 9999) {
                $passportSeries = $passportSeries - 10000;
            }
        }

        $birthDate = clone $origin->getBirthDate();
        if (!$isExactDuplicate && $mutationLevel > 60) {
            $dateFactor = rand(1, 3);

            switch ($dateFactor) {
                case 1:
                    $intervalSpec = "P" . rand(1, 3) . "Y";
                    break;

                case 2:
                    $intervalSpec = "P" . rand(1, 12) . "M";
                    break;

                case 3:
                    $intervalSpec = "P" . rand(1, 30) . "D";
                    break;

                default:
                    $intervalSpec = "P1D";
            }

            $birthDate->add(new \DateInterval($intervalSpec));
        }

        $originalFullName = mb_convert_encoding($origin->getFullName(), "UTF-8");
        $fullName = $originalFullName;

        if(!$isExactDuplicate){
            $charsToReplaceCount = rand(1, 3);
            $nameLength = mb_strlen($originalFullName);
            $positions = [];

            do {
                $newPosition = rand(0, $nameLength - 1);

                if (mb_substr($originalFullName, $newPosition, 1) === " ") {
                    continue;
                }

                $alreadyHavePosition = false;

                foreach ($positions as $p) {
                    if ($p === $newPosition) {
                        $alreadyHavePosition = true;
                        break;
                    }
                }

                if (!$alreadyHavePosition) {
                    $positions[] = $newPosition;
                }
            } while (count($positions) < $charsToReplaceCount);


            foreach ($positions as $p) {
                $l = mb_substr($letters, rand(0, $lettersCount - 1), 1);
                $fullName = mb_substr($fullName, 0, $p) . $l . mb_substr($fullName, $p + 1);
            }
        }

        $client = new Entity\Client;
        $client->setFullName($fullName);
        $client->setBirthDate($birthDate);
        $client->setPassportSeries(sprintf("%'.04d", $passportSeries));
        $client->setPassportNumber(sprintf("%'.06d", $passportNumber));

        return $client;
    }
}
