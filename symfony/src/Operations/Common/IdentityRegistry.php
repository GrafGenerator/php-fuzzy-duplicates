<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 03.09.18
 * Time: 2:21
 */

namespace App\Operations\Common;


final class IdentityRegistry
{
    private static $registry = null;

    public static function getRegistry() :IdentityRegistryImpl {
        if(IdentityRegistry::$registry == null) {
            IdentityRegistry::$registry = new IdentityRegistryImpl;
        }

        return IdentityRegistry::$registry;
    }
}