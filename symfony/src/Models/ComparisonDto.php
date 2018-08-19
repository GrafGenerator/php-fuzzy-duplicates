<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 18.08.18
 * Time: 23:44
 */

namespace App\Models;


class ComparisonDto
{
    private $id1;
    private $id2;
    private $hash1;
    private $hash2;

    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    public function getId1()
    {
        return $this->id1;
    }

    /**
     * @return mixed
     */
    public function getId2()
    {
        return $this->id2;
    }

    /**
     * @return mixed
     */
    public function getHash1()
    {
        return $this->hash1;
    }

    /**
     * @return mixed
     */
    public function getHash2()
    {
        return $this->hash2;
    }
}