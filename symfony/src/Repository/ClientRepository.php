<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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


    /*
     * fuzzy sql:
     * select
     *   a.id
     *  ,b.id
     *  ,CONCAT(a.full_name, ' ',  a.birth_date, ' ', a.passport_series, ' ',  a.passport_number)
     *  ,CONCAT(b.full_name, ' ',  b.birth_date, ' ', b.passport_series, ' ',  b.passport_number)
     * from client a
     * join client b
     *   on b.id > a.id
     *     and ssdeep_fuzzy_compare(
     *        ssdeep_fuzzy_hash(CONCAT(a.full_name, ' ',  a.birth_date, ' ', a.passport_series, ' ',  a.passport_number)),
     *        ssdeep_fuzzy_hash(CONCAT(b.full_name, ' ',  b.birth_date, ' ', b.passport_series, ' ',  b.passport_number))
     *     ) > 32
     * order by
     *   ssdeep_fuzzy_compare(
     *        ssdeep_fuzzy_hash(CONCAT(a.full_name, ' ',  a.birth_date, ' ', a.passport_series, ' ',  a.passport_number)),
     *        ssdeep_fuzzy_hash(CONCAT(b.full_name, ' ',  b.birth_date, ' ', b.passport_series, ' ',  b.passport_number))
     *   ) DESC;
*/

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
