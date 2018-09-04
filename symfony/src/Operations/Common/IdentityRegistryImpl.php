<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 03.09.18
 * Time: 2:31
 */

namespace App\Operations\Common;


use App\Abstractions\OperationsProcessing\HandlerIdentity;
use App\Entity\StatisticsHelper;
use App\Operations\TestOperationHandler;
use App\Operations\UpdateStatisticsOperationHandler;

final class IdentityRegistryImpl
{
    public function __construct()
    {
        $this->test = HandlerIdentity::create(1, "test operation", TestOperationHandler::class);
        $this->updateStatistics = HandlerIdentity::createForEntity(2, "update db data statistics", UpdateStatisticsOperationHandler::class, StatisticsHelper::class);
    }

    private $test;
    private $updateStatistics;

    /**
     * @return HandlerIdentity
     */
    public function getTest(): HandlerIdentity
    {
        return $this->test;
    }

    /**
     * @return HandlerIdentity
     */
    public function getUpdateStatistics(): HandlerIdentity
    {
        return $this->updateStatistics;
    }
}