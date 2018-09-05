<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 05.09.18
 * Time: 21:24
 */

namespace App\Model\Api;


use App\Abstractions\Api\AbstractApiRequest;

final class TestOperationApiRequest extends AbstractApiRequest
{
    /**
     * @return int
     */
    public function getSampleValue() {
        return $this->getField("sample", 1111);
    }
}