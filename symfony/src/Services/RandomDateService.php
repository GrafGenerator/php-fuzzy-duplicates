<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 06.09.18
 * Time: 1:03
 */

namespace App\Services;


use App\Abstractions\Services\RandomDateServiceInterface;

class RandomDateService implements RandomDateServiceInterface
{
    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @return \DateTime
     */
    public function fromRange(\DateTime $start, \DateTime $end) {
        $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());
        $randomDate = new \DateTime();
        $randomDate->setTimestamp($randomTimestamp);
        return $randomDate;
    }
}