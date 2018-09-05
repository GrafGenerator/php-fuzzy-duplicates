<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 05.09.18
 * Time: 21:24
 */

namespace App\Model\Api;


use App\Abstractions\Api\AbstractApiRequest;

final class GenerateDbApiRequest extends AbstractApiRequest
{
    /**
     * @return int
     */
    public function getTotalCount() {
        return $this->getField("clients", 10000000);
    }

    /**
     * @return int
     */
    public function getDuplicatesCount() {
        return $this->getField("intendedDuplicates", 100);
    }
}