<?php

namespace App\Controller;

use DateTime;
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

        $repo->createTempTable();
        $data = $repo->useTempTable();

        return $this->json($data);
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

        $result = $this->generateClients($doctrine, $totalCount, $duplicatesCount, $names);

        $executionTime = microtime(true) - $time_start;

        return $this->json([
            'executionTime' => $executionTime,
            'duplicatePairs' => $result,
        ]);
    }

    /**
     * @Route("/fetchDuplicates", name="fetchDuplicates", methods={"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function fetchDuplicates(Request $request){
        $matchThreshold = $request->get("matchThreshold") ?? 90;
        $doctrine = $this->getDoctrine();
        $repo = $doctrine->getRepository(Entity\Client::class);

        $time_start = microtime(true);
        $repo->setupHashes();
        $buildHashesTime = microtime(true) - $time_start;

        $time_start = microtime(true);
        $result = $repo->fetchDuplicatesIds($matchThreshold);
        $duplicatesSearchTime = microtime(true) - $time_start;

        // $repo->teardownHashes();

        return $this->json([
            'hashBuildTime' => $buildHashesTime,
            'duplicatesSearchTime' => $duplicatesSearchTime,
            'result' => $result
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
        $repo = $doctrine->getRepository(Entity\Client::class);

        $repo->clearClients();
    }

    private function generateClients(\Doctrine\Common\Persistence\ManagerRegistry $doctrine, int $totalCount, int $duplicatesCount, array $names)
    {
        $em = $doctrine->getManager();

        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $fairCount = $totalCount - $duplicatesCount;
        $startBirthDate = DateTime::createFromFormat('d/m/Y', '1/1/1971');
        $endBirthDate = DateTime::createFromFormat('d/m/Y', '1/12/2000');

        // generate new clients in batch
        $batchSize = 500;

        for ($i = 0; $i < $fairCount; $i++){
            $clientDuplicate = $this->generateNewClient($names, $startBirthDate, $endBirthDate);

            $em->persist($clientDuplicate);

            $tempRecords[] = $clientDuplicate;

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

        foreach ($origins as $origin){
            $isExactDuplicate = rand(1, 100) > 50;
            $clientDuplicate = $this->generateClientDuplicate($origin, $isExactDuplicate, $letters, $lettersCount);

            $em->persist($clientDuplicate);

            $duplicatedPairs[] = [
                'isExactDuplicate' => $isExactDuplicate,
                'original' => $origin,
                'duplicate' => $clientDuplicate,
            ];
        }

        $em->flush();
        //$em->clear();

        return $duplicatedPairs;
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
