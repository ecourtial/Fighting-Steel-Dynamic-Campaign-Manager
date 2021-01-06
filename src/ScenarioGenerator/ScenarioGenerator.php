<?php

declare(strict_types=1);

namespace App\ScenarioGenerator;

use App\Core\File\TextFileWriter;

class ScenarioGenerator
{
    private const DATE_PATTERN = 'Y-m-d-H-i-s';

    private ContextGenerator $contextGenerator;
    private BodyGenerator $bodyGenerator;
    private TextFileWriter $fileWriter;
    private string $fsScenarioDirectory;

    public function __construct(
        ContextGenerator $contextGenerator,
        BodyGenerator $bodyGenerator,
        TextFileWriter $fileWriter,
        string $fsDirectory
    ) {
        $this->contextGenerator = $contextGenerator;
        $this->bodyGenerator = $bodyGenerator;
        $this->fileWriter = $fileWriter;
        $this->fsScenarioDirectory = $fsDirectory . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;
    }

    public function generate(string $code, int $period, bool $mixedNavies): void
    {
        if (false === array_key_exists($code, ScenarioEnv::SELECTOR)) {
            throw new \InvalidArgumentException("The theater '{$code}' does not exist.");
        }

        if (false === array_key_exists($period, ScenarioEnv::SELECTOR[$code]['periods'])) {
            throw new \InvalidArgumentException("The period '{$period}' does not exist for this theater.");
        }

        $scenarioName = 'randomScenar' . date(static::DATE_PATTERN);
        $year = $this->getYear($code, $period);
        $month = $this->getMonth($code, $period, $year);

        $this->fileWriter->writeMultilineFromString(
            '',
            $this->contextGenerator->getHeaderData($year, $month, $scenarioName) . PHP_EOL . PHP_EOL
                . $this->bodyGenerator->getBody($code, $period, $year, $mixedNavies)
        );
    }

    private function getYear(string $code, int $period): int
    {
        return array_rand(array_keys(ScenarioEnv::SELECTOR[$code]['periods'][$period]['years']));
    }

    private function getMonth(string $code, int $period, int $year): int
    {
        return array_rand(ScenarioEnv::SELECTOR[$code]['periods'][$period]['years'][$year]);
    }
}
