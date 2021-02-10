<?php

namespace Customer;

use Mockery;
use App\Entities\Customer;
use Illuminate\Support\Collection;
use Stubs\CustomerImporterClassStub;
use Doctrine\ORM\Tools\ToolsException;
use Doctrine\ORM\EntityManagerInterface;
use App\Services\Customer\CustomerManager;
use Illuminate\Contracts\Events\Dispatcher;
use App\Services\Customer\CustomerImporter;
use App\Services\Customer\Helpers\XmlParserHelper;
use App\Services\Customer\Models\CustomerImportModel;

class CustomerImporterTest extends \TestCase
{
    protected XmlParserHelper $xmlParserhelper;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->artisan('doctrine:schema:create');
            entity(Customer::class, 30)->create();
        } catch (ToolsException $th) {

        }

        $this->xmlParserhelper = new XmlParserHelper();

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('doctrine:schema:drop');
        });
    }

    protected function tearDown() : void
    {
        $this->artisan('doctrine:schema:drop', [
            '--force' => true,
        ]);
    }

    /** @test */
    public function json_importer_class()
    {
        entity(Customer::class, 10)->make();
        entity(Customer::class)->create([
            'email' => 'email@example.com',
            'firstName' => 'Jefferson',
            'lastName' => 'Thompson'
        ]);

        /** @var CustomerManager @manager */
        $manager = Mockery::mock(CustomerManager::class);
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
    public function xml_importer_class()
    {
        entity(Customer::class, 10)->make();
        entity(Customer::class)->create([
            'email' => 'email@example.com',
            'firstName' => 'Jefferson',
            'lastName' => 'Thompson'
        ]);

        /** @var CustomerManager @manager */
        $manager = Mockery::mock(CustomerManager::class);
        $manager->shouldReceive('results')
            ->andReturn(new Collection([
                $this->xmlParserhelper->parse($this->xml_sample_data())
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
    public function create_customer_from_entity()
    {
        $entities = entity(Customer::class, 10)->make();
        $this->assertCount(10, $entities);
    }

    /** @test */
    public function customer_with_same_email()
    {
        entity(Customer::class, 10)->make();
        entity(Customer::class)->create([
            'email' => 'email@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe'
        ]);
        /** @var CustomerManager @manager */
        $manager = Mockery::mock(CustomerManager::class);
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

    protected function xml_sample_data()
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
}
