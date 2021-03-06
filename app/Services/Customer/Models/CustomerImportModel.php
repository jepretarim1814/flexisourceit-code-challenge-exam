<?php

namespace App\Services\Customer\Models;

use App\Entities\Customer;
use App\Services\Customer\Contracts\CustomerToImportContract;
use Illuminate\Support\Arr;

class CustomerImportModel implements CustomerToImportContract
{
    /**
     * @var string
     */
    private const MALE = 'male';

    /**
     * @param array|mixed $row
     * @param Customer|null $customer
     * @return Customer
     */
    public function toImport(array $row, ?Customer $customer = null): Customer
    {
        $customer = ($customer ?? new Customer())
            ->setFirstName(Arr::get($row, 'name.first'))
            ->setLastName(Arr::get($row, 'name.last'))
            ->setUsername(Arr::get($row, 'login.username'))
            ->setGender(Arr::get($row, 'gender') === self::MALE ? 0 : 1)
            ->setCountry(Arr::get($row, 'location.country'))
            ->setCity(Arr::get($row, 'location.city'))
            ->setPhone(Arr::get($row, 'phone'))
            ->setPassword(Arr::get($row, 'login.md5'));

        if ($customer !== null) {
            $customer->setEmail(Arr::get($row, 'email'));
        }

        return $customer;
    }
}
