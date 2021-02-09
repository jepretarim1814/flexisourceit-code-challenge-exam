<?php


namespace Concerns;


use App\Entities\Customer;
use Doctrine\ORM\Tools\ToolsException;

trait RefreshDatabases
{
    protected function setUp(): void
    {
        parent::setUp();
        try {
            $this->artisan('doctrine:schema:create');
            entity(Customer::class, 30)->create();
        } catch (ToolsException $th) {

        }

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
}
