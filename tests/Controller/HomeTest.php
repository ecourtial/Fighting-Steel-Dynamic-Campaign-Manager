<?php

declare(strict_types=1);

/*
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       20/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace App\Tests\Controller;

use App\Controller\Home;
use App\Core\Tas\Scenario\ScenarioRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeTest extends WebTestCase
{
    use ResponseTrait;

    public function testHome(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $response = $client->getResponse();
        $this->checkResponse($response, 200);
    }

    public function testHomeError(): void
    {
        $scenarioRepo = $this->createMock(ScenarioRepository::class);
        $scenarioRepo->expects(static::once())->method('getAll')->willThrowException(
            new \Exception('Sorry captain!')
        );

        $controller = new Home($scenarioRepo);
        $response = $controller();
        $content = \json_decode($response->getContent(), true);
        static::assertEquals(['messages' => ['Sorry captain!']], $content);
        $this->checkResponse($response, 500);
    }
}
