<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 05.09.18
 * Time: 23:59
 */

namespace App\Services;


use App\Abstractions\Cqrs\SqlExecutorInterface;
use App\Abstractions\Services\CleanupDbServiceInterface;
use App\Abstractions\Services\ResetStatisticsServiceInterface;

class CleanupDbService implements CleanupDbServiceInterface
{
    private $resetSql = "TRUNCATE TABLE client";

    /**
     * @var SqlExecutorInterface
     */
    private $sqlExecutor;
    /**
     * @var ResetStatisticsServiceInterface
     */
    private $resetStatisticsService;

    public function __construct(
        SqlExecutorInterface $sqlExecutor,
        ResetStatisticsServiceInterface $resetStatisticsService
    )
    {
        $this->sqlExecutor = $sqlExecutor;
        $this->resetStatisticsService = $resetStatisticsService;
    }

    public function cleanUp(){
        $this->sqlExecutor->execute($this->resetSql);
        $this->resetStatisticsService->reset();
    }
}