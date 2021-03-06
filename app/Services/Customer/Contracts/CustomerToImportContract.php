<?php

namespace App\Services\Customer\Contracts;

use App\Entities\Customer;

interface CustomerToImportContract
{
    /**
     * @param array|mixed $row
     * @param Customer|null $customer
     * @return Customer
     */
    public function toImport(array $row, ?Customer $customer = null) : Customer;
}
