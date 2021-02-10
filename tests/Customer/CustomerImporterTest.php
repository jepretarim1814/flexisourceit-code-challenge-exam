<?php

namespace Customer;

use App\Entities\Customer;
use App\Services\Customer\Contracts\CustomerManagerContract;
use App\Services\Customer\CustomerImporter;
use App\Services\Customer\Helpers\XmlParserHelper;
use App\Services\Customer\Models\CustomerImportModel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\ToolsException;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;
use Mockery;
use Stubs\CustomerImporterClassStub;
use TestCase;

/**
 * @covers \App\Services\Customer\CustomerImporter
 */
class CustomerImporterTest extends TestCase
{
    protected XmlParserHelper $xmlParserHelper;

    /** @test */
    public function json_importer_class(): void
    {
        entity(Customer::class, 10)->make();
        entity(Customer::class)->create([
            'email' => 'email@example.com',
            'firstName' => 'Jefferson',
            'lastName' => 'Thompson'
        ]);

        /** @var CustomerManagerContract @manager */
        $manager = Mockery::mock(CustomerManagerContract::class);
        $manager->shouldReceive('results')
            ->andReturn(new Collection([
                [
                    'email' => 'email@example.com',
                    'name' => [
                        'first' => 'Jefferson',
                        'last' => 'Johnson'
                    ],
                    'location' => [
                        'country' => 'Philippines',
                        'city' => 'Bacoor'
                    ],
                    'login' => [
                        'username' => 'testUsername',
                        'md5' => md5('password')
                    ],
                    'phone' => '(02) 222-2222'
                ]
            ]));

        /** @var Dispatcher @dispatcher */
        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher->shouldReceive('dispatch')->andReturnNull();

        $importer = new CustomerImporter(
            $manager,
            $this->app->make(EntityManagerInterface::class),
            $dispatcher
        );

        $importer->import(new CustomerImportModel());

        $this->seeInDatabase('customers', [
            'email' => 'email@example.com',
            'first_name' => 'Jefferson',
            'last_name' => 'Johnson',
        ]);
    }

    /** @test */
    public function xml_importer_class(): void
    {
        entity(Customer::class, 10)->make();
        entity(Customer::class)->create([
            'email' => 'email@example.com',
            'firstName' => 'Jefferson',
            'lastName' => 'Thompson'
        ]);

        /** @var CustomerManagerContract @manager */
        $manager = Mockery::mock(CustomerManagerContract::class);
        $manager->shouldReceive('results')
            ->andReturn(new Collection([
                $this->xmlParserHelper->parse($this->xml_sample_data())
            ]));

        /** @var Dispatcher @dispatcher */
        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher->shouldReceive('dispatch')->andReturnNull();

        $importer = new CustomerImporter(
            $manager,
            $this->app->make(EntityManagerInterface::class),
            $dispatcher
        );

        $importer->import(new CustomerImportModel());

        $this->seeInDatabase('customers', [
            'email' => 'email@example.com',
            'first_name' => 'Jefferson',
            'last_name' => 'Johnson',
        ]);
    }

    private function xml_sample_data(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
            <user>
                <results>
                    <gender>female</gender>
                    <name>
                        <first>Jefferson</first>
                        <last>Johnson</last>
                    </name>
                    <location>
                        <city>Bacoor</city>
                        <country>Philippines</country>
                    </location>
                    <email>email@example.com</email>
                    <login>
                        <username>angryfish388</username>
                        <md5>' . md5('password') . '</md5>
                    </login>
                    <phone>(473)-958-6419</phone>
                </results>
            </user>';
    }

    /** @test */
    public function create_customer_from_entity(): void
    {
        $entities = entity(Customer::class, 10)->make();
        self::assertCount(10, $entities);
    }

    /** @test */
    public function customer_with_same_email(): void
    {
        entity(Customer::class, 10)->make();
        entity(Customer::class)->create([
            'email' => 'email@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe'
        ]);
        /** @var CustomerManagerContract @manager */
        $manager = Mockery::mock(CustomerManagerContract::class);
        $manager->shouldReceive('results')->andReturn(new Collection([
            [
                'email' => 'email@example.com',
                'name' => [
                    'first' => 'John',
                    'last' => 'Johnson'
                ],
                'location' => [
                    'country' => 'Philippines',
                    'city' => 'Bacoor'
                ],
                'login' => [
                    'username' => 'testUsername',
                    'md5' => md5('password')
                ],
                'phone' => '(02) 222-2222'
            ]
        ]));

        /** @var Dispatcher $dispatcher */
        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher->shouldReceive('dispatch')->andReturnNull();

        $importer = new CustomerImporter(
            $manager,
            $this->app->make(EntityManagerInterface::class),
            $dispatcher
        );

        $importer->import(CustomerImporterClassStub::class);

        $this->seeInDatabase('customers', [
            'email' => 'email@example.com',
            'first_name' => 'John',
            'last_name' => 'Johnson'
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->artisan('doctrine:schema:create');
            entity(Customer::class, 30)->create();
        } catch (ToolsException $th) {

        }

        $this->xmlParserHelper = new XmlParserHelper();

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('doctrine:schema:drop');
        });
    }

    protected function tearDown(): void
    {
        $this->artisan('doctrine:schema:drop', [
            '--force' => true,
        ]);
    }
}
