<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 03.09.18
 * Time: 2:31
 */

namespace App\Operations\Common;


use App\Abstractions\OperationsProcessing\HandlerIdentity;
use App\Operations\TestOperationHandler;

final class IdentityRegistryImpl
{
    public function __construct()
    {
        $this->test = HandlerIdentity::create(1, "test operation", TestOperationHandler::class);
    }

    private $test;

    /**
     * @return HandlerIdentity
     */
    public function getTest()
    {
        return $this->test;
    }
}