<?php

namespace App\Cqrs\Readers;

use App\Abstractions\Cqrs\EntityReadersFactoryInterface;
use App\Abstractions\Readers\StatisticsHelperReaderInterface;
use App\Entity\StatisticsHelper;

class StatisticsHelperReader implements StatisticsHelperReaderInterface
{
    private $entityReader;

    public function __construct(EntityReadersFactoryInterface $readersFactory)
    {
        $this->entityReader = $readersFactory->get(StatisticsHelper::class);
    }

    /**
     * @return array
     */
    public function getStatistics() : array {
        /* @var StatisticsHelper[] $statistics */
        $statistics = $this->entityReader->getRepository()->findAll();

        $clientsCount = -1;
        $duplicatesCount = -1;

        foreach ($statistics as $s){
            switch ($s->getName()){
                case 'total':
                    $clientsCount = intval($s->getValue());
                    break;

                case 'duplicates':
                    $duplicatesCount = intval($s->getValue());
                    break;
            }
        }

        if($clientsCount === -1 || $duplicatesCount === -1){
            throw new \RuntimeException("Incorrect statistics helpers.");
        }

        return array($clientsCount, $duplicatesCount);
    }
}
