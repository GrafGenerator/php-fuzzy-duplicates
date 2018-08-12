<?php

namespace App\Controller;

use DateTime;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Entity;

class ClientController extends Controller
{
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

        $this->generateClients($doctrine, $totalCount, $duplicatesCount, $names);

        return $this->json($names);
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

        $fairCount = $totalCount - $duplicatesCount;
        $startBirthDate = DateTime::createFromFormat('d/m/Y', '1/1/1971');
        $endBirthDate = DateTime::createFromFormat('d/m/Y', '1/12/2000');

        $batchSize = 5000;

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

            if (($i % $batchSize) === 0) {
                $em->flush();
                $em->clear();
                // gc_collect_cycles();
            }
        }

        $em->flush();
        $em->clear();
    }

    private function randomDateInRange(DateTime $start, DateTime $end) {
        $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());
        $randomDate = new DateTime();
        $randomDate->setTimestamp($randomTimestamp);
        return $randomDate;
    }
}
