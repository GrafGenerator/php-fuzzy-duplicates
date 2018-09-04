<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 04.09.18
 * Time: 23:40
 */

namespace App\Abstractions\Cqrs;


interface SqlExecutorInterface
{
    public function execute(string $sql, $params = null): void;
}