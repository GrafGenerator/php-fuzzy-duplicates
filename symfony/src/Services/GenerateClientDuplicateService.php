<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 06.09.18
 * Time: 1:07
 */

namespace App\Services;


use App\Abstractions\Services\GenerateClientDuplicateServiceInterface;
use App\Entity\Client;

class GenerateClientDuplicateService implements GenerateClientDuplicateServiceInterface
{
    private $alphabet = "абвгдеёжзийклмнопрстуфхцчшщъыьэюя";

    /**
     * @param Client $origin
     * @param $isExactDuplicate
     * @return Client
     * @throws \Exception
     */
    public function generate(Client $origin, $isExactDuplicate): Client
    {
        $letters = mb_convert_encoding($this->alphabet, "UTF-8");
        $lettersCount = mb_strlen($letters);

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

        /* @var \DateTime $birthDate */
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

        if (!$isExactDuplicate) {
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

        $client = new Client;
        $client->setFullName($fullName);
        $client->setBirthDate($birthDate);
        $client->setPassportSeries(sprintf("%'.04d", $passportSeries));
        $client->setPassportNumber(sprintf("%'.06d", $passportNumber));

        return $client;
    }
}