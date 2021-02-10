<?php

namespace Customer;

use App\Services\Customer\CustomerManager;
use Illuminate\Http\Client\Factory;
use InvalidArgumentException;
use TestCase;

class CustomerManagerTest extends TestCase
{
    /** @test */
    public function custom_driver_not_found(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $manager = new CustomerManager(
            $this->app,
            $this->app['config']->set('customer.importer_drivers.' . __CLASS__),
            $this->app[Factory::class]->fake()
        );

        self::assertEquals($manager, $manager->driver(__CLASS__));
    }

    /** @test */
    public function set_custom_driver_as_default(): void
    {
        $manager = new CustomerManager(
            $this->app,
            $this->app['config']->set('customer.importer_drivers.' . __CLASS__),
            $this->app[Factory::class]->fake()
        );
        $manager->extend(__CLASS__, function () {
            return $this;
        });
        $manager->setDefaultDriver(__CLASS__);
        self::assertSame(__CLASS__, $manager->getDefaultDriver());
    }
}
