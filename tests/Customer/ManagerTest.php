<?php


namespace Customer;


use App\Services\Customer\Manager;
use Illuminate\Http\Client\Factory;

class ManagerTest extends \TestCase
{
    /**
     * @test
     * @expectException \InvalidArgumentException::class
     */
    public function custom_driver_not_found()
    {
        $this->expectException(\InvalidArgumentException::class);

        $manager = new Manager(
            $this->app,
            $this->app['config']->set('customer.importer_drivers.' . __CLASS__),
            $this->app[Factory::class]->fake()
        );

        $this->assertEquals($manager, $manager->driver(__CLASS__));
    }

    /** @test */
    public function set_custom_driver_as_default()
    {
        $manager = new Manager(
            $this->app,
            $this->app['config']->set('customer.importer_drivers.' . __CLASS__),
            $this->app[Factory::class]->fake()
        );
        $manager->extend(__CLASS__, function () {
            return $this;
        });
        $manager->setDefaultDriver(__CLASS__);
        $this->assertSame(__CLASS__, $manager->getDefaultDriver());
    }
}
