<?php

declare(strict_types=1);

/**
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       21/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;

trait ResponseTrait
{
    public function checkResponse(Response $response): void
    {
        static::assertEquals(200, $response->getStatusCode());
        static::assertEquals(0, $response->getMaxAge());
        static::assertEquals(0, $response->getTtl());
    }
}
