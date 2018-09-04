<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 04.09.18
 * Time: 23:44
 */

namespace App\Cqrs;


use App\Abstractions\Cqrs\SqlExecutorInterface;
use Doctrine\DBAL\Connection;
use Symfony\Bridge\Doctrine\RegistryInterface;

final class SqlExecutor implements SqlExecutorInterface
{

    /**
     * @var RegistryInterface
     */
    private $managerRegistry;

    public function __construct(RegistryInterface $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function execute(string $sql, $params = null): void
    {
        /* @var Connection $conn */
        $conn = $this->managerRegistry->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }
}