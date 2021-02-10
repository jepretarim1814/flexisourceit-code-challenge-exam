<?php

namespace App\Services\Customer\Events;

class CustomerImportEvent
{
    protected array $result;

    protected int $index;

    public function getResult() : array
    {
        return $this->result;
    }

    public function getIndex()
    {
        return $this->index;
    }
}
