<?php

namespace App\Services\Customer\Helpers;

class XmlParserHelper
{
    public function parse(string $xml)
    {
        $response = json_decode(json_encode($this->loadXml($xml)), true);
        return $response['results'] ?? [];
    }

    private function loadXml(string $xml)
    {
        try {
            return simplexml_load_string($xml);
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
