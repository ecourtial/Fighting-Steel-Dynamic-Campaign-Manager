<?php

declare(strict_types=1);

/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 * @licence MIT
 */

namespace Tests\Controller\Test;

use App\Controller\Test\Debug;
use App\Core\Exception\SecurityException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class DebugTest extends TestCase
{
    /** @dataProvider responseDataProvider */
    public function testResponse(string $env): void
    {
        $debugController = new Debug($env);
        $response = $debugController();
        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertEquals('{"msg":"This route is for debug only"}', $response->getContent());
    }

    public function responseDataProvider(): array
    {
        return [
            ['test'],
            ['prod'],
        ];
    }

    public function testError(): void
    {
        try {
            $debugController = new Debug('dev');
            $debugController();
            static::fail('An exception was expected! No code should be executed here!');
        } catch (SecurityException $exception) {
            static::assertEquals('Please make sur that no code is executed!', $exception->getMessage());
        }
    }
}
