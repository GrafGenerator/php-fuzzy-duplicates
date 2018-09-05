<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 04.09.18
 * Time: 21:38
 */

namespace App\OperationsProcessing;


use App\Abstractions\Cqrs\EntityRepositoryInterface;
use App\Abstractions\OperationsProcessing\HandlerIdentity;
use App\Abstractions\OperationsProcessing\OperationScopeInterface;
use App\Cqrs\EntityRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\RegistryInterface;

class OperationScope implements OperationScopeInterface
{
    /**
     * @var RegistryInterface
     */
    private $managerRegistry;
    /**
     * @var HandlerIdentity
     */
    private $handlerIdentity;

    /**
     * @var [string]ObjectManager
     */
    private $managers;

    public function __construct(RegistryInterface $managerRegistry, HandlerIdentity $handlerIdentity)
    {
        $this->managerRegistry = $managerRegistry;
        $this->handlerIdentity = $handlerIdentity;
        $this->managers = array();

        $this->managerRegistry->getConnection()->getConfiguration()->setSQLLogger(null);
    }

    /**
     * @param string $entityClass
     * @return EntityRepositoryInterface
     */
    public function getRepo(string $entityClass)
    {
        return $this->getRepository($entityClass);
    }

    /**
     * @return EntityRepositoryInterface
     * @throws \Exception
     */
    public function getDefaultRepo()
    {
        $defaultEntityClass = $this->handlerIdentity->getEntityClass();

        if($defaultEntityClass == null) {
            throw new \Exception(sprintf(
                "Default entity not configured for handler id %s, '%s'",
                $this->handlerIdentity->getId(),
                $this->handlerIdentity->getDescription()
            ));
        }

        return $this->getRepository($defaultEntityClass);
    }

    /**
     * @return HandlerIdentity
     */
    public function getHandlerIdentity()
    {
        return $this->handlerIdentity;
    }

    public function complete(): void
    {
        $this->commit();
        // do another completion routines here
    }

    /**
     * @param string $entityClass
     * @return EntityRepositoryInterface
     */
    private function getRepository(string $entityClass){
        /* @var ObjectManager $objectManager */
        $objectManager = null;

        if (array_key_exists($entityClass, $this->managers)) {
            $objectManager = $this->managers[$entityClass];
        }
        else {
            $objectManager = $this->managerRegistry->getManagerForClass($entityClass);
            $this->managers[$entityClass] = $objectManager;
        }

        return new EntityRepository($objectManager);
    }

    /**
     * Intermediate scope commit
     */
    public function commit(): void
    {
        /* @var ObjectManager $manager */
        foreach ($this->managers as $manager){
            $manager->flush();
        }
    }
}