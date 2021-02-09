<?php


namespace App\Services\Customer\Contracts;


use Illuminate\Support\Collection;

interface ClientContract
{
    /**
     * @param array $options
     * @return Collection
     */
    public function results(array $options = []) : Collection;
}