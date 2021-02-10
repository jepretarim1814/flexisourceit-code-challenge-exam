<?php

namespace App\Services\Customer\Helpers;

class XmlParserHelper
{
    /**
     * @param string $xml
     * @return array|mixed
     */
    public function parse(string $xml) : array
    {
        $response = json_decode(json_encode($this->loadXml($xml)), true);
        return $response['results'] ?? [];
    }

    /**
     * @param string $xml
     * @return \SimpleXMLElement|null
     */
    private function loadXml(string $xml): ?\SimpleXMLElement
    {
        try {
            return simplexml_load_string($xml);
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
