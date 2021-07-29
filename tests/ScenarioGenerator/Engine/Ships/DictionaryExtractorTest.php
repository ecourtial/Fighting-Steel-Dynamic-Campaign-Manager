<?php

declare(strict_types=1);

namespace App\Tests\ScenarioGenerator\Engine\Ships;

use App\NameSwitcher\Dictionary\DictionaryReader;
use App\ScenarioGenerator\Engine\Ships\DictionaryExtractor;
use PHPUnit\Framework\TestCase;

class DictionaryExtractorTest extends TestCase
{
    public function testBehavior(): void
    {
        $reader = static::createMock(DictionaryReader::class);
        $path = 'somePath';

        $reader->expects(static::once())
            ->method('extractData')
            ->with($path)
            ->willReturnOnConsecutiveCalls(
                $this->getDicoContent()
            );

        $dictionaryExtractor = new DictionaryExtractor($reader);
        $result = $dictionaryExtractor->getShipDictionary($path);

        $expected = [
            'KM' => [
                'BB' => [
                    'Bismarck' => ['name' => 'Bismarck', 'class' => 'Bismarck', 'type' => 'BB'],
                    'Tirpitz' => ['name' => 'Tirpitz', 'class' => 'Bismarck', 'type' => 'BB'],
                ],
            ],
            'MN' => [
                'BB' => [
                    'Clemenceau' => ['name' => 'Clemenceau', 'class' => 'Richelieu', 'type' => 'BB'],
                ],
            ], ];

        static::assertEquals($expected, $result);

        $result = $dictionaryExtractor->getShipDictionary($path);
        static::assertEquals($expected, $result);
    }

    private function getDicoContent(): \Generator
    {
        $data = [
            ['Name' => 'Bismarck', 'Class' => 'Bismarck', 'Type' => 'BB', 'Navy' => 'KM', 'AvailableForRandom' => 'Yes'],
            ['Name' => 'Clemenceau', 'Class' => 'Richelieu', 'Type' => 'BB', 'Navy' => 'MN', 'AvailableForRandom' => 'Yes'],
            ['Name' => 'Richelieu', 'Class' => 'Richelieu', 'Type' => 'BB', 'Navy' => 'MN', 'AvailableForRandom' => 'No'],
            ['Name' => 'Tirpitz', 'Class' => 'Bismarck', 'Type' => 'BB', 'Navy' => 'KM', 'AvailableForRandom' => 'Yes'],
        ];

        foreach ($data as $element) {
            yield $element;
        }
    }
}
