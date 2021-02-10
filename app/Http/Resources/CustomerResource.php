<?php

namespace App\Http\Resources;

use App\Entities\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Customer
 */
class CustomerResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request) : array
    {
        return [
            'full_name' => $this->getFullName(),
            'email' => $this->getEmail(),
            'username' => $this->getUsername(),
            'gender' => $this->getGender() === 1 ? 'female' : 'male',
            'country' => $this->getCountry(),
            'city' => $this->getCity(),
            'phone' => $this->getPhone(),
        ];
    }
}
