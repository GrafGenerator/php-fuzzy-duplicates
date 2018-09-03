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
     * TestOperationResult constructor.
     * @param int $value
     */
    public function __construct(int $value)
    {
        $this->value = $value;
    }


}