<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       23/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Core\Tas\Savegame\Fleet;

use App\Core\Exception\InvalidInputException;
use App\Core\File\IniReader;
use App\Core\Tas\Scenario\Scenario;

class FleetExtractor
{
    public const TF_REGEX = '/^TF[0-9]*$/';
    public const TF_DIVISION_REGEX = '/^TF[0-9]*DIVISION[0-9]$/';

    private IniReader $iniReader;

    public function __construct(IniReader $iniReader)
    {
        $this->iniReader = $iniReader;
    }

    /** @return string[] */
    public function getShipsInPort(string $path, string $side): array
    {
        if (false === in_array($side, Scenario::SIDES, true)) {
            throw new InvalidInputException("Unknown side '$side'");
        }

        $path .= DIRECTORY_SEPARATOR . $side . 'Ships.cfg';
        $record = false;
        $ships = [];
        $currentShipName = '';

        foreach ($this->iniReader->getData($path, false) as $line) {
            if (
                0 === strpos($line['key'], 'header_')
                && 'SHIPS IN PORT' === $line['value']
            ) {
                $record = true;

                continue;
            }

            if ('NAME' === $line['key'] && $record) {
                $currentShipName = $line['value'];

                continue;
            }

            if ('LOCATION' === $line['key'] && '' !== $currentShipName && $record) {
                $ships[$currentShipName] = $line['value'];
                $currentShipName = '';

                continue;
            }
        }

        return $ships;
    }

    /** @return Fleet[] */
    public function extractFleets(string $path, string $side): array
    {
        if (false === in_array($side, Scenario::SIDES, true)) {
            throw new InvalidInputException("Unknown side '$side'");
        }

        $path .= DIRECTORY_SEPARATOR . $side . 'Ships.cfg';
        $fleets = [];
        $fleet = null;
        $fleetContext = false;
        $currentDivision = '';
        $currentName = '';

        foreach ($this->iniReader->getData($path, false) as $line) {
            // Task force header
            if (
                0 === strpos($line['key'], 'header_')
                    && preg_match(static::TF_REGEX, $line['value'])
            ) {
                if ($fleet instanceof Fleet) {
                    $fleets[$fleet->getId()] = $fleet;
                    $currentDivision = '';
                }

                $fleet = new Fleet();
                $fleet->setId($line['value']);
                $fleetContext = true;

                continue;
            }

            if ('NAME' === $line['key']) {
                if ('' !== $currentDivision) {
                    $fleet->addShipToDivision($currentDivision, $line['value']);
                    $currentName = $line['value'];
                } elseif ($fleetContext) {
                    $fleet->setName($line['value']);
                } else {
                    throw new InvalidInputException('Unknown NAME case');
                }

                continue;
            }

            if (
                'TYPE' === $line['key']
                || 'MAXSPEED' === $line['key']
                || 'ENDURANCE' === $line['key']
                || 'CURRENTENDURANCE' === $line['key']
                || 'RECONRANGE' === $line['key']
            ) {
                $fleet->addDataToShip($currentDivision, $currentName, $line['key'], $line['value']);

                continue;
            }

            if ('SPEED' === $line['key'] && $fleetContext) {
                $fleet->setSpeed((float) $line['value']);

                continue;
            }

            if ('MISSION' === $line['key'] && $fleetContext) {
                $fleet->setMission($line['value']);

                continue;
            }

            if ('PROB' === $line['key'] && $fleetContext) {
                $fleet->setProb($line['value']);

                continue;
            }

            if ('CASECOUNT' === $line['key'] && $fleetContext) {
                $fleet->setCaseCount($line['value']);

                continue;
            }

            if ('LL' === $line['key'] && $fleetContext) {
                $fleet->setLl($line['value']);

                continue;
            }

            if ('OBJECTIVE' === $line['key'] && $fleetContext) {
                throw new InvalidInputException('OBJECTIVE is not supported for fleets!');
            }

            if ('LL' === $line['key'] && $fleetContext) {
                $fleet->setLl($line['value']);

                continue;
            }

            if (('WP' === $line['key'] || 'TP' === $line['key']) && $fleetContext) {
                $fleet->addWaypoint($line['value']);

                continue;
            }

            if ('ENDMISSION' === $line['key'] && $fleetContext) {
                $fleetContext = false;

                continue;
            }

            // Division header
            if (
                0 === strpos($line['key'], 'header_')
                && preg_match(static::TF_DIVISION_REGEX, $line['value'])
            ) {
                $currentDivision = $line['value'];
                $fleet->addDivision($currentDivision);
            }

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
}
