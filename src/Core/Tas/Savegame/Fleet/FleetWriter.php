<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       23/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

declare(strict_types=1);

namespace App\Core\Tas\Savegame\Fleet;

use App\Core\File\TextFileWriter;
use App\Core\Tas\Savegame\Savegame;

class FleetWriter
{
    private TextFileWriter $textFileWriter;

    public function __construct(TextFileWriter $textFileWriter)
    {
        $this->textFileWriter = $textFileWriter;
    }

    public function update(Savegame $savegame, string $side): void
    {
        $path = $savegame->getPath() . DIRECTORY_SEPARATOR . $side . 'Ships.cfg';
        $content = '';

        foreach ($savegame->getNavalData()->getFleets($side) as $fleet) {
            // Basic info of the TF
            $content .= '[' . $fleet->getId() . ']' . PHP_EOL;
            $content .= 'NAME=' . $fleet->getName() . PHP_EOL;
            $content .= 'PROB=' . $fleet->getProb() . PHP_EOL;
            $content .= 'SPEED=' . $fleet->getSpeed() . PHP_EOL;
            $content .= 'MISSION=' . $fleet->getMission() . PHP_EOL;
            $content .= 'CASECOUNT=' . $fleet->getCaseCount() . PHP_EOL;
            $content .= 'CASE1' . PHP_EOL;
            $content .= 'FORMATIONHEADING=0' . PHP_EOL;
            $content .= 'LL=' . $fleet->getLl() . PHP_EOL;

            $content .= $this->addWaypoints($fleet);

            $content .= 'ENDMISSION' . PHP_EOL;
            $content .= PHP_EOL;

            // Divisions
            $content .= $this->addDivisions($fleet);
        } // End loop on the fleets

        // Ships in Port
        $content .= $this->addShipsInPort($savegame, $side);

        $this->textFileWriter->writeMultilineFromString($path, $content);
    }

    private function addShipsInPort(Savegame $savegame, string $side): string
    {
        $content = '[SHIPS IN PORT]' . PHP_EOL;
        $content .= PHP_EOL;

        foreach ($savegame->getNavalData()->getShipsInPort($side) as $ship => $shipData) {
            $content .= 'NAME=' . $ship . PHP_EOL;
            foreach ($shipData as $key => $value) {
                $content .= $key . '=' . $value . PHP_EOL;
            }
            $content .= PHP_EOL;
        }

        return $content;
    }

    private function addDivisions(TaskForce $fleet): string
    {
        $content = '';

        foreach ($fleet->getFleetData()->getDivisions() as $divisionName => $division) {
            $content .= '[' . $divisionName . ']' . PHP_EOL;
            $content .= 'FORMATION=Column' . PHP_EOL;
            $content .= PHP_EOL;

            foreach ($division as $ship => $shipData) {
                $content .= 'NAME=' . $ship . PHP_EOL;
                foreach ($shipData as $key => $value) {
                    $content .= $key . '=' . $value . PHP_EOL;
                }
                $content .= PHP_EOL;
            }
        }

        return $content;
    }

    private function addWaypoints(TaskForce $fleet): string
    {
        $content = '';
        $wpCount = count($fleet->getWaypoints());

        foreach ($fleet->getWaypoints() as $waypoint) {
            if ($wpCount > 1) {
                $prefix = 'WP';
            } else {
                $prefix = 'TP';
            }
            $content .= $prefix . '=' . $waypoint . PHP_EOL;

            $wpCount--;
        }

        return $content;
    }
}
