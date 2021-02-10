<?php

namespace Customer;

use TestCase;
use Illuminate\Http\Response;
use Concerns\RefreshDatabases;

class CustomerTest extends TestCase
{
    use RefreshDatabases;

    /** @test */
    public function single_customer()
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
    public function customer_next_page()
    {
        $this->get('customers/?page=2');
        $this->assertResponseOk();
    }

    /** @test */
    public function customer_list_with_valid_order_query()
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
    public function customer_not_found()
    {
        $this->get('customers/100000');
        $this->assertResponseStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function customer_lists()
    {
        $this->get('customers');
        $this->assertResponseOk();
    }
}
