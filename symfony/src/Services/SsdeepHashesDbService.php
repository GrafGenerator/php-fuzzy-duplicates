<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 06.09.18
 * Time: 1:58
 */

namespace App\Services;


use App\Abstractions\Cqrs\SqlExecutorInterface;
use App\Abstractions\Services\SsdeepHashesDbServiceInterface;

final class SsdeepHashesDbService implements SsdeepHashesDbServiceInterface
{
    private $setupSql = '
            DROP TABLE IF EXISTS hashes;
            CREATE TABLE hashes(id int not null primary key, hash varchar(640) not null, significantLength int not null);
            INSERT INTO hashes
            SELECT
              id,
              ssdeep_fuzzy_hash(CONCAT(full_name, birth_date, passport_series, passport_number)),
              LENGTH(full_name)
            FROM client;';

    private $tearDownSql = 'DROP TABLE IF EXISTS hashes;';

    /**
     * @var SqlExecutorInterface
     */
    private $sqlExecutor;

    public function __construct(SqlExecutorInterface $sqlExecutor)
    {
        $this->sqlExecutor = $sqlExecutor;
    }

    public function setup() {
        $this->sqlExecutor->execute($this->setupSql);
    }

    public function tearDown() {
        $this->sqlExecutor->execute($this->tearDownSql);
    }
}