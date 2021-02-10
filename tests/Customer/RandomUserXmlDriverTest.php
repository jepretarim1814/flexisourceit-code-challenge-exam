<?php

namespace Customer;

use Illuminate\Support\Collection;
use Illuminate\Http\Client\Factory;
use Stubs\JsonGeneratorDataClassStub;
use App\Services\Customer\Helpers\XmlParserHelper;
use App\Services\Customer\Helpers\ArrayToXmlHelper;
use App\Services\Customer\Drivers\RandomUserXmlDriver;

class RandomUserXmlDriverTest extends \TestCase
{
    protected Factory $factory;

    protected JsonGeneratorDataClassStub $jsonGenerator;

    protected XmlParserHelper $xmlParserhelper;

    protected ArrayToXmlHelper $arrayToXml;

    protected function setUp() : void
    {

        $this->factory = new Factory();
        $this->jsonGenerator = new JsonGeneratorDataClassStub();
        $this->xmlParserhelper = new XmlParserHelper();
        $this->arrayToXml = new ArrayToXmlHelper();
    }

    /** @test */
    public function get_results_using_xml_driver()
    {
        $count = 100;

        $data = [
            'results' => $this->jsonGenerator->generateJsonResults(\Faker\Factory::create(), $count)
        ];
        $xml = $this->arrayToXml->toXml($data, 'user');

        $http = $this->factory->fake([
            '*' => array(
                'body' => $xml
            )
        ])->withHeaders([
            'Content-Type' => 'text/xml;charset=utf-8'
        ]);

        $client = new RandomUserXmlDriver(
            $http->baseUrl('/'),
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
            $this->xmlParserhelper
        );

        $results = $client->results();
        $this->assertCount($count, $results);
        $this->assertInstanceOf(Collection::class, $results);
    }
}
