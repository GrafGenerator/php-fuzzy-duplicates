<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\ParameterType;
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
            CREATE TABLE hashes(id int not null primary key, hash varchar(640) not null); 
            INSERT INTO hashes SELECT id, ssdeep_fuzzy_hash(CONCAT(full_name, birth_date, passport_series, passport_number)) FROM client;';
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
          ON b.id > a.id
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

    public function getHashes(){
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
          SELECT id, hash
          FROM hashes';
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
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
