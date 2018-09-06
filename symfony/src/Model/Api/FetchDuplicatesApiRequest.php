<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 05.09.18
 * Time: 21:24
 */

namespace App\Model\Api;


use App\Abstractions\Api\AbstractApiRequest;

final class FetchDuplicatesApiRequest extends AbstractApiRequest
{
    /**
     * @return int
     */
    public function getMatchThreshold() {
        return $this->getField("matchThreshold", 90);
    }
}