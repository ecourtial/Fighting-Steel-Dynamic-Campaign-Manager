<?php

declare(strict_types=1);

/*
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       20/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Tests\Controller\Tas;

use App\Controller\Tas\TasToFs;
use App\Core\Tas\Scenario\Scenario;
use App\Core\Tas\Scenario\ScenarioRepository;
use App\NameSwitcher\Dictionary\Dictionary;
use App\NameSwitcher\Dictionary\DictionaryFactory;
use App\NameSwitcher\ScenarioProcessor;
use App\Tests\Controller\ResponseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TasToFsTest extends TestCase
{
    use ResponseTrait;

    public function testInvoke(): void
    {
        [$requestStack, $request, $scenarioProcessor, $dicoFactory, $scenarioRepo] = $this->getMocks();

        $requestStack->expects(static::exactly(3))->method('getCurrentRequest')->willReturn($request);
        $request->expects(static::at(0))->method('get')->with('scenario', null)->willReturn('UnScenario');
        $request->expects(static::at(1))->method('get')->with('oneShip', null)->willReturn('Hood');
        $request->expects(static::at(2))->method('get')->with('switchLevel', null)->willReturn('Basic');
        $dummyScenar = $this->createMock(Scenario::class);
        $scenarioRepo->expects(static::once())->method('getOneWillAllData')->with('UnScenario')->willReturn($dummyScenar);
        $dummyScenar->expects(static::once())->method('getDictionaryPath')->willReturn('test.csv');
        $dummyScenar->expects(static::once())->method('getFsShips')->willReturn([]);
        $dummyDico = $this->createMock(Dictionary::class);
        $dicoFactory->expects(static::once())->method('getDictionary')->with('test.csv')->willReturn($dummyDico);
        $scenarioProcessor->expects(static::once())->method('convertFromTasToFs')->with('Hood', $dummyDico, []);

        $controller = new TasToFs($requestStack, $scenarioProcessor, $dicoFactory, $scenarioRepo);
        $response = $controller();
        $content = (\json_decode($response->getContent()));

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals([], $content);
        $this->checkResponse($response);
    }

    public function testError(): void
    {
        [$requestStack, $request, $scenarioProcessor, $dicoFactory, $scenarioRepo] = $this->getMocks();

        $requestStack->expects(static::exactly(3))->method('getCurrentRequest')->willReturn($request);
        $request->expects(static::at(0))->method('get')->with('scenario', null)->willReturn('UnScenario');
        $request->expects(static::at(1))->method('get')->with('oneShip', null)->willReturn('Hood');
        $request->expects(static::at(2))->method('get')->with('switchLevel', null)->willReturn('Basic');
        $scenarioRepo->expects(static::once())->method('getOneWillAllData')->with('UnScenario')->willThrowException(
            new \Exception('Oh sooorrrryyy')
        );

        $controller = new TasToFs($requestStack, $scenarioProcessor, $dicoFactory, $scenarioRepo);
        $response = $controller();
        $content = (\json_decode($response->getContent()));

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals(['Oh sooorrrryyy'], $content);
        $this->checkResponse($response);
    }

    /** @dataProvider invalidRequestProvider */
    public function testInvalidRequest(?string $scenario, ?string $ship, ?string $level): void
    {
        [$requestStack, $request, $scenarioProcessor, $dicoFactory, $scenarioRepo] = $this->getMocks();

        $requestStack->expects(static::exactly(3))->method('getCurrentRequest')->willReturn($request);
        $request->expects(static::at(0))->method('get')->with('scenario', null)->willReturn($scenario);
        $request->expects(static::at(1))->method('get')->with('oneShip', null)->willReturn($ship);
        $request->expects(static::at(2))->method('get')->with('switchLevel', null)->willReturn($level);

        $controller = new TasToFs($requestStack, $scenarioProcessor, $dicoFactory, $scenarioRepo);
        $response = $controller();
        $content = (\json_decode($response->getContent()));

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals(['Invalid request data!'], $content);
        $this->checkResponse($response);
    }

    public function invalidRequestProvider(): array
    {
        return [
          [null, 'Hood', 'Basic'],
          ['UnScenario', null, 'Basic'],
          ['UnScenario', 'Hood', null],
        ];
    }

    private function getMocks(): array
    {
        return [
            $this->createMock(RequestStack::class),
            $this->createMock(Request::class),
            $this->createMock(ScenarioProcessor::class),
            $this->createMock(DictionaryFactory::class),
            $this->createMock(ScenarioRepository::class),
        ];
    }
}
