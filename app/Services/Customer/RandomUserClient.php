<?php


namespace App\Services\Customer;


use Illuminate\Support\Collection;
use Illuminate\Http\Client\PendingRequest;
use App\Services\Customer\Contracts\ClientContract;

class RandomUserClient implements ClientContract
{
    protected PendingRequest $request;

    protected array $config;

    /**
     * RandomUserClient constructor.
     * @param PendingRequest $request
     * @param array $config
     */
    public function __construct(PendingRequest $request, array $config)
    {
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * @param array $options
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
     * @param array $options
     * @return array
     */
    private function generateQueryParams(array $options)
    {
        $configOptions = [
            'nationalities' => implode(',', $this->config['nationalities']),
            'inc' => implode(',', $this->config['fields']),
            'results' => (int) ($options['count'] ?? $this->config['count'])
        ];

        return Collection::make($configOptions)->toArray();
    }
}
