<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 05.09.18
 * Time: 23:55
 */

namespace App\Model\Operations\Result;


class GenerateDbOperationResult
{
    /**
     * @var float
     */
    public $executionTime;
    /**
     * @var int
     */
    public $exactDuplicatesCount;
    /**
     * @var array
     */
    public $duplicatePairs;

    public function __construct(float $executionTime, int $exactDuplicatesCount, array $duplicatePairs)
    {
        $this->executionTime = $executionTime;
        $this->exactDuplicatesCount = $exactDuplicatesCount;
        $this->duplicatePairs = $duplicatePairs;
    }


}