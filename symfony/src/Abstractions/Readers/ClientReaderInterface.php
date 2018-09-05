<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 06.09.18
 * Time: 2:03
 */

namespace App\Abstractions\Readers;

use App\Entity\Client;

interface ClientReaderInterface
{
    /**
     * @param int $count
     * @return Client[]
     */
    public function getFirstN(int $count);

    /**
     * @param $matchThreshold
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function fetchDuplicatesIds($matchThreshold);

    /**
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getHashesToCompare();

    /**
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function getHashesToCompareIterable(): \Doctrine\DBAL\Driver\Statement;
}