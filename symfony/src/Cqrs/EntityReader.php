<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 05.09.18
 * Time: 22:36
 */

namespace App\Cqrs;


use App\Abstractions\Cqrs\EntityReaderInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class EntityReader
 * Place for common readers functionality
 * @package App\Cqrs
 */
final class EntityReader implements EntityReaderInterface
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var string
     */
    private $className;

    public function __construct(EntityManagerInterface $entityManager, string $className)
    {
        $this->entityManager = $entityManager;
        $this->repository = $this->entityManager->getRepository($className);
        $this->className = $className;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->repository;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }


}