<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 06.09.18
 * Time: 9:51
 */

namespace App\Model\Operations\Result;


class FetchDuplicatesOperationResult
{
    /**
     * @var float
     */
    public $buildHashesTime;

    /**
     * @var float
     */
    public $duplicatesSearchTime;

    /**
     * @var int
     */
    public $duplicatesCount;

    /**
     * @var int
     */
    public $exactDuplicatesCount;

    /**
     * @var int
     */
    public $intendedDuplicatesCount;

    public $result;

    /**
     * FetchDuplicatesOperationResult constructor.
     * @param float $buildHashesTime
     * @param float $duplicatesSearchTime
     * @param int $duplicatesCount
     * @param int $exactDuplicatesCount
     * @param int $intendedDuplicatesCount
     * @param $result
     */
    public function __construct(float $buildHashesTime, float $duplicatesSearchTime, int $duplicatesCount, int $exactDuplicatesCount, int $intendedDuplicatesCount, $result)
    {
        $this->buildHashesTime = $buildHashesTime;
        $this->duplicatesSearchTime = $duplicatesSearchTime;
        $this->duplicatesCount = $duplicatesCount;
        $this->exactDuplicatesCount = $exactDuplicatesCount;
        $this->intendedDuplicatesCount = $intendedDuplicatesCount;
        $this->result = $result;
    }


}