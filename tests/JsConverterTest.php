<?php

namespace OviDigital\Tests;

use OviDigital\JsObjectToJson\JsConverter;
use PHPUnit\Framework\TestCase;

class JsConverterTest extends TestCase
{
    public function testEmptyObject()
    {
        $input = '{}';
        $expected = '{}';

        $converted = JsConverter::convertToJson($input);

        $this->assertEquals($expected, $converted);
    }

    public function testSingleQuotedStrings()
    {
        $input = <<<EOT
{
    key1: 'value 1', 
    'key2': 'value 2'
}
EOT;
        $expected = '{"key1":"value 1","key2":"value 2"}';

        $converted = JsConverter::convertToJson($input);

        $this->assertEquals($expected, $converted);
    }

    public function testMixedQuotedStrings()
    {
        $input = <<<EOT
{
    key1: 'value 1', 
    'key2': "value 2",
    "key3": 'value 3'
}
EOT;
        $expected = '{"key1":"value 1","key2":"value 2","key3":"value 3"}';

        $converted = JsConverter::convertToJson($input);

        $this->assertEquals($expected, $converted);
    }

    public function testStringsContainingQuotes()
    {
        $input = <<<EOT
{
    key1: 'double quote " enclosed in single quotes',
    key2: "double quote \" enclosed in double quotes",
    key3: 'single quote \' enclosed in single quotes',
    key4: "single quote ' enclosed in double quotes"
}
EOT;
        $expected = <<<EOT
{"key1":"double quote \" enclosed in single quotes","key2":"double quote \" enclosed in double quotes","key3":"single quote ' enclosed in single quotes","key4":"single quote ' enclosed in double quotes"}
EOT;

        $converted = JsConverter::convertToJson($input);

        $this->assertEquals($expected, $converted);
    }

    public function testBooleanAndNullValues()
    {
        $input = <<<EOT
{
    key1: null, 
    key2: true,
    key3: false
}
EOT;
        $expected = '{"key1":"null","key2":"true","key3":"false"}';

        $converted = JsConverter::convertToJson($input);

        $this->assertEquals($expected, $converted);
    }

    public function testNumericValues()
    {
        $input = <<<EOT
{
    key1: 0, 
    key2: 1337,
    key3: -45,
    key4: 3.78,
    key4: 2.99792458e8,
    key5: "12345"
}
EOT;

        $expected = <<<EOT
{"key1":0,"key2":1337,"key3":-45,"key4":3.78,"key4":2.99792458e8,"key5":"12345"}
EOT;
;

        $converted = JsConverter::convertToJson($input);

        $this->assertEquals($expected, $converted);
    }

    public function testArrayValues()
    {
        $input = <<<EOT
{
    key1: []
    key2: [
        'item 1', 
        "item 2", 
        "item 3"
    ],
    key3: [true, false, null, 1, 2, 3]
}
EOT;

        $expected = <<<EOT
{"key1":[]"key2":["item 1","item 2","item 3"],"key3":["true","false","null",1,2,3]}
EOT;

        $converted = JsConverter::convertToJson($input);

        $this->assertEquals($expected, $converted);
    }

    public function testNestedObjects()
    {
        $input = <<<EOT
{
    key1: {},
    key2: {
        key21 : 'v21', 
        'key22': "v22", 
        "key23": 123,
        key24: ['x', "y", "z", 1, 2, 3, true, false, null]
    },
    key3: {
        key31 : {
            key311: {
                key3111: [ 
                    {
                        nestedKey11: "str",
                        nestedKey12: 1337,
                        nestedKey13: ['x', "y", "z", 1, 2, 3, true, false, null]
                    },
                    {
                        nestedKey21: "str",
                        nestedKey22: 1337,
                        nestedKey23: ['x', "y", "z", 1, 2, 3, true, false, null]
                    }
                ]
            }
        }
    }
}
EOT;

        $expected = <<<EOT
{"key1":{},"key2":{"key21":"v21","key22":"v22","key23":123,"key24":["x","y","z",1,2,3,"true","false","null"]},"key3":{"key31":{"key311":{"key3111":[{"nestedKey11":"str","nestedKey12":1337,"nestedKey13":["x","y","z",1,2,3,"true","false","null"]},{"nestedKey21":"str","nestedKey22":1337,"nestedKey23":["x","y","z",1,2,3,"true","false","null"]}]}}}}
EOT;

        $converted = JsConverter::convertToJson($input);

        $this->assertEquals($expected, $converted);
    }
}
