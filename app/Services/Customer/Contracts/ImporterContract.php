<?php


namespace App\Services\Customer\Contracts;


interface ImporterContract
{
    /**
     * @param $contract
     * @param array $options
     */
    public function import($contract, array $options = []) : void;
}
