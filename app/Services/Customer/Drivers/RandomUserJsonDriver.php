<?php

namespace App\Services\Customer\Drivers;

use Illuminate\Support\Collection;
use Illuminate\Http\Client\PendingRequest;
use App\Services\Customer\Contracts\CustomerDriverContract;

class RandomUserJsonDriver implements CustomerDriverContract
{
    protected array $config;

    protected PendingRequest $request;

    /**
     * RandomUserJsonDriver constructor.
     * @param mixed[] $config
     * @param PendingRequest $request
     */
    public function __construct(array $config, PendingRequest $request)
    {
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * @param mixed[] $options
     * @return Collection
     */
    public function results(array $options = []) : Collection
    {
        $request = $this->request->get(
            $this->config['version'],
            $this->generateQueryParams($options)
        );

        return new Collection($request->json('results'));
    }

    /**
     * @param mixed[] $options
     * @return mixed[]
     */
    private function generateQueryParams(array $options) : array
    {
        return [
            'nationalities' => implode(',', $this->config['nationalities']),
            'inc' => implode(',', $this->config['fields']),
            'results' => (int) ($options['count'] ?? $this->config['count']),
            'format' => 'json'
        ];
    }
}
