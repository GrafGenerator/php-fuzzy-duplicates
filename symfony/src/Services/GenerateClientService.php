<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 06.09.18
 * Time: 1:02
 */

namespace App\Services;


use App\Abstractions\Services\GenerateClientServiceInterface;
use App\Abstractions\Services\RandomDateServiceInterface;
use App\Entity\Client;

class GenerateClientService implements GenerateClientServiceInterface
{
    /**
     * @var RandomDateServiceInterface
     */
    private $randomDateService;

    public function __construct(RandomDateServiceInterface $randomDateService)
    {
        $this->randomDateService = $randomDateService;
    }

    /**
     * @param array $names
     * @param $startBirthDate
     * @param $endBirthDate
     * @return Client
     */
    public function generate(array $names, $startBirthDate, $endBirthDate): Client
    {
        $sexSelector = rand(1, 100) > 50 ? "male" : "female";
        $name = $names[$sexSelector]["name"][array_rand($names[$sexSelector]["name"])];
        $surname = $names[$sexSelector]["surname"][array_rand($names[$sexSelector]["surname"])];
        $patronymic = $names[$sexSelector]["patronymic"][array_rand($names[$sexSelector]["patronymic"])];
        $birthDate = $this->randomDateService->fromRange($startBirthDate, $endBirthDate);
        $passportSeries = rand(5000, 6999);
        $passportNumber = rand(1, 999999);

        $client = new Client;
        $client->setFullName($surname . " " . $name . " " . $patronymic);
        $client->setBirthDate($birthDate);
        $client->setPassportSeries(sprintf("%'.04d", $passportSeries));
        $client->setPassportNumber(sprintf("%'.06d", $passportNumber));

        return $client;
    }
}