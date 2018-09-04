<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 03.09.18
 * Time: 2:15
 */

namespace App\Abstractions\OperationsProcessing;


final class HandlerIdentity
{
    private $id;
    private $description;
    private $handlerClass;
    private $entityClass;

    protected function __construct(int $id, string $description, string $handlerClass, string $entityClass = null)
    {
        $this->id = $id;
        $this->description = $description;
        $this->handlerClass = $handlerClass;
        $this->entityClass = $entityClass;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getHandlerClass(): string
    {
        return $this->handlerClass;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public static function createForEntity(int $id, string $description, string $handlerClass, string $entityClass) : HandlerIdentity {
        return new HandlerIdentity($id, $description, $handlerClass, $entityClass);
    }

    public static function create(int $id, string $description, string $handlerClass) : HandlerIdentity {
        return new HandlerIdentity($id, $description, $handlerClass);
    }
}