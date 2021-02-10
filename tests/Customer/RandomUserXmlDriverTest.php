<?php

namespace Customer;

use App\Services\Customer\Drivers\RandomUserXmlDriver;
use App\Services\Customer\Helpers\ArrayToXmlHelper;
use App\Services\Customer\Helpers\JsonGeneratorDataHelper;
use App\Services\Customer\Helpers\XmlParserHelper;
use Faker\Factory as FactoryFaker;
use Illuminate\Http\Client\Factory;
use TestCase;

class RandomUserXmlDriverTest extends TestCase
{
    protected ArrayToXmlHelper $arrayToXml;

    protected Factory $factory;

    protected JsonGeneratorDataHelper $jsonGenerator;

    protected XmlParserHelper $xmlParserHelper;

    /** @test */
    public function check_if_correct_count(): void
    {
        $count = 2;

        $data = [
            'results' => $this->jsonGenerator->generateJsonResults(FactoryFaker::create(), $count)
        ];
        $xml = $this->arrayToXml->toXml($data, 'user');

        $http = $this->factory->fake([
            '*' => [
                'body' => $xml
            ]
        ])->withHeaders([
            'Content-Type' => 'text/xml;charset=utf-8'
        ]);

        $client = new RandomUserXmlDriver(
            [
                'driver' => 'xml',
                'url' => '/',
                'version' => '1.3',
                'nationalities' => ['au'],
                'fields' => [
                    'name'
                ],
                'count' => $count
            ],
            $http->baseUrl('/'),
            $this->xmlParserHelper
        );

        self::assertCount($count, $client->results());
    }

    protected function setUp(): void
    {
        $this->factory = new Factory();
        $this->arrayToXml = new ArrayToXmlHelper();
        $this->xmlParserHelper = new XmlParserHelper();
        $this->jsonGenerator = new JsonGeneratorDataHelper();
    }
}
