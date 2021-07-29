<?php

declare(strict_types=1);

/*
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       20/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Tests\Controller\Tas;

use App\Controller\Tas\ScenarioValidation;
use App\NameSwitcher\Validator\ScenarioValidator;
use App\Tests\Controller\ResponseTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ScenarioValidationTest extends TestCase
{
    use ResponseTrait;

    public function testInvoke(): void
    {
        [$requestStack, $request, $logger, $scenarioValidator] = $this->getMocks();

        $requestStack->expects(static::once())->method('getCurrentRequest')->willReturn($request);
        $request->expects(static::at(0))->method('get')->with('scenario', null)->willReturn('UnScenario');
        $scenarioValidator->expects(static::once())->method('validate')->with('UnScenario');

        $controller = new ScenarioValidation($requestStack, $logger, $scenarioValidator);
        $response = $controller();
        $content = (\json_decode($response->getContent(), true));

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals(['messages' => []], $content);
        $this->checkResponse($response, 200);
    }

    public function testError(): void
    {
        [$requestStack, $request, $logger, $scenarioValidator] = $this->getMocks();

        $requestStack->expects(static::once())->method('getCurrentRequest')->willReturn($request);
        $request->expects(static::at(0))->method('get')->with('scenario', null)->willReturn('UnScenario');
        $scenarioValidator->expects(static::once())->method('validate')->with('UnScenario')->willThrowException(
            new \Exception('Oh sooorrrryyy')
        );

        $controller = new ScenarioValidation($requestStack, $logger, $scenarioValidator);
        $response = $controller();
        $content = (\json_decode($response->getContent(), true));

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals(['messages' => ['An error occurred: Oh sooorrrryyy']], $content);
        $this->checkResponse($response, 500);
        static::assertTrue($logger->hasErrorRecords());
    }

    public function testInvalidRequest(): void
    {
        [$requestStack, $request, $logger, $scenarioValidator] = $this->getMocks();

        $requestStack->expects(static::once())->method('getCurrentRequest')->willReturn($request);
        $request->expects(static::at(0))->method('get')->with('scenario', null)->willReturn(null);

        $controller = new ScenarioValidation($requestStack, $logger, $scenarioValidator);
        $response = $controller();
        $content = (\json_decode($response->getContent(), true));

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals(['messages' => ['Missing scenario key']], $content);
        $this->checkResponse($response, 400);
    }

    private function getMocks(): array
    {
        return [
            $this->createMock(RequestStack::class),
            $this->createMock(Request::class),
            new TestLogger(),
            $this->createMock(ScenarioValidator::class),
        ];
    }
}
