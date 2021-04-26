<?php

declare(strict_types=1);

namespace App\ScenarioGenerator;

use App\Core\File\TextFileWriter;
use App\ScenarioGenerator\Engine\BodyGenerator;
use App\ScenarioGenerator\Engine\ContextGenerator;
use App\ScenarioGenerator\Engine\ScenarioEnv;
use App\ScenarioGenerator\Engine\Tools;

class ScenarioGenerator
{
    private ContextGenerator $contextGenerator;
    private BodyGenerator $bodyGenerator;
    private TextFileWriter $fileWriter;
    private Tools $tools;
    private string $fsScenarioDirectory;

    public function __construct(
        ContextGenerator $contextGenerator,
        BodyGenerator $bodyGenerator,
        TextFileWriter $fileWriter,
        Tools $tools,
        string $fsDirectory
    ) {
        $this->contextGenerator = $contextGenerator;
        $this->bodyGenerator = $bodyGenerator;
        $this->fileWriter = $fileWriter;
        $this->fsScenarioDirectory = $fsDirectory . DIRECTORY_SEPARATOR . 'Scenarios' . DIRECTORY_SEPARATOR;
        $this->tools = $tools;
    }

    public function generate(string $code, int $period, bool $mixedNavies): string
    {
        if (false === array_key_exists($code, ScenarioEnv::SELECTOR)) {
            throw new \InvalidArgumentException("The theater '{$code}' does not exist.");
        }

        if (false === array_key_exists($period, ScenarioEnv::SELECTOR[$code]['periods'])) {
            throw new \InvalidArgumentException("The period '{$period}' does not exist for this theater.");
        }

        $scenarioName = $this->tools->getScenarioName();
        $year = $this->tools->getYear($code, $period);
        $month = $this->tools->getMonth($code, $period, $year);

        $this->fileWriter->writeMultilineFromString(
            $this->fsScenarioDirectory . $scenarioName . '.scn',
            $this->contextGenerator->getHeaderData($month, $year, $scenarioName) . PHP_EOL . PHP_EOL
                . $this->bodyGenerator->getBody($code, $period, $year, $mixedNavies)
        );

        return $scenarioName;
    }
}
