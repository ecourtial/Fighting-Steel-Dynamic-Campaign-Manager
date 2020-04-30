<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       23/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

declare(strict_types=1);

namespace App\Core\Tas\Savegame\Fleet;

use App\Core\File\IniReader;
use App\Core\Traits\SideValidationTrait;

class FleetExtractor
{
    use SideValidationTrait;

    public const TF_REGEX = '/^TF[0-9]*$/';
    public const TF_DIVISION_REGEX = '/^TF[0-9]*DIVISION[0-9]$/';

    private IniReader $iniReader;

    public function __construct(IniReader $iniReader)
    {
        $this->iniReader = $iniReader;
    }

    /** @return string[][] */
    public function getShipsInPort(string $path, string $side): array
    {
        $this->validateSide($side);

        $path .= DIRECTORY_SEPARATOR . $side . 'Ships.cfg';
        $record = false;
        $ships = [];
        $currentShipName = '';

        foreach ($this->iniReader->getData($path, false) as $line) {
            $this->handlePortLine($ships, $record, $currentShipName, $line);
        }

        return $ships;
    }

    /**
     * @param string[][] $ships
     * @param string[]   $line
     */
    private function handlePortLine(array &$ships, bool &$record, string &$currentShipName, array $line): void
    {
        if (
            0 === strpos($line['key'], 'header_')
            && 'SHIPS IN PORT' === $line['value']
        ) {
            $record = true;

            return;
        }

        if ('NAME' === $line['key'] && $record) {
            $currentShipName = $line['value'];
            $ships[$currentShipName] = [];

            return;
        }

        if (
            $record
            && (
                'LOCATION' === $line['key']
                || 'TYPE' === $line['key']
                || 'MAXSPEED' === $line['key']
                || 'ENDURANCE' === $line['key']
                || 'CURRENTENDURANCE' === $line['key']
                || 'RECONRANGE' === $line['key']
            )
        ) {
            $ships[$currentShipName][$line['key']] = $line['value'];

            return;
        }
    }

    /** @return TaskForce[] */
    public function extractFleets(string $path, string $side): array
    {
        $this->validateSide($side);

        $path .= DIRECTORY_SEPARATOR . $side . 'Ships.cfg';
        $fleets = [];
        $fleet = null;
        $fleetContext = null;
        $currentDivision = '';
        $currentName = '';

        foreach ($this->iniReader->getData($path, false) as $line) {
            // Task force header
            if (
                0 === strpos($line['key'], 'header_')
                    && preg_match(static::TF_REGEX, $line['value'])
            ) {
                if ($fleet instanceof TaskForce) {
                    $fleets[$fleet->getId()] = $fleet;
                    $currentDivision = '';
                }

                $fleet = new TaskForce($line['value']);
                $fleetContext = true;

                continue;
            }

            $this->handleFleetLine($line, $currentName, $fleet, $currentDivision, $fleetContext);

            // Ship in ports are in the second part of the file. We need to stop here.
            if (
                0 === strpos($line['key'], 'header_')
                && 'SHIPS IN PORT' === $line['value']
            ) {
                $fleets[$fleet->getId()] = $fleet;

                break;
            }
        }

        return $fleets;
    }

    /** @param string[] $line */
    private function handleFleetLine(
        array $line,
        string &$currentName,
        ?TaskForce $fleet,
        string &$currentDivision,
        ?bool &$fleetContext
    ): void {
        if ('NAME' === $line['key']) {
            if ('' !== $currentDivision) {
                $currentName = $line['value'];
            } else {
                // if $fleetContext, any other case is probably because the file is not properly formed
                $fleet->setName($line['value']);
            }

            return;
        }

        if (
            'TYPE' === $line['key']
            || 'MAXSPEED' === $line['key']
            || 'ENDURANCE' === $line['key']
            || 'CURRENTENDURANCE' === $line['key']
            || 'RECONRANGE' === $line['key']
        ) {
            $fleet->getFleetData()->addDataToShipInDivision($currentDivision, $currentName, $line['key'], $line['value']);

            return;
        }

        if (
            in_array(
                $line['key'],
                [
                'SPEED',
                'MISSION',
                'PROB',
                'CASECOUNT',
                'LL',
                'WP',
                'TP',
                ],
                true
            )
        ) {
            $this->addDataToFleet($fleet, $line, $fleetContext);
        }

        // Division header
        if (
            0 === strpos($line['key'], 'header_')
            && preg_match(static::TF_DIVISION_REGEX, $line['value'])
        ) {
            $currentDivision = $line['value'];
        }
    }

    /** @param string[] $line */
    private function addDataToFleet(TaskForce $fleet, array $line, bool $fleetContext): void
    {
        if ('SPEED' === $line['key'] && $fleetContext) {
            $fleet->setSpeed((float) $line['value']);
        }

        if ('MISSION' === $line['key'] && $fleetContext) {
            $fleet->setMission($line['value']);
        }

        if ('PROB' === $line['key'] && $fleetContext) {
            $fleet->setProb($line['value']);
        }

        if ('CASECOUNT' === $line['key'] && $fleetContext) {
            $fleet->setCaseCount($line['value']);
        }

        if ('LL' === $line['key'] && $fleetContext) {
            $fleet->setLl($line['value']);
        }

        if (('WP' === $line['key'] || 'TP' === $line['key']) && $fleetContext) {
            $fleet->addWaypoint($line['value']);
        }
    }
}
