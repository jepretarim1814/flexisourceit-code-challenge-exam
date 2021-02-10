<?php

namespace App\Services\Customer\Drivers;

use Illuminate\Support\Collection;
use Illuminate\Http\Client\PendingRequest;
use App\Services\Customer\Helpers\XmlParserHelper;
use App\Services\Customer\Contracts\CustomerClientContract;

class RandomUserXmlDriver implements CustomerClientContract
{
    protected PendingRequest $request;

    protected array $config;

    protected XmlParserHelper $helper;

    public function __construct(PendingRequest $request, array $config, XmlParserHelper $helper)
    {
        $this->request = $request;
        $this->config = $config;
        $this->helper = $helper;
    }

    public function results(array $options = []) : Collection
    {
        $request = $this->request->get(
            $this->config['version'],
            $this->generateQueryParams($options)
        );
        return new Collection($this->helper->parse($request->json('body') ?? $request->body()));
    }

    /**
     * @param array $options
     * @return array
     */
    private function generateQueryParams(array $options) : array
    {
        return [
            'nationalities' => implode(',', $this->config['nationalities']),
            'inc' => implode(',', $this->config['fields']),
            'results' => (int) ($options['count'] ?? $this->config['count']),
            'format' => $this->config['driver']
        ];
    }
}
