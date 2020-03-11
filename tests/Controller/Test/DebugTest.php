<?php

declare(strict_types=1);

/*
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       29/02/2020 (dd-mm-YYYY)
 */

use App\Controller\Test\Debug;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class DebugTest extends TestCase
{
    /** @dataProvider responseDataProvider */
    public function testResponse(string $env): void
    {
        $debugController = new Debug($env);
        static::assertInstanceOf(JsonResponse::class, $debugController());
    }

    public function responseDataProvider(): array
    {
        return [
            ['test'],
            ['prod'],
        ];
    }
}
