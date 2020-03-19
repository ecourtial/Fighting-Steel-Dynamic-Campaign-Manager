<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\NameSwitcher\Validator;

use App\Core\File\IniReader;
use App\Core\File\TextFileReader;
use App\Core\Fs\Ship\ShipExtractor as FsShipExtractor;
use App\Core\Tas\Ship\ShipExtractor as TasShipExtractor;
use App\NameSwitcher\Reader\DictionaryReader;
use PHPUnit\Framework\TestCase;
use App\NameSwitcher\Validator\ScenarioValidator;
use App\NameSwitcher\Validator\DictionaryValidator;
use App\Core\Tas\Scenario\ScenarioRepository;
use Wizaplace\Etl\Extractors\Csv as CsvExtractor;

class ScenarioValidatorTest extends TestCase
{
    protected static ScenarioValidator $scenarioValidator;

    public static function setUpBeforeClass()
    {
        $textReader = new TextFileReader();
        $iniReader = new IniReader($textReader);
        $tasShipExtractor = new TasShipExtractor($iniReader);
        $fsShipExtractor = new FsShipExtractor($iniReader);

        $repo = new ScenarioRepository(
            $_ENV['TAS_LOCATION'],
            $iniReader,
            $tasShipExtractor,
            $fsShipExtractor
        );

        $dictionaryReader = new DictionaryReader(new CsvExtractor());
        static::$scenarioValidator = new ScenarioValidator(new DictionaryValidator($dictionaryReader), $repo);
    }

    public function testSuccessValidation(): void
    {
        static::assertEquals(
            [],
            static::$scenarioValidator->validate(
                'Good Scenario',
                'tests/Assets/dictionary.csv'
            )
        );
    }

    public function testValidationWithDictionaryError(): void
    {
        $errors = static::$scenarioValidator->validate(
            'Bad GoebenReminiscence',
            'tests/Assets/dictionary-bad.csv'
        );

        $expected = [
            0 => "Error at line #4. The name 'Richelieu' is already used at line #2",
            1 => "Error at line #5. The name 'Richelieu' is already used at line #2",
            2 => "Error at line #6. FS Short name is too long: 'Mogador|Hunt'",
            3 => "Error at line #8. The name 'Lutzow' is already used at line #7",
            4 => "Row with index #9 only contains 3 elements while 7 were expected.",
        ];

        static::assertEquals($expected, $errors);
    }

    public function testValidationErrorWhenLoadingTheScenario(): void
    {
        $errors = static::$scenarioValidator->validate(
            'Bad GoebenReminiscence',
            'tests/Assets/dictionary.csv'
        );

        static::assertEquals(["FS Short name is too long: 'La Bombarde'"], $errors);
    }

    public function testTasShipMissingInFsFile(): void
    {
        $errors = static::$scenarioValidator->validate(
            'ScenarioWithMissingFsShips',
            'tests/Assets/dictionary.csv'
        );

        $expected = [
            0 => "Tas Ship 'Algerie' is not present in the FS file",
            1 => "Tas Ship 'Scharnhorst' is not present in the FS file"
        ];

        static::assertEquals($expected, $errors);
    }
}