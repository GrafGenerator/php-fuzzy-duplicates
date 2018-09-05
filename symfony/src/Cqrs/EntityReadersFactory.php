<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 05.09.18
 * Time: 22:57
 */

namespace App\Cqrs;


use App\Abstractions\Cqrs\EntityReaderInterface;
use App\Abstractions\Cqrs\EntityReadersFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final class EntityReadersFactory implements EntityReadersFactoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * EntityReadersFactory constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $className
     * @return EntityReaderInterface
     */
    public function get(string $className)
    {
        $reader = new EntityReader($this->entityManager, $className);
        return $reader;
    }
}