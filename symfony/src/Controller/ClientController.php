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
        $str1 = "Даниил Михайлович Парчевский";
        $str2 = "Данил Михайлович Парчевский";
        $str3 = "Даниил Михалович Парчевский";
        $str4 = "Данила Михалович Парчевский";

        $hash1 = ssdeep_fuzzy_hash($str1);
        $hash2 = ssdeep_fuzzy_hash($str2);
        $hash3 = ssdeep_fuzzy_hash($str3);
        $hash4 = ssdeep_fuzzy_hash($str4);

        $match11 = ssdeep_fuzzy_compare($hash1, $hash1);
        $match12 = ssdeep_fuzzy_compare($hash1, $hash2);
        $match13 = ssdeep_fuzzy_compare($hash1, $hash3);
        $match14 = ssdeep_fuzzy_compare($hash1, $hash4);

        return $this->json([
            'hashes' => [
                $hash1,
                $hash2,
                $hash3,
                $hash4,
            ],
            'comparison' => [
                $match11,
                $match12,
                $match13,
                $match14,
            ],
        ]);
    }

    public function generateDb(Request $request){
        $names = $this->getNames();
        $doctrine = $this->getDoctrine();

        $this->clearAllClients($doctrine);

        $totalCount = $request->get("clients") ?? 10000000;
        $duplicatesCount = $request->get("intendedDuplicates") ?? 100;

        $result = $this->generateClients($doctrine, $totalCount, $duplicatesCount, $names);

        return $this->json($result);
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

        $batchSize = 500;

        for ($i = 0; $i < $fairCount; $i++){
            $sexSelector = rand(1, 100) > 50 ? "male" : "female";
            $name = $names[$sexSelector]["name"][array_rand($names[$sexSelector]["name"])];
            $surname = $names[$sexSelector]["surname"][array_rand($names[$sexSelector]["surname"])];
            $patronymic = $names[$sexSelector]["patronymic"][array_rand($names[$sexSelector]["patronymic"])];
            $birthDate = $this->randomDateInRange($startBirthDate, $endBirthDate);
            $passportSeries = rand(5000, 6999);
            $passportNumber = rand(1, 999999);

            $client = new Entity\Client();
            $client->setFullName($surname . " " . $name . " " . $patronymic);
            $client->setBirthDate($birthDate);
            $client->setPassportSeries(sprintf("%'.04d", $passportSeries));
            $client->setPassportNumber(sprintf("%'.06d", $passportNumber));

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


        $letters = mb_convert_encoding("абвгдеёжзийклмнопрстуфхцчшщъыьэюя", "UTF-8");
        $lettersCount = mb_strlen($letters);
        $repo = $doctrine->getRepository(Entity\Client::class);
        $origins = $repo->getFirstN($duplicatesCount);
        $hist = [];
        $hist[] = $letters;

        foreach ($origins as $origin){
            $mutationLevel = rand(1, 100);

            $passportNumber = intval($origin->getPassportNumber());
            if($mutationLevel > 95){
                $passportNumber = intval($origin->getPassportNumber()) + pow(rand(1,9), rand(0, 5));

                if($passportNumber > 999999){
                    $passportNumber = $passportNumber - 1000000;
                }
            }

            $passportSeries = intval($origin->getPassportSeries());
            if($mutationLevel > 90){
                $passportSeries = intval($origin->getPassportSeries()) + pow(rand(1,9), rand(0, 3));

                if($passportSeries > 9999){
                    $passportSeries = $passportSeries - 10000;
                }
            }

            $birthDate = $origin->getBirthDate();
            if($mutationLevel > 70) {
                $dateFactor = rand(1,3);

                switch ($dateFactor){
                    case 1:
                        $intervalSpec = "P" . rand(1,3) . "Y";
                        break;

                    case 2:
                        $intervalSpec = "P" . rand(1,12) . "M";
                        break;

                    case 3:
                        $intervalSpec = "P" . rand(1,30) . "D";
                        break;

                    default:
                        $intervalSpec = "P1D";
                }

                $birthDate = $origin->getBirthDate()->add(new \DateInterval($intervalSpec));
            }

            $originalFullName = mb_convert_encoding($origin->getFullName(), "UTF-8");
            $charsToReplaceCount = rand(1, 3);
            $nameLength = mb_strlen($originalFullName);
            $positions = [];

            do {
                $newPosition = rand(0, $nameLength - 1);

                if(mb_substr($originalFullName, $newPosition, 1) === " ") {
                    continue;
                }

                $alreadyHavePosition = false;

                foreach ($positions as $p){
                    if($p === $newPosition){
                        $alreadyHavePosition = true;
                        break;
                    }
                }

                if(!$alreadyHavePosition){
                    $positions[] = $newPosition;
                }
            }
            while (count($positions) < $charsToReplaceCount);

            $fullName = $originalFullName;
            foreach ($positions as $p){
                $l = mb_substr($letters, rand(0, $lettersCount - 1), 1);
                $hist[] = $l;
                $hist[] = $fullName;
                $fullName = mb_substr($fullName, 0, $p) . $l . mb_substr($fullName, $p + 1);
                // $fullName = substr_replace($fullName, $l, $p * 2, 2);
                $hist[] = $fullName;
            }

            $client = new Entity\Client();
            $client->setFullName($fullName);
            $client->setBirthDate($birthDate);
            $client->setPassportSeries(sprintf("%'.04d", $passportSeries));
            $client->setPassportNumber(sprintf("%'.06d", $passportNumber));

            $em->persist($client);
        }

        $em->flush();
        $em->clear();

        return $hist;
    }

    private function randomDateInRange(DateTime $start, DateTime $end) {
        $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());
        $randomDate = new DateTime();
        $randomDate->setTimestamp($randomTimestamp);
        return $randomDate;
    }
}
