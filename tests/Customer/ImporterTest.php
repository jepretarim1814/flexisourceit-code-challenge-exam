<?php


namespace Customer;


use Mockery;
use App\Entities\Customer;
use Stubs\ImporterClassStub;
use Concerns\RefreshDatabases;
use App\Services\Customer\Manager;
use Illuminate\Support\Collection;
use App\Services\Customer\Importer;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Events\Dispatcher;
use App\Services\Customer\Models\CustomerImport;

class ImporterTest extends \TestCase
{
    use RefreshDatabases;

    /** @test */
    public function importer_class()
    {
        entity(Customer::class, 10)->make();
        entity(Customer::class)->create([
            'email' => 'email@example.com',
            'firstName' => 'Jefferson',
            'lastName' => 'Thompson'
        ]);

        /** @var Manager @manager */
        $manager = Mockery::mock(Manager::class);
        $manager->shouldReceive('results')->andReturn(new Collection([
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

        $importer = new Importer(
            $manager,
            $this->app->make(EntityManagerInterface::class),
            $dispatcher
        );

        $importer->import(new CustomerImport());

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
        /** @var Manager @manager */
        $manager = Mockery::mock(Manager::class);
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

        $importer = new Importer(
            $manager,
            $this->app->make(EntityManagerInterface::class),
            $dispatcher
        );

        $importer->import(ImporterClassStub::class);

        $this->seeInDatabase('customers', [
            'email' => 'email@example.com',
            'first_name' => 'John',
            'last_name' => 'Johnson'
        ]);
    }
}
