<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 03.09.18
 * Time: 1:05
 */

namespace App\Abstractions\OperationsProcessing;


interface OperationResultInterface
{
    public function getIsSuccessful(): bool;

    /**
     * @return mixed
     */
    public function getResult();
}