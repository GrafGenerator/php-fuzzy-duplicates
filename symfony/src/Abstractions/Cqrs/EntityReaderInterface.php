<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 04.09.18
 * Time: 21:14
 */

namespace App\Abstractions\Cqrs;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

interface EntityReaderInterface
{
    /**
     * @return EntityRepository
     */
    public function getRepository(): EntityRepository;

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface;

    /**
     * @return string
     */
    public function getClassName(): string;
}