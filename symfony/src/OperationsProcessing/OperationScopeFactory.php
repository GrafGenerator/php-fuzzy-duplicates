<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 04.09.18
 * Time: 21:45
 */

namespace App\OperationsProcessing;


use App\Abstractions\OperationsProcessing\HandlerIdentity;
use App\Abstractions\OperationsProcessing\OperationScopeFactoryInterface;
use App\Abstractions\OperationsProcessing\OperationScopeInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

final class OperationScopeFactory implements OperationScopeFactoryInterface
{

    /**
     * @var RegistryInterface
     */
    private $managerRegistry;

    public function __construct(RegistryInterface $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param HandlerIdentity $handlerIdentity
     * @return OperationScopeInterface
     */
    public function for(HandlerIdentity $handlerIdentity)
    {
        $scope = new OperationScope($this->managerRegistry, $handlerIdentity);
        return $scope;
    }
}