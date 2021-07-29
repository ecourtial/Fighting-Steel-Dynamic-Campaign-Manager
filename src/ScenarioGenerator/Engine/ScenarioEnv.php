<?php

declare(strict_types=1);

namespace App\ScenarioGenerator\Engine;

use App\Core\Fs\Scenario\Ship\Ship;
use App\Core\Tas\Scenario\Scenario;

interface ScenarioEnv
{
    // Correspondence in the FSP dictionary
    public const GB = 'RN';
    public const GE = 'KM';
    public const FR = 'MN';
    public const IT = 'RM';
    public const JP = 'IJN';
    public const US = 'USN';

    public const AIR_CONTROL = [
        2 => 'None',
        3 => 'Force A',
        4 => 'Contested',
        5 => 'Force B',
    ];

    public const BATTLE_TYPE = [
        0 => 'Meeting engagement',
        3 => 'Intercept',
    ];

    // LEVELS
    public const WIND_SPEED_MIN = 1;
    public const WIND_SPEED_MAX = 40;

    public const SEA_STATE_MIN = 1;
    public const SEA_STATE_MAX = 8;

    public const HEADING_DEGREE_MIN = 1;
    public const HEADING_DEGREE_MAX = 360;

    public const RAIN_OFF = 0;
    public const RAIN_ON = 1;

    public const VISIBILITY_MIN = 40;
    public const VISIBILITY_MAX = 100;

    public const RADAR_CONDITION_MIN = 0;
    public const RADAR_CONDITION_MAX = 100;

    public const RADAR_LEVELS = [
        1939 => [
            self::GB => [Ship::RADAR_LEVEL_NONE],
            self::FR => [Ship::RADAR_LEVEL_NONE],
            self::GE => [Ship::RADAR_LEVEL_NONE, Ship::RADAR_LEVEL_POOR],
        ],
        1940 => [
            self::GB => [Ship::RADAR_LEVEL_NONE, Ship::RADAR_LEVEL_POOR],
            self::FR => [Ship::RADAR_LEVEL_NONE],
            self::GE => [Ship::RADAR_LEVEL_NONE, Ship::RADAR_LEVEL_POOR],
            self::IT => [Ship::RADAR_LEVEL_NONE],
        ],
        1941 => [
            self::GB => [Ship::RADAR_LEVEL_POOR],
            self::FR => [Ship::RADAR_LEVEL_NONE, Ship::RADAR_LEVEL_POOR],
            self::GE => [Ship::RADAR_LEVEL_POOR],
            self::IT => [Ship::RADAR_LEVEL_NONE],
            self::JP => [Ship::RADAR_LEVEL_NONE, Ship::RADAR_LEVEL_POOR],
            self::US => [Ship::RADAR_LEVEL_NONE, Ship::RADAR_LEVEL_POOR],
        ],
        1942 => [
            self::GB => [Ship::RADAR_LEVEL_AVERAGE],
            self::FR => [Ship::RADAR_LEVEL_NONE, Ship::RADAR_LEVEL_POOR],
            self::GE => [Ship::RADAR_LEVEL_AVERAGE],
            self::IT => [Ship::RADAR_LEVEL_NONE, Ship::RADAR_LEVEL_POOR],
            self::JP => [Ship::RADAR_LEVEL_POOR, Ship::RADAR_LEVEL_AVERAGE],
            self::US => [Ship::RADAR_LEVEL_AVERAGE],
        ],
        1943 => [
            self::GB => [Ship::RADAR_LEVEL_AVERAGE, Ship::RADAR_LEVEL_GOOD, Ship::RADAR_LEVEL_EXCELLENT],
            self::FR => [Ship::RADAR_LEVEL_POOR, Ship::RADAR_LEVEL_AVERAGE],
            self::GE => [Ship::RADAR_LEVEL_AVERAGE, Ship::RADAR_LEVEL_GOOD],
            self::IT => [Ship::RADAR_LEVEL_NONE, Ship::RADAR_LEVEL_POOR],
            self::JP => [Ship::RADAR_LEVEL_POOR, Ship::RADAR_LEVEL_AVERAGE, Ship::RADAR_LEVEL_GOOD],
            self::US => [Ship::RADAR_LEVEL_AVERAGE, Ship::RADAR_LEVEL_GOOD, Ship::RADAR_LEVEL_EXCELLENT],
        ],
        1944 => [
            self::GB => [Ship::RADAR_LEVEL_AVERAGE, Ship::RADAR_LEVEL_GOOD, Ship::RADAR_LEVEL_EXCELLENT],
            self::FR => [Ship::RADAR_LEVEL_POOR, Ship::RADAR_LEVEL_AVERAGE],
            self::GE => [Ship::RADAR_LEVEL_AVERAGE, Ship::RADAR_LEVEL_GOOD],
            self::JP => [Ship::RADAR_LEVEL_POOR, Ship::RADAR_LEVEL_AVERAGE, Ship::RADAR_LEVEL_GOOD],
            self::US => [Ship::RADAR_LEVEL_AVERAGE, Ship::RADAR_LEVEL_GOOD, Ship::RADAR_LEVEL_EXCELLENT],
        ],
        1945 => [
            self::GB => [Ship::RADAR_LEVEL_GOOD, Ship::RADAR_LEVEL_EXCELLENT, Ship::RADAR_LEVEL_SUPERB],
            self::FR => [Ship::RADAR_LEVEL_AVERAGE, Ship::RADAR_LEVEL_GOOD],
            self::GE => [Ship::RADAR_LEVEL_AVERAGE, Ship::RADAR_LEVEL_GOOD],
            self::JP => [Ship::RADAR_LEVEL_POOR, Ship::RADAR_LEVEL_AVERAGE, Ship::RADAR_LEVEL_GOOD],
            self::US => [Ship::RADAR_LEVEL_GOOD, Ship::RADAR_LEVEL_EXCELLENT, Ship::RADAR_LEVEL_SUPERB],
        ],
    ];

    // TIME
    public const DAY_24_HOURS_CLOCK_MIN = 0;
    public const DAY_24_HOURS_CLOCK_MAX = 23;

    public const MINUTES_MIN = 1;
    public const MINUTES_MAX = 59;

    public const WINTER_SUNRISE_HOUR = 7;
    public const MID_SUNRISE_HOUR = 6;
    public const SUMMER_SUNRISE_HOUR = 5;

    public const WINTER_SUNSET_HOUR = 17;
    public const MID_SUNSET_HOUR = 19;
    public const SUMMER_SUNSET_HOUR = 21;

    public const WINTER_MONTHS = [11, 12, 1, 2, 3];
    public const MID_MONTHS = [4, 5, 9, 10];
    public const SUMMER_MONTHS = [6, 7, 8];

    public const MONTHS = [
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];

    // THEATERS
    public const ATLANTIC_THEATER = 'Atlantic';
    public const MEDITERRANEAN_THEATER = 'Mediterranean';
    public const PACIFIC_THEATER = 'Pacific';

    public const SELECTOR = [
        self::ATLANTIC_THEATER => [
            'name' => self::ATLANTIC_THEATER,
            'periods' => [
                // EN and FR vs GE
                0 => [
                    'name' => 'September 1939 to may 1940',
                    'years' => [
                        1939 => [9, 10, 11, 12],
                        1940 => [1, 2, 3, 4, 5],
                    ],
                    Scenario::ALLIED_SIDE => [self::GB, self::FR],
                    Scenario::AXIS_SIDE => [self::GE],
                ],
                // EN vs GE
                1 => [
                    'name' => 'June 1940 to november 1941',
                    'years' => [
                        1940 => [6, 7, 8, 9, 10, 11, 12],
                        1941 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
                    ],
                    Scenario::ALLIED_SIDE => [self::GB],
                    Scenario::AXIS_SIDE => [self::GE],
                ],
                // EN and US vs GE
                2 => [
                    'name' => 'December 1941 to october 1942',
                    'years' => [
                        1941 => [12],
                        1942 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                    ],
                    Scenario::ALLIED_SIDE => [self::GB, self::US],
                    Scenario::AXIS_SIDE => [self::GE],
                ],
                // EN, US and FR vs GE
                3 => [
                    'name' => 'November 1942 to may 1945',
                    'years' => [
                        1942 => [11, 12],
                        1943 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        1944 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        1945 => [1, 2, 3, 4, 5],
                    ],
                    Scenario::ALLIED_SIDE => [self::GB, self::US, self::FR],
                    Scenario::AXIS_SIDE => [self::GE],
                ],
            ],
        ],
        self::MEDITERRANEAN_THEATER => [
            'name' => self::MEDITERRANEAN_THEATER,
            'periods' => [
                // EN and FR  vs IT
                0 => [
                    'name' => 'June 1940',
                    'years' => [
                        1940 => [6],
                    ],
                    Scenario::ALLIED_SIDE => [self::GB, self::FR],
                    Scenario::AXIS_SIDE => [self::IT],
                ],
                // EN vs IT
                1 => [
                    'name' => 'July 1940 to november 1941',
                    'years' => [
                        1940 => [7, 8, 9, 10, 11, 12],
                        1941 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
                    ],
                    Scenario::ALLIED_SIDE => [self::GB],
                    Scenario::AXIS_SIDE => [self::IT],
                ],
                // EN and US vs IT
                2 => [
                    'name' => 'December 1941 to october 1942',
                    'years' => [
                        1941 => [12],
                        1942 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                    ],
                    Scenario::ALLIED_SIDE => [self::GB, self::US],
                    Scenario::AXIS_SIDE => [self::IT],
                ],
                // ENG, US and FR vs IT
                3 => [
                    'name' => 'November 1942 to september 1943',
                    'years' => [
                        1942 => [11, 12],
                        1943 => [1, 2, 3, 4, 5, 6, 7, 8, 9],
                    ],
                    Scenario::ALLIED_SIDE => [self::GB, self::US, self::FR],
                    Scenario::AXIS_SIDE => [self::IT],
                ],
            ],
        ],
        self::PACIFIC_THEATER => [
            'name' => self::PACIFIC_THEATER,
            'periods' => [
                // EN and US vs JA
                0 => [
                    'name' => 'December 1941 to october 1942',
                    'years' => [
                        1941 => [12],
                        1942 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                    ],
                    Scenario::ALLIED_SIDE => [self::GB, self::US],
                    Scenario::AXIS_SIDE => [self::JP],
                ],
                // EN, US and FR vs JA
                1 => [
                    'name' => 'November 1942 to september 1945',
                    'years' => [
                        1942 => [11, 12],
                        1943 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        1944 => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
                        1945 => [1, 2, 3, 4, 5, 6, 7, 8, 9],
                    ],
                    Scenario::ALLIED_SIDE => [self::GB, self::US, self::FR],
                    Scenario::AXIS_SIDE => [self::JP],
                ],
            ],
        ],
    ];
}
