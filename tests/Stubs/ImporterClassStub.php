<?php
declare(strict_types=1);


namespace Stubs;


use App\Entities\Customer;
use Illuminate\Support\Arr;
use App\Services\Customer\Contracts\ToImportContract;

class ImporterClassStub implements ToImportContract
{
    /**
     * @param array|mixed $row
     * @param Customer|null $customer
     * @return Customer
     */
    public function toImport($row, Customer $customer = null) : Customer
    {
        $customer = ($customer ?? new Customer())
            ->setFirstName(Arr::get($row, 'name.first'))
            ->setLastName(Arr::get($row, 'name.last'))
            ->setUsername(Arr::get($row, 'login.username'))
            ->setGender(Arr::get($row, 'gender') === 'male' ? 0 : 1)
            ->setCountry(Arr::get($row, 'location.country'))
            ->setCity(Arr::get($row, 'location.city'))
            ->setPhone(Arr::get($row, 'phone'))
            ->setPassword(Arr::get($row, 'login.md5'));

        if ($customer !== null)
        {
            $customer->setEmail(Arr::get($row, 'email'));
        }

        return $customer;
    }
}
