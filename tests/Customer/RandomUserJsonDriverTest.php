<?php

namespace Customer;

use Illuminate\Support\Collection;
use Illuminate\Http\Client\Factory;
use App\Services\Customer\Drivers\RandomUserJsonDriver;
use Stubs\JsonGeneratorDataClassStub;

class RandomUserJsonDriverTest extends \TestCase
{

    protected Factory $factory;

    protected JsonGeneratorDataClassStub $jsonGenerator;

    protected function setUp() : void
    {
        parent::setUp();
        $this->factory = new Factory();
        $this->jsonGenerator = new JsonGeneratorDataClassStub();
    }

    /** @test */
    public function get_results_using_json_driver()
    {
        $count = 100;
        $http = $this->factory->fake([
            '*' => [
                'results' => $this->jsonGenerator->generateJsonResults(\Faker\Factory::create(), $count)
            ]
        ]);
        $client = new RandomUserJsonDriver(
            $http->baseUrl('/'),
            [
                'url' => '/',
                'version' => '1.3',
                'nationalities' => ['au'],
                'fields' => [
                    'name'
                ],
                'count' => $count
            ]
        );
        $results = $client->results();
        $this->assertCount($count, $results);
        $this->assertInstanceOf(Collection::class, $results);
    }

}
