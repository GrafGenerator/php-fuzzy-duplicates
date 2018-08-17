<?php

namespace App\Repository;

use App\Entity\StatisticsHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use http\Exception\RuntimeException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StatisticsHelper|null find($id, $lockMode = null, $lockVersion = null)
 * @method StatisticsHelper|null findOneBy(array $criteria, array $orderBy = null)
 * @method StatisticsHelper[]    findAll()
 * @method StatisticsHelper[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatisticsHelperRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StatisticsHelper::class);
    }

    public function clearStatistics(){
        $conn = $this->getEntityManager()->getConnection();

        $sql = 'TRUNCATE TABLE statistics_helper';
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }

    public function setStatistics($clientsCount, $duplicatesCount){
        $em = $this->getEntityManager();

        $clientsCountStatistics = new StatisticsHelper;
        $clientsCountStatistics->setName('total');
        $clientsCountStatistics->setValue($clientsCount);

        $duplicatesCountStatistics = new StatisticsHelper;
        $duplicatesCountStatistics->setName('duplicates');
        $duplicatesCountStatistics->setValue($duplicatesCount);

        $em->persist($clientsCountStatistics);
        $em->persist($duplicatesCountStatistics);

        $em->flush();
    }

    public function getStatistics() : array {
        $statistics = $this->findAll();

        $clientsCount = -1;
        $duplicatesCount = -1;

        foreach ($statistics as $s){
            switch ($s->getName()){
                case 'total':
                    $clientsCount = intval($s->getValue());
                    break;

                case 'duplicates':
                    $duplicatesCount = intval($s->getValue());
                    break;
            }
        }

        if($clientsCount === -1 || $duplicatesCount === -1){
            throw new RuntimeException("Incorrect statistics helpers.");
        }

        return array($clientsCount, $duplicatesCount);
    }
//    /**
//     * @return StatisticsHelper[] Returns an array of StatisticsHelper objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?StatisticsHelper
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
