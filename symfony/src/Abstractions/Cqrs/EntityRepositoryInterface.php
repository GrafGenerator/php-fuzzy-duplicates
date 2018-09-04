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
}