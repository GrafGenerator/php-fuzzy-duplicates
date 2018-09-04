<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 05.09.18
 * Time: 1:34
 */

namespace App\Model\Operations\Common;


final class EmptyOperationResult
{
    public static function create(){
        return new EmptyOperationResult();
    }
}