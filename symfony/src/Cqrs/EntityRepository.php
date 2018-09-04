<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 04.09.18
 * Time: 21:16
 */

namespace App\Cqrs;


use App\Abstractions\Cqrs\EntityRepositoryInterface;
use Doctrine\Common\Persistence\ObjectManager;

final class EntityRepository implements EntityRepositoryInterface
{
    /**
     * @var ObjectManager
     */
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function add($entity): void
    {
        $this->manager->persist($entity);
    }

    public function update($entity): void
    {
        $this->manager->persist($entity);
    }

    public function delete($entity): void
    {
        $this->manager->remove($entity);
    }
}