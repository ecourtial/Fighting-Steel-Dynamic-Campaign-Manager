<?php

declare(strict_types=1);

namespace App\Tests\Core\Tas\Savegame\Fleet;

// Note that a big part of the code of the Fleet class is actually tested in the FleetWriterTest

use App\Core\Exception\InvalidInputException;
use App\Core\Tas\Savegame\Fleet\TaskForce;
use PHPUnit\Framework\TestCase;

class FleetTest extends TestCase
{
    public function testUnknownMission(): void
    {
        $fleet = new TaskForce('AH');
        try {
            $fleet->setMission('Scuttle');
            static::fail('Since the mission is not known, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                "Unknown mission type: 'Scuttle'",
                $exception->getMessage()
            );
        }
    }

    public function testBadCaseCount(): void
    {
        $fleet = new TaskForce('OH');
        try {
            $fleet->setCaseCount('2');
            static::fail('Since the case count is invalid, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                'Fleet case count must be 1!',
                $exception->getMessage()
            );
        }
    }

    public function testInvalidProb(): void
    {
        $fleet = new TaskForce('HE');
        try {
            $fleet->setProb('-1');
            static::fail('Since the probability is invalid, an exception was expected');
        } catch (InvalidInputException $exception) {
            static::assertEquals(
                'Fleet prob must be >= 1 and <= 100! -1 given.',
                $exception->getMessage()
            );
        }

        $fleet->setProb('1');
        static::assertEquals(1, $fleet->getProb());
        $fleet->setProb('100');
        static::assertEquals(100, $fleet->getProb());
    }
}
