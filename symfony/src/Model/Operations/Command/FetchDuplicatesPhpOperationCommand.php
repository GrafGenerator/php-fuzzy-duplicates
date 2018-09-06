<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 06.09.18
 * Time: 9:49
 */

namespace App\Model\Operations\Command;


use App\Abstractions\OperationsProcessing\OperationCommandInterface;

class FetchDuplicatesPhpOperationCommand implements OperationCommandInterface
{
    /**
     * @var int
     */
    private $matchThreshold;

    /**
     * FetchDuplicatesSqlOperationCommand constructor.
     * @param int $matchThreshold
     */
    public function __construct(int $matchThreshold)
    {
        $this->matchThreshold = $matchThreshold;
    }

    /**
     * @return int
     */
    public function getMatchThreshold(): int
    {
        return $this->matchThreshold;
    }
}