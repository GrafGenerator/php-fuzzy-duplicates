<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 06.09.18
 * Time: 2:01
 */

namespace App\Abstractions\Services;

interface SsdeepHashesDbServiceInterface
{
    public function setup();

    public function tearDown();
}