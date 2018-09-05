<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 06.09.18
 * Time: 1:12
 */

namespace App\Abstractions\Services;

use App\Entity\Client;

interface GenerateClientDuplicateServiceInterface
{
    /**
     * @param Client $origin
     * @param $isExactDuplicate
     * @return Client
     * @throws \Exception
     */
    public function generate(Client $origin, $isExactDuplicate): Client;
}