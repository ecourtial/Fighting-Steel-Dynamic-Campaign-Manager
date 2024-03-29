<?php

declare(strict_types=1);

/*
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       20/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Tests\Controller\Fs;

use App\Controller\Fs\FsToTas;
use App\NameSwitcher\ScenarioManager;
use App\Tests\Controller\ResponseTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Symfony\Component\HttpFoundation\JsonResponse;

class FsToTasTest extends TestCase
{
    use ResponseTrait;

    public function testInvoke(): void
    {
        [$scenarioManager, $logger] = $this->getMocks();
        $scenarioManager->expects(static::once())->method('fromFsToTas');

        $controller = new FsToTas($scenarioManager, $logger);
        $response = $controller();
        $content = (\json_decode($response->getContent(), true));

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals(['messages' => ['Translation from FS to TAS completed.']], $content);
        $this->checkResponse($response, 200);
    }

    public function testError(): void
    {
        [$scenarioManager, $logger] = $this->getMocks();
        $scenarioManager->expects(static::once())->method('fromFsToTas')->willThrowException(
            new \Exception('Oh sooorrrryyy')
        );

        $controller = new FsToTas($scenarioManager, $logger);
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
            $this->createMock(ScenarioManager::class),
            new TestLogger(),
        ];
    }
}
