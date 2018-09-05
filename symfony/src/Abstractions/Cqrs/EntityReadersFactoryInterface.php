<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 05.09.18
 * Time: 22:53
 */

namespace App\Abstractions\Cqrs;


interface EntityReadersFactoryInterface
{
    /**
     * @param string $className
     * @return EntityReaderInterface
     */
    public function get(string $className);
}