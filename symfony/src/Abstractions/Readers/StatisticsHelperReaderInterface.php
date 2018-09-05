<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 06.09.18
 * Time: 1:53
 */

namespace App\Abstractions\Readers;

interface StatisticsHelperReaderInterface
{
    /**
     * @return array
     */
    public function getStatistics(): array;
}