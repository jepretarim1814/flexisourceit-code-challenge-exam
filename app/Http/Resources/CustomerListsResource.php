<?php

namespace App\Http\Resources;

use App\Entities\Customer;
use Illuminate\Http\Request ;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Customer
 */
class CustomerListsResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request) : array
    {
        return [
            'id' => (int) $this->getId(),
            'full_name' => $this->getFullName(),
            'email' => $this->getEmail(),
            'country' => $this->getCountry()
        ];
    }
}
