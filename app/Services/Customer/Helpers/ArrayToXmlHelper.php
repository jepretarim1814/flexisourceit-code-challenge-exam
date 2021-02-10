<?php

namespace App\Services\Customer\Helpers;

use DOMDocument;

class ArrayToXmlHelper
{
    public function toXml($data, $rootNodeName = 'data', &$xml = null)
    {
        // turn off compatibility mode as simple xml throws a wobbly if you don't.
        if (ini_get('zend.ze1_compatibility_mode') == 1) {
            ini_set('zend.ze1_compatibility_mode', 0);
        }

        if (is_null($xml)) {
            $xml = simplexml_load_string(stripslashes("<?xml version='1.0' encoding='utf-8'?><user></user>"));
        }

        // loop through the data passed in.
        foreach ($data as $key => $value) {
            // no numeric keys in our xml please!
            $numeric = false;
            if (is_numeric($key)) {
                $numeric = 1;
                $key = $rootNodeName;
            }

            // delete any char not allowed in XML element names
            $key = preg_replace('/[^a-z0-9\-\_\.\:]/i', '', $key);

            //check to see if there should be an attribute added (expecting to see _id_)
            $attrs = false;

            //if there are attributes in the array (denoted by attr_**) then add as XML attributes
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

            // if there is another array found recursively call this function
            if (is_array($value)) {
                if ($this->isAssoc($value) || $numeric) {
                    // older SimpleXMLElement Libraries do not have the addChild Method
                    if (method_exists('SimpleXMLElement', 'addChild')) {
                        $node = $xml->addChild($key, null, 'http://www.lcc.arts.ac.uk/');
                        if ($attrs) {
                            foreach ($attrs as $key => $attribute) {
                                $node->addAttribute($key, $attribute);
                            }
                        }
                    }
                } else {
                    $node =$xml;
                }

                // recrusive call.
                if ($numeric) {
                    $key = 'anon';
                }
                $this->toXml($value, $key, $node);
            } else {
                // older SimplXMLElement Libraries do not have the addChild Method
                if (method_exists('SimpleXMLElement', 'addChild')) {
                    $childnode = $xml->addChild($key, htmlspecialchars($value), 'http://www.lcc.arts.ac.uk/');
                    if ($attrs) {
                        foreach ($attrs as $key => $attribute) {
                            $childnode->addAttribute($key, $attribute);
                        }
                    }
                }
            }
        }

        // pass back as unformatted XML
        //return $xml->asXML('data.xml');

        // if you want the XML to be formatted, use the below instead to return the XML
        $doc = new DOMDocument('1.0');
        $doc->preserveWhiteSpace = false;

        return $this->fixCDATA($xml->asXML());
    }

    public function fixCDATA(string $string)
    {
        //fix CDATA tags
        $find[]     = '&lt;![CDATA[';
        $replace[] = '<![CDATA[';
        $find[]     = ']]&gt;';
        $replace[] = ']]>';

        $string = str_ireplace($find, $replace, $string);
        return $string;
    }

    // determine if a variable is an associative array
    public function isAssoc(array $array)
    {
        return (is_array($array) && 0 !== count(array_diff_key($array, array_keys(array_keys($array)))));
    }
}
