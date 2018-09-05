<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 03.09.18
 * Time: 23:00
 */

namespace App\Model\Operations\Result;


class TestOperationResult
{
    /**
     * @var int
     */
    public $value;

    /**
     * @var float
     */
    public $timeSpent;

    /**
     * TestOperationResult constructor.
     * @param int $value
     */
    public function __construct(int $value, float $timeSpent)
    {
        $this->value = $value;
        $this->timeSpent = $timeSpent;
    }


}