<?php

namespace Customer;

use App\Entities\Customer;
use Doctrine\ORM\EntityManagerInterface;
use App\Repositories\CustomerRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Tools\ToolsException;

class CustomerRepositoryTest extends \TestCase
{
    protected CustomerRepository $repository;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->artisan('doctrine:schema:create');
        } catch (ToolsException $exception) {

        }

        $this->entityManager = $this->app->make(EntityManagerInterface::class);

        $this->repository = $this->entityManager->getRepository(Customer::class);

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
    public function it_should_return_same_count() : void
    {
        entity(Customer::class, 15)->create();
        self::assertCount(15, $this->repository->all());
    }

    /** @test */
    public function check_if_ascending_order_is_correct() : void
    {
        entity(Customer::class)->create([
            'email' => 'foo@email.com'
        ]);

        entity(Customer::class)->create([
            'email' => 'bar@email.com'
        ]);

        $customer = $this->repository->all(Criteria::ASC)->first();

        self::assertSame('foo@email.com', $customer->getEmail());
    }
}
