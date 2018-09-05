<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 06.09.18
 * Time: 1:05
 */

namespace App\Abstractions\Services;

interface RandomDateServiceInterface
{
    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return \DateTime
     */
    public function fromRange(\DateTime $start, \DateTime $end);
}