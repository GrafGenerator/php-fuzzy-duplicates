<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 03.09.18
 * Time: 1:30
 */

namespace App\OperationsProcessing;


use App\Abstractions\OperationsProcessing\HandlerIdentity;
use App\Abstractions\OperationsProcessing\OperationHandlerInterface;
use App\Abstractions\OperationsProcessing\OperationHandlerServiceLocatorInterface;
use App\Abstractions\OperationsProcessing\OperationHandlersFactoryInterface;
use InvalidArgumentException;

class OperationHandlersFactory implements
    OperationHandlersFactoryInterface,
    OperationHandlerServiceLocatorInterface
{
    /**
     * @var OperationHandlerInterface[string]
     */
    private $handlers;

    public function __construct()
    {
        $this->handlers = array();
    }

    public function get(HandlerIdentity $identity): OperationHandlerInterface
    {
        $handlerClass = $identity->getHandlerClass();
        if (array_key_exists($handlerClass, $this->handlers)) {
            return $this->handlers[$handlerClass];
        }

        throw new InvalidArgumentException(sprintf("No handler found for identity %s", $identity->getId()));
    }

    public function registerHandler($handler, string $handlerClass)
    {
        $this->handlers[$handlerClass] = $handler;
    }
}