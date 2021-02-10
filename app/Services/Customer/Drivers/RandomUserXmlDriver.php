<?php

namespace App\Services\Customer\Drivers;

use Illuminate\Support\Collection;
use Illuminate\Http\Client\PendingRequest;
use App\Services\Customer\Helpers\XmlParserHelper;
use App\Services\Customer\Contracts\CustomerDriverContract;

class RandomUserXmlDriver implements CustomerDriverContract
{
    protected array $config;

    protected PendingRequest $request;

    protected XmlParserHelper $helper;

    /**
     * RandomUserXmlDriver constructor.
     * @param mixed[] $config
     * @param PendingRequest $request
     * @param XmlParserHelper $helper
     */
    public function __construct(array $config, PendingRequest $request, XmlParserHelper $helper)
    {
        $this->config = $config;
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * @param array $options
     * @return Collection
     * @throws \JsonException
     */
    public function results(array $options = []) : Collection
    {
        $request = $this->request->get(
            $this->config['version'],
            $this->generateQueryParams($options)
        );

        return new Collection($this->helper->parse($request->json('body') ?? $request->body()));
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
            'format' => 'xml'
        ];
    }
}
