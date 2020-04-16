<?php

declare(strict_types=1);
/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       07/04/2020 (dd-mm-YYYY)
 */

namespace App\Tests\Core\Fs\Scenario;

use App\Core\Fs\Scenario\FleetLevelExperienceDetector;
use App\Core\Fs\Scenario\Ship\Ship;
use PHPUnit\Framework\TestCase;

class FleetLevelExperienceDetectorTest extends TestCase
{
    /** @dataProvider detectorProvider */
    public function testNormalDetection($ships, string $expected): void
    {
        $detector = new FleetLevelExperienceDetector();
        static::assertEquals($expected, $detector->getFleetLevel($ships, 'Blue'));
    }

    public function detectorProvider(): array
    {
        return [
            [$this->getScenario(['Veteran', 'Veteran', 'Elite', 'Veteran']), 'Elite'],
            [$this->getScenario(['Veteran', 'Elite', 'Elite', 'Veteran']), 'Elite'],
            [$this->getScenario(['Veteran', 'Veteran', 'Elite', 'Average']), 'Veteran'],
            [$this->getScenario(['Veteran', 'Veteran', 'Elite', 'Green']), 'Veteran'],
            [$this->getScenario(['Veteran', 'Veteran', 'Green', 'Green']), 'Average'],
            [$this->getScenario(['Elite', 'Green', 'Green', 'Green']), 'Average'],
            [$this->getScenario(['Average', 'Green', 'Green', 'Green']), 'Green'],
            // These ones are for infection
            [$this->getScenario(['Green', 'Green', 'Green', 'Green']), 'Green'],
            [$this->getScenario(['Average', 'Average', 'Average', 'Average']), 'Average'],
            [$this->getScenario(['Veteran', 'Veteran', 'Veteran', 'Veteran']), 'Veteran'],
            [$this->getScenario(['Elite', 'Elite', 'Elite', 'Elite']), 'Elite'],
            [$this->getScenario(['Green', 'Veteran', 'Elite']), 'Veteran'],
            [$this->getScenario(['Green', 'Average']), 'Green'],
        ];
    }

    private function getScenario(array $data): array
    {
        $ships = [];
        $dummyData = ['NAME' => 'Foo', 'SHORTNAME' => 'Fo', 'TYPE' => 'BC', 'CLASS' => 'Bar'];
        $levels = ['Green', 'Average', 'Veteran'];

        foreach ($data as $level) {
            $ships[] = (new Ship($dummyData))
                ->setSide('Blue')
                ->setCrewFatigue('Normal')
                ->setCrewQuality($level);
        }

        $ships[] = (new Ship($dummyData))
            ->setSide('Red')
            ->setCrewFatigue('Normal')
            ->setCrewQuality($levels[array_rand($levels)]);

        $ships[] = (new Ship($dummyData))
            ->setSide('Red')
            ->setCrewFatigue('Normal')
            ->setCrewQuality($levels[array_rand($levels)]);

        return $ships;
    }
}
