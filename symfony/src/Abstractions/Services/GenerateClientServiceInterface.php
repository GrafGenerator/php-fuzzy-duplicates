<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 06.09.18
 * Time: 1:06
 */

namespace App\Abstractions\Services;

use App\Entity\Client;

interface GenerateClientServiceInterface
{
    /**
     * @param array $names
     * @param $startBirthDate
     * @param $endBirthDate
     * @return Client
     */
    public function generate(array $names, $startBirthDate, $endBirthDate): Client;
}