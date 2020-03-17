<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\NameSwitcher\Validator;

use App\NameSwitcher\Reader\DictionaryReader;
use App\NameSwitcher\Validator\DictionaryValidator;
use PHPUnit\Framework\TestCase;
use Wizaplace\Etl\Extractors\Csv as CsvExtractor;

class DictionaryValidatorTest extends TestCase
{
    protected static DictionaryValidator $dictionaryValidator;

    public static function setUpBeforeClass(): void
    {
        $dictionaryReader = new DictionaryReader(new CsvExtractor());
        static::$dictionaryValidator = new DictionaryValidator($dictionaryReader);
    }

    public function testValidateWithoutError(): void
    {
        $expected = [];
        static::assertEquals($expected, static::$dictionaryValidator->validate('tests/Assets/dictionary.csv'));
    }

    public function testValidateFileReadingIssue(): void
    {
        $expected = ['Error during the dictionary extraction: fopen(fjejkhk.csv): failed to open stream: No such file or directory'];
        static::assertEquals($expected, static::$dictionaryValidator->validate('fjejkhk.csv'));
    }

    public function testValidateWithError(): void
    {
        $expected = [
            "Error at line #4. The name 'Richelieu' is already used at line #2",
            "Error at line #5. The name 'Richelieu' is already used at line #2",
            "Error at line #6. FS Short name is too long: 'Mogador|Hunt'",
            "Error at line #8. The name 'Lutzow' is already used at line #7",
            'Row with index #9 only contains 2 elements while 6 were expected.',
        ];
        static::assertEquals($expected, static::$dictionaryValidator->validate('tests/Assets/dictionary-bad.csv'));
    }
}
