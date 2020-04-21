<?php

declare(strict_types=1);

/*
 * @author     Eric COURTIAL <e.courtial30@gmail.com>
 * @date       20/04/2020 (dd-mm-YYYY)
 * @licence    MIT
 */

namespace Tests\Controller;

use App\Controller\Home;
use App\Core\Tas\Scenario\ScenarioRepository;
use App\Tests\Controller\ResponseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeTest extends WebTestCase
{
    use ResponseTrait;

    public function testHome(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $response = $client->getResponse();
        static::assertNotFalse(strpos($response->getContent(), 'Fighting Steel'));
        $this->checkResponse($response);
    }

    public function testHomeError(): void
    {
        $scenarioRepo = $this->createMock(ScenarioRepository::class);
        $scenarioRepo->expects(static::once())->method('getAll')->willThrowException(
            new \Exception('Sorry captain!')
        );

        $controller = new Home($scenarioRepo);
        $response = $controller();
        $content = \json_decode($response->getContent());
        static::assertEquals(['Sorry captain!'], $content);
        $this->checkResponse($response);
    }
}
