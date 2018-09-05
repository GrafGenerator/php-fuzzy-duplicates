<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 04.09.18
 * Time: 23:25
 */

namespace App\Model\Operations\Command;


use App\Abstractions\OperationsProcessing\OperationCommandInterface;

class GenerateDbOperationCommand implements OperationCommandInterface
{
    /**
     * @var int
     */
    private $totalCount;

    /**
     * @var int
     */
    private $duplicatesCount;

    /**
     * UpdateStatisticsOperationCommand constructor.
     * @param int $totalCount
     * @param int $duplicatesCount
     */
    public function __construct(int $totalCount, int $duplicatesCount)
    {
        $this->totalCount = $totalCount;
        $this->duplicatesCount = $duplicatesCount;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @return int
     */
    public function getDuplicatesCount(): int
    {
        return $this->duplicatesCount;
    }

}