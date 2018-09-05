<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 04.09.18
 * Time: 21:13
 */

namespace App\Abstractions\Cqrs;


interface EntityRepositoryInterface
{
    public function add($entity): void;
    public function update($entity): void;
    public function delete($entity): void;
    public function detach($entity): void;

    /**
     * Very bad code here due to need to clear EntityManager to improve performance.
     * This should be done in another way, but left for now.
     * TODO: refactor this to avoid such method in repo. Probably specialized repo type will suit well.
     * @param $entity
     */
    public function clearManager($entity): void;
}