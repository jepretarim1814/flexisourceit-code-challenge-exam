<?php

namespace App\Services\Customer\Events;

class CustomerImportEvent
{
    protected array $result;

    protected int $index;

    /**
     * @return array
     */
    public function getResult() : array
    {
        return $this->result;
    }

    /**
     * @return int
     */
    public function getIndex() : int
    {
        return $this->index;
    }
}
