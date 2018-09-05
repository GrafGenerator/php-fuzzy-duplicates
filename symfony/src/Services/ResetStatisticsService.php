<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 06.09.18
 * Time: 0:46
 */

namespace App\Services;


use App\Abstractions\Cqrs\SqlExecutorInterface;
use App\Abstractions\Services\ResetStatisticsServiceInterface;

final class ResetStatisticsService implements ResetStatisticsServiceInterface
{
    private $resetSql = "TRUNCATE TABLE statistics_helper";

    /**
     * @var SqlExecutorInterface
     */
    private $sqlExecutor;

    /**
     * ResetStatisticsService constructor.
     * @param SqlExecutorInterface $sqlExecutor
     */
    public function __construct(SqlExecutorInterface $sqlExecutor)
    {
        $this->sqlExecutor = $sqlExecutor;
    }

    public function reset() {
        $this->sqlExecutor->execute($this->resetSql);
    }
}