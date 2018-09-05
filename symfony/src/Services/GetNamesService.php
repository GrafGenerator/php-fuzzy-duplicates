<?php
/**
 * Created by PhpStorm.
 * User: grafgenerator
 * Date: 06.09.18
 * Time: 0:55
 */

namespace App\Services;


use App\Abstractions\Services\GetNamesServiceInterface;
use Symfony\Component\Finder\Finder;

class GetNamesService implements GetNamesServiceInterface
{
    public function getNames() : array{
        $finder = new Finder();
        $finder->files()->in(__DIR__ . "/../Data");

        $names = [
            'male' => [
                'name' => null,
                'surname' => null,
                'patronymic' => null,
            ],
            'female' => [
                'name' => null,
                'surname' => null,
                'patronymic' => null,
            ],
        ];

        foreach ($finder as $file) {
            $fileName = $file->getBasename("." . $file->getExtension());
            $parts = explode("-", $fileName);
            $namesInFile = explode("\n", $file->getContents());
            $names[$parts[0]][$parts[1]] = array_values($namesInFile);
        }

        return $names;
    }
}