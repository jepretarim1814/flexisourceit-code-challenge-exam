<?php

namespace App\Services\Customer\Helpers;

use DOMDocument;

class ArrayToXmlHelper
{
    /**
     * @param $data
     * @param string $rootNodeName
     * @param null|\SimpleXMLElement $xml
     * @return string|string[]
     */
    public function toXml($data, string $rootNodeName = 'data', &$xml = null) : string
    {
        if (is_null($xml)) {
            $xml = simplexml_load_string(stripslashes("<?xml version='1.0' encoding='utf-8'?><user></user>"));
        }

        foreach ($data as $key => $value) {
            $numeric = false;
            if (is_numeric($key)) {
                $numeric = 1;
                $key = $rootNodeName;
            }

            $key = preg_replace('/[^a-z0-9\-\_\.\:]/i', '', $key);

            $attrs = false;

            if (is_array($value)) {
                if (isset($value['attr_id'])) {
                    $attrs['id'] = $value['attr_id'];
                    unset($value['attr_id']);
                }
                if (isset($value['attr_title'])) {
                    $attrs['title'] = $value['attr_title'];
                    unset($value['attr_title']);
                }
                if (isset($value['attr_shortDescripton'])) {
                    $attrs['shortDescripton'] = $value['attr_shortDescripton'];
                    unset($value['attr_shortDescripton']);
                }
                if (isset($value['attr_theme'])) {
                    $attrs['theme'] = $value['attr_theme'];
                    unset($value['attr_theme']);
                }
                if (isset($value['attr_longDescripton'])) {
                    $attrs['longDescripton'] = $value['attr_longDescripton'];
                    unset($value['attr_longDescripton']);
                }
            }

            if (is_array($value)) {
                if ($this->isAssoc($value) || $numeric) {
                    if (method_exists('SimpleXMLElement', 'addChild')) {
                        $node = $xml->addChild($key, null, 'http://www.lcc.arts.ac.uk/');
                        if ($attrs) {
                            foreach ($attrs as $key1 => $attribute) {
                                $node->addAttribute($key1, $attribute);
                            }
                        }
                    }
                } else {
                    $node =$xml;
                }

                if ($numeric) {
                    $key = 'anon';
                }
                $this->toXml($value, $key, $node);
            } else {
                if (method_exists('SimpleXMLElement', 'addChild')) {
                    $childnode = $xml->addChild($key, htmlspecialchars($value), 'http://www.lcc.arts.ac.uk/');
                    if ($attrs) {
                        foreach ($attrs as $key1 => $attribute) {
                            $childnode->addAttribute($key1, $attribute);
                        }
                    }
                }
            }
        }

        $doc = new DOMDocument('1.0');
        $doc->preserveWhiteSpace = false;

        return $this->fixCDATA($xml->asXML());
    }

    /**
     * @param string $string
     * @return string|string[]
     */
    public function fixCDATA(string $string) : string
    {
        $find[]     = '&lt;![CDATA[';
        $replace[] = '<![CDATA[';
        $find[]     = ']]&gt;';
        $replace[] = ']]>';

        $string = str_ireplace($find, $replace, $string);

        return $string;
    }

    /**
     * @param array $array
     * @return bool
     */
    public function isAssoc(array $array) : bool
    {
        return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
    }
}
