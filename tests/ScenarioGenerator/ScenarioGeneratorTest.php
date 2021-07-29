<?php

declare(strict_types=1);

namespace App\Tests\ScenarioGenerator;

use App\Core\File\TextFileWriter;
use App\ScenarioGenerator\Engine\BodyGenerator;
use App\ScenarioGenerator\Engine\ContextGenerator;
use App\ScenarioGenerator\Engine\Tools;
use App\ScenarioGenerator\ScenarioGenerator;
use PHPUnit\Framework\TestCase;

class ScenarioGeneratorTest extends TestCase
{
    private static ContextGenerator $contextGenerator;
    private static BodyGenerator $bodyGenerator;
    private static TextFileWriter $fileWriter;
    private static Tools $tools;

    public function setUp(): void
    {
        static::$contextGenerator = $this->createMock(ContextGenerator::class);
        static::$bodyGenerator = $this->createMock(BodyGenerator::class);
        static::$fileWriter = $this->createMock(TextFileWriter::class);
        static::$tools = $this->createMock(Tools::class);
    }

    public function testSuccess(): void
    {
        $directory = 'some/path';

        $scenarioName = 'someScenar';
        $code = 'Atlantic';
        $period = 3;
        $year = 1943;
        $month = 12;
        $header = 'someHeader';
        $body = 'someBody'; // Mouahahaha
        $mixed = true;

        static::$tools->expects(static::once())->method('getScenarioName')->willReturn($scenarioName);
        static::$tools->expects(static::once())->method('getYear')->with($code, $period)->willReturn($year);
        static::$tools->expects(static::once())->method('getMonth')->with($code, $period, $year)->willReturn($month);

        static::$contextGenerator->expects(static::once())->method('getHeaderData')->with($month, $year, $scenarioName)->willReturn($header);
        static::$bodyGenerator->expects(static::once())->method('getBody')->with($code, $period, $year, $mixed)->willReturn($body);

        static::$fileWriter->expects(static::once())->method('writeMultilineFromString')
            ->with(
                $directory . '/Scenarios/' . $scenarioName . '.scn',
                $header . PHP_EOL . PHP_EOL . $body
            );

        $scenarioGenerator = new ScenarioGenerator(static::$contextGenerator, static::$bodyGenerator, static::$fileWriter, static::$tools, $directory);

        static::assertEquals($scenarioName, $scenarioGenerator->generate($code, $period, $mixed));
    }

    public function testUnknownTheater(): void
    {
        $scenarioGenerator = new ScenarioGenerator(static::$contextGenerator, static::$bodyGenerator, static::$fileWriter, static::$tools, 'blablabla');

        $code = '666';

        static::expectException(\InvalidArgumentException::class);
        static::expectExceptionMessage("The theater '{$code}' does not exist.");

        $scenarioGenerator->generate($code, 1939, false);
    }

    public function testUnknownPeriod(): void
    {
        $scenarioGenerator = new ScenarioGenerator(static::$contextGenerator, static::$bodyGenerator, static::$fileWriter, static::$tools, 'blablabla');

        $period = 1111;

        static::expectException(\InvalidArgumentException::class);
        static::expectExceptionMessage("The period '{$period}' does not exist for this theater.");

        $scenarioGenerator->generate('Atlantic', $period, false);
    }
}
