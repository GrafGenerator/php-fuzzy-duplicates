<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 04.09.18
 * Time: 21:41
 */

namespace App\Abstractions\OperationsProcessing;


use App\Abstractions\Cqrs\EntityRepositoryInterface;

interface OperationScopeInterface
{
    /**
     * @return HandlerIdentity
     */
    public function getHandlerIdentity();

    /**
     * @param string $entityClass
     * @return EntityRepositoryInterface
     */
    public function getRepo(string $entityClass);

    /**
     * Get repository for entity that is default for handler of operation scope
     * @return EntityRepositoryInterface
     */
    public function getDefaultRepo();

    /**
     * Final scope completion, includes commit
     */
    public function complete(): void;

    /**
     * Intermediate scope commit
     */
    public function commit(): void;
}