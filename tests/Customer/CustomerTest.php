<?php

namespace Customer;

use App\Entities\Customer;
use TestCase;
use Illuminate\Http\Response;
use Doctrine\ORM\Tools\ToolsException;

class CustomerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        try {
            $this->artisan('doctrine:schema:create');
            entity(Customer::class, 10)->create();
        } catch (ToolsException $th) {

        }

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('doctrine:schema:drop', [
                '--force' => true
            ]);
        });
    }

    /** @test */
    public function single_customer() : void
    {
        $this->get('customers/1');
        $this->assertResponseOk();
        $this->seeJsonStructure([
            'data' => [
                'full_name',
                'email',
                'username',
                'gender',
                'country',
                'city',
                'phone'
            ],
        ]);
    }

    /** @test */
    public function customer_next_page() : void
    {
        $this->get('customers/?page=2');
        $this->assertResponseOk();
    }

    /** @test */
    public function customer_list_with_valid_order_query() : void
    {
        $this->get('customers/?order=ASC');
        $this->assertResponseOk();
        $this->get('customers/?order=DESC');
        $this->assertResponseOk();
        $this->seeJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'full_name',
                    'email',
                    'country',
                ],
            ],
        ]);
    }

    /** @test */
    public function customer_not_found() : void
    {
        $this->get('customers/100000');
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function customer_lists() : void
    {
        $this->get('customers');
        $this->assertResponseOk();
    }
}
