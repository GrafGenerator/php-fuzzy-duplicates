<?php

namespace App\Cqrs\Readers;

use App\Abstractions\Cqrs\EntityReadersFactoryInterface;
use App\Abstractions\Readers\ClientReaderInterface;
use App\Entity\Client;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;

class ClientReader implements ClientReaderInterface
{
    private $entityReader;

    public function __construct(EntityReadersFactoryInterface $readersFactory)
    {
        $this->entityReader = $readersFactory->get(Client::class);
    }

    public function getFirstN(int $count){
        $dql = '
          SELECT c 
          FROM App\Entity\Client c 
          ORDER BY c.id ASC';
        $query = $this->entityReader->getEntityManager()->createQuery($dql);
        $query->setMaxResults($count);

        return $query->getResult();
    }

    /**
     * @param $matchThreshold
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function fetchDuplicatesIds($matchThreshold){
        $conn = $this->entityReader->getEntityManager()->getConnection();

        $sql = '
          SELECT 
            a.id id1,
            b.id id2,
            ssdeep_fuzzy_compare(a.hash, b.hash) compareResult
          FROM hashes a 
          JOIN hashes b 
          ON 
            b.id > a.id
            AND 
            ABS(a.significantLength - b.significantLength) < 2
          WHERE
            ssdeep_fuzzy_compare(a.hash, b.hash) > :matchThreshold;';
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':matchThreshold', $matchThreshold, ParameterType::INTEGER);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getHashesToCompare(){
        $conn = $this->entityReader->getEntityManager()->getConnection();

        $sql = '
          SELECT 
            a.id id1,
            b.id id2,
            a.hash hash1,
            b.hash hash2
          FROM hashes a 
          JOIN hashes b 
          ON 
            b.id > a.id
            AND 
            ABS(a.significantLength - b.significantLength) < 2';
        $stmt = $conn->prepare($sql);
        $stmt->
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @return \Doctrine\DBAL\Driver\Statement
     */
    public function getHashesToCompareIterable() : \Doctrine\DBAL\Driver\Statement {
        $conn = $this->entityReader->getEntityManager()->getConnection();
        $queryBuilder = $conn->createQueryBuilder();

        $query = $queryBuilder
            ->select("a.id id1", "a.hash hash1", "b.id id2", "b.hash hash2")
            ->from("hashes", "a")
            ->join("a", "hashes", "b", "b.id > a.id")
            ->join("a", "client", "c1", "c1.id = a.id")
            ->join("b", "client", "c2", "c2.id = b.id")
            ->where("ABS(a.significantLength - b.significantLength) < 2")
            ->andWhere("c1.birth_date = c2.birth_date");

        $stmt = $query->execute();
        $stmt->setFetchMode(FetchMode::CUSTOM_OBJECT, 'ComparisonDto');
        return $stmt;
    }
}
