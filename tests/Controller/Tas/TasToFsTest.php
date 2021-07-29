<?php

declare(strict_types=1);

/*
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       20/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Tests\Controller\Tas;

use App\Controller\Tas\TasToFs;
use App\NameSwitcher\ScenarioManager;
use App\Tests\Controller\ResponseTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TasToFsTest extends TestCase
{
    use ResponseTrait;

    public function testInvoke(): void
    {
        [$requestStack, $request, $scenarioManager, $logger] = $this->getMocks();

        $requestStack->expects(static::exactly(3))->method('getCurrentRequest')->willReturn($request);
        $request->expects(static::at(0))->method('get')->with('scenario', null)->willReturn('UnScenario');
        $request->expects(static::at(1))->method('get')->with('oneShip', null)->willReturn('Hood');
        $request->expects(static::at(2))->method('get')->with('switchLevel', null)->willReturn('Basic');

        $scenarioManager->expects(static::once())->method('fromTasToFs')->with('UnScenario', 'Hood', 'Basic');

        $controller = new TasToFs($requestStack, $logger, $scenarioManager);
        $response = $controller();
        $content = (\json_decode($response->getContent(), true));

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals(['messages' => ['Translation from TAS to FS completed.']], $content);
        $this->checkResponse($response, 200);
    }

    public function testError(): void
    {
        [$requestStack, $request, $scenarioManager, $logger] = $this->getMocks();

        $requestStack->expects(static::exactly(3))->method('getCurrentRequest')->willReturn($request);
        $request->expects(static::at(0))->method('get')->with('scenario', null)->willReturn('UnScenario');
        $request->expects(static::at(1))->method('get')->with('oneShip', null)->willReturn('Hood');
        $request->expects(static::at(2))->method('get')->with('switchLevel', null)->willReturn('Basic');
        $scenarioManager->expects(static::once())->method('fromTasToFs')->with('UnScenario')->willThrowException(
            new \Exception('Oh sooorrrryyy')
        );

        $controller = new TasToFs($requestStack, $logger, $scenarioManager);
        $response = $controller();
        $content = (\json_decode($response->getContent(), true));

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals(['messages' => ['An error occurred: Oh sooorrrryyy']], $content);
        $this->checkResponse($response, 500);

        /** @var TestLogger $logger */
        static::assertTrue($logger->hasErrorRecords());
    }

    /** @dataProvider invalidRequestProvider */
    public function testInvalidRequest(?string $scenario, ?string $ship, ?string $level): void
    {
        [$requestStack, $request, $scenarioManager, $logger] = $this->getMocks();

        $requestStack->expects(static::exactly(3))->method('getCurrentRequest')->willReturn($request);
        $request->expects(static::at(0))->method('get')->with('scenario', null)->willReturn($scenario);
        $request->expects(static::at(1))->method('get')->with('oneShip', null)->willReturn($ship);
        $request->expects(static::at(2))->method('get')->with('switchLevel', null)->willReturn($level);

        $controller = new TasToFs($requestStack, $logger, $scenarioManager);
        $response = $controller();
        $content = (\json_decode($response->getContent(), true));

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals(['messages' => ['Invalid request data!']], $content);
        $this->checkResponse($response, 400);
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
            $this->createMock(ScenarioManager::class),
            new TestLogger(),
        ];
    }
}
