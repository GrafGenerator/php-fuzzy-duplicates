<?php

namespace App\Repository;

use App\Entity\Client;
use App\Models\ComparisonDto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Statement;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function clearClients(){
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'TRUNCATE TABLE client';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }

    public function getFirstN(int $count){
        $dql = '
          SELECT c 
          FROM App\Entity\Client c 
          ORDER BY c.id ASC';
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setMaxResults($count);

        return $query->getResult();
    }

    public function setupHashes(){
        $conn = $this->getEntityManager()->getConnection();

        $createTableSql = '
            DROP TABLE IF EXISTS hashes;
            CREATE TABLE hashes(id int not null primary key, hash varchar(640) not null, significantLength int not null); 
            INSERT INTO hashes 
            SELECT 
              id,
              ssdeep_fuzzy_hash(CONCAT(full_name, birth_date, passport_series, passport_number)),
              LENGTH(full_name)
            FROM client;';
        $createTableStmt = $conn->prepare($createTableSql);
        $createTableStmt->execute();
    }

    public function fetchDuplicatesIds($matchThreshold){
        $conn = $this->getEntityManager()->getConnection();

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

    public function tearDownHashes(){
        $conn = $this->getEntityManager()->getConnection();

        $dropTableSql = 'DROP TABLE IF EXISTS hashes;';
        $dropTableStmt = $conn->prepare($dropTableSql);
        $dropTableStmt->execute();
    }

    public function getHashesToCompare(){
        $conn = $this->getEntityManager()->getConnection();

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

        $query = $em->createQuery('SELECT NEW CustomerDTO(c.name, e.email, a.city) FROM Customer c JOIN c.email e JOIN c.address a');
        $users = $query->getResult(); // array of CustomerDTO
    }

    /**
     * @return \PDOStatement
     */
    public function getHashesToCompareIterable() : \PDOStatement {
        $conn = $this->getEntityManager()->getConnection();
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

//    /**
//     * @return Client[] Returns an array of Client objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Client
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
