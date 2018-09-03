<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 03.09.18
 * Time: 22:56
 */

namespace App\Model\Operations\Command;


use App\Abstractions\OperationsProcessing\OperationCommandInterface;

class TestOperationCommand implements OperationCommandInterface
{
    /**
     * @var int
     */
    private $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }
}