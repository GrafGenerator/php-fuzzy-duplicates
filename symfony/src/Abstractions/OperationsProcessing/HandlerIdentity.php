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

    protected function __construct(int $id, string $description, string $handlerClass)
    {
        $this->id = $id;
        $this->description = $description;
        $this->handlerClass = $handlerClass;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getHandlerClass()
    {
        return $this->handlerClass;
    }

    public static function create(int $id, string $description, string $handlerClass) : HandlerIdentity {
        return new HandlerIdentity($id, $description, $handlerClass);
    }
}