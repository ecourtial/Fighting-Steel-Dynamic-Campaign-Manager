<?php

declare(strict_types=1);

namespace App\Tests\Controller\Fs;

use App\Controller\Fs\ScenarioGenerator as ScenarioGeneratorController;
use App\ScenarioGenerator\ScenarioGenerator;
use App\Tests\Controller\ResponseTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ScenarioGeneratorTest extends TestCase
{
    use ResponseTrait;

    public function testInvoke(): void
    {
        [$requestStack, $request, $logger, $scenarioGenerator] = $this->getMocks();

        $scenarioGenerator->expects(static::once())->method('generate')->willReturn('POMPOMPOM');
        $requestStack->expects(static::any())->method('getCurrentRequest')->willReturn($request);
        $request->expects(static::at(0))->method('get')->with('code', null)->willReturn('Mediterranean');
        $request->expects(static::at(1))->method('get')->with('period', null)->willReturn('1');
        $request->expects(static::at(2))->method('get')->with('mixedNavies', null)->willReturn('0');

        $controller = new ScenarioGeneratorController($requestStack, $logger, $scenarioGenerator);
        $response = $controller();
        $content = (\json_decode($response->getContent(), true));

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals(['messages' => ["The scenario with the following name has been generated : 'POMPOMPOM'"]], $content);
        $this->checkResponse($response, 200);
    }

    public function testError(): void
    {
        [$requestStack, $request, $logger, $scenarioGenerator] = $this->getMocks();

        $scenarioGenerator->expects(static::once())->method('generate')->willThrowException(
            new \Exception('Oh sooorrrryyy')
        );
        $requestStack->expects(static::any())->method('getCurrentRequest')->willReturn($request);
        $request->expects(static::at(0))->method('get')->with('code', null)->willReturn('Mediterranean');
        $request->expects(static::at(1))->method('get')->with('period', null)->willReturn('1');
        $request->expects(static::at(2))->method('get')->with('mixedNavies', null)->willReturn('0');

        $controller = new ScenarioGeneratorController($requestStack, $logger, $scenarioGenerator);
        $response = $controller();
        $content = (\json_decode($response->getContent(), true));

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals(['messages' => ['An error occurred: Oh sooorrrryyy']], $content);
        $this->checkResponse($response, 500);
        static::assertTrue($logger->hasErrorRecords());
    }

    private function getMocks(): array
    {
        return [
            $this->createMock(RequestStack::class),
            $this->createMock(Request::class),
            new TestLogger(),
            $this->createMock(ScenarioGenerator::class),
        ];
    }
}
