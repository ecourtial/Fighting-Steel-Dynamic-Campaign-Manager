<?php

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       22/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

declare(strict_types=1);

namespace App\Core\Tas\Map;

use App\Core\Exception\InvalidInputException;

use function convertDMSToDecimal;

class MapService
{
    public const EARTH_RADIUS = 6371000;

    /**
     * Return the distance between two points, in kilometers.
     *
     * @see https://stackoverflow.com/questions/10053358/measuring-the-distance-between-two-coordinates-in-php
     */
    public function calculateDistanceBetweenTwoCoord(string $from, string $where): float
    {
        [$latitudeFrom, $longitudeFrom] = explode(' ', $from);
        $latitudeFrom = $this->convertDECtoDMS($latitudeFrom);
        $longitudeFrom = $this->convertDECtoDMS($longitudeFrom);

        [$latitudeTo, $longitudeTo] = explode(' ', $where);
        $latitudeTo = $this->convertDECtoDMS($latitudeTo);
        $longitudeTo = $this->convertDECtoDMS($longitudeTo);

        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return round($angle * static::EARTH_RADIUS / 1000, 1);
    }

    public function convertDECtoDMS(string $latitudeOrLongitude): float
    {
        return convertDMSToDecimal($this->convertTasLocationElement($latitudeOrLongitude));
    }

    private function convertTasLocationElement(string $entry): string
    {
        $entryLength = strlen($entry);
        // 5 and 6 => cases for the waypoints. 7 and 8 are for LL.
        if (5 === $entryLength) {
            return substr($entry, 0, 2) . '.' . substr($entry, 2);
        } elseif (6 === $entryLength) {
            return substr($entry, 0, 3) . '.' . substr($entry, 3);
        } elseif (7 === $entryLength) {
            $element = substr($entry, 0, 4) . substr($entry, 6);

            return substr($element, 0, 2) . '.' . substr($element, 2);
        } elseif (8 === $entryLength) {
            $element = substr($entry, 0, 5) . substr($entry, 7);

            return substr($element, 0, 3) . '.' . substr($element, 3);
        } else {
            throw new InvalidInputException("Unknown TAS location part: '{$entry}'");
        }
    }
}
