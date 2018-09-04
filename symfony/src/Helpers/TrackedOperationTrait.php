<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 04.09.18
 * Time: 21:45
 */

namespace App\Helpers;


trait TrackedOperationTrait
{
    /**
     * @var float
     */
    private $startTime;

    protected function startTracking(): void {
        $this->startTime = microtime(true);
    }

    /**
     * @return float
     */
    protected function getElapsedTime(): float {
        return microtime(true) - $this->startTime;
    }

    /**
     * @return float
     */
    protected function getElapsedTimeAndReset(): float {
        $elapsedTime = $this->getElapsedTime();
        $this->startTime = microtime(true);
        return $elapsedTime;
    }
}