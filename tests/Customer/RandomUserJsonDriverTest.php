<?php

namespace Customer;

use App\Services\Customer\Drivers\RandomUserJsonDriver;
use App\Services\Customer\Helpers\JsonGeneratorDataHelper;
use Faker\Factory as FactoryFaker;
use Illuminate\Http\Client\Factory;
use TestCase;

class RandomUserJsonDriverTest extends TestCase
{
    protected Factory $factory;

    protected JsonGeneratorDataHelper $jsonGenerator;

    /** @test */
    public function check_if_correct_count(): void
    {
        $count = 100;
        $http = $this->factory->fake([
            '*' => [
                'results' => $this->jsonGenerator->generateJsonResults(FactoryFaker::create(), $count)
            ]
        ]);
        $client = new RandomUserJsonDriver(
            [
                'url' => '/',
                'version' => '1.3',
                'nationalities' => ['au'],
                'fields' => [
                    'name'
                ],
                'count' => $count
            ],
            $http->baseUrl('/')
        );

        self::assertCount($count, $client->results());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new Factory();
        $this->jsonGenerator = new JsonGeneratorDataHelper();
    }
}
