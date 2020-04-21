<?php

declare(strict_types=1);

/*
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       20/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

use App\Controller\Fs\FsToTas;
use App\NameSwitcher\ScenarioProcessor;
use App\Tests\Controller\ResponseTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class ScenarioValidationTest extends TestCase
{
    use ResponseTrait;

    public function testInvoke(): void
    {
        [$scenarioProcessor] = $this->getMocks();
        $scenarioProcessor->expects(static::once())->method('convertFromFsToTas');

        $controller = new FsToTas($scenarioProcessor);
        $response = $controller();
        $content = (\json_decode($response->getContent()));

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals([], $content);
        $this->checkResponse($response);
    }

    public function testError(): void
    {
        [$scenarioProcessor] = $this->getMocks();
        $scenarioProcessor->expects(static::once())->method('convertFromFsToTas')->willThrowException(
            new \Exception('Oh sooorrrryyy'));

        $controller = new FsToTas($scenarioProcessor);
        $response = $controller();
        $content = (\json_decode($response->getContent()));

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals(['Oh sooorrrryyy'], $content);
        $this->checkResponse($response);
    }

    private function getMocks(): array
    {
        return [
            $this->createMock(ScenarioProcessor::class),
        ];
    }
}
