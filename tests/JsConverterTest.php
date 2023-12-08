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

        $this->assertEquals([], JsConverter::convertToArray($input));
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

        $expectedPhpArray = [
            "key1" => "value 1",
            "key2" => "value 2"
        ];
        $this->assertEquals($expectedPhpArray, JsConverter::convertToArray($input));
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
        $expected = '{"key1":null,"key2":true,"key3":false}';

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

    public function testTrailingCommas()
    {
        $input = <<<EOT
{
    key1: [
        'item 1',
        "item 2",
        "item 3",
    ],
    key2: [true, false, null, 1, 2, 3,],
    key3: {
        key31 : {
            key311: {
                key3111: [
                    {
                        nestedKey11: "str",
                        nestedKey12: 1337,
                        nestedKey13: ['x', "y", "z", 1, 2, 3, true, false, null],
                    },
                    {
                        nestedKey21: "str",
                        nestedKey22: 1337,
                        nestedKey23: ['x', "y", "z", 1, 2, 3, true, false, null] ,
                    },
                ]
            },
        },
    },
}
EOT;

        $expected = <<<EOT
{"key1":["item 1","item 2","item 3"],"key2":["true","false","null",1,2,3],"key3":{"key31":{"key311":{"key3111":[{"nestedKey11":"str","nestedKey12":1337,"nestedKey13":["x","y","z",1,2,3,"true","false","null"]},{"nestedKey21":"str","nestedKey22":1337,"nestedKey23":["x","y","z",1,2,3,"true","false","null"]}]}}}}
EOT;

        $converted = JsConverter::convertToJson($input);

        $this->assertEquals($expected, $converted);
    }

    public function testEmbeddedHtml()
    {
        $input = <<<EOT
{
    key1: '<p style="color: red;">text</p>',
    key2: "<p style=\"color: red;\">text</p>",
    key3: "<p style='color: red;'>text</p>"
}
EOT;
        $expected = <<<EOT
{"key1":"<p style=\"color: red;\">text</p>","key2":"<p style=\"color: red;\">text</p>","key3":"<p style='color: red;'>text</p>"}
EOT;
;

        $converted = JsConverter::convertToJson($input);

        $this->assertEquals($expected, $converted);
    }

    public function testFunctionsRemoval()
    {
        $input = <<<EOT
{
    key1: (var) => 'Test',
    key2: (var) => "Test",
    key3: var => 'Test',
    key4: var => "Test",
    key5: () => 'Test',
    key6: () => { return 'Test'; },
    key7: (var) => {
        return 'Test';
    },
    key8: () => {
        return 'Test';
    },
    key9: () => {
        if (complex) {
            return 'Test';
        }

        return 'Test';
    },
    key10() {
        return 'Test';
    },
    foo: "bar"
}
EOT;
        $expected = <<<EOT
{"foo":"bar"}
EOT;
;

        $converted = JsConverter::convertToJson($input);

        $this->assertEquals($expected, $converted);
    }

    public function testSingleLineComments()
    {
        $input = <<<EOT
{
    // top comment
    'key1': "value 1", // some other comment with ' quotes "
    key2: 'value 2', // @see someFunction() or https://www.example.com
    key3: false
}
EOT;
        $expected = '{"key1":"value 1","key2":"value 2","key3":false}';

        $converted = JsConverter::convertToJson($input);

        $this->assertEquals($expected, $converted);
    }


    public function testMultiLineValues()
    {
        $input = <<<EOT
{
    key: `
      some test value1
      some test value1
   `
}
EOT;
        $expected = '{"key":"
      some test value1
      some test value1
   "}';

        $converted = JsConverter::convertToJson($input);

        $this->assertEquals($expected, $converted);
    }

    public function testSingleQuoteBetweenDoubleQuotes()
    {
        $input = <<<EOT
{id:304,name: "Anna's house",jobs:[{id:3041,title: "Drive to Anna's house.",label: "Drive",steps:3}]}
EOT;
        $expected = <<<EOT
{"id":304,"name":"Anna's house","jobs":[{"id":3041,"title":"Drive to Anna's house.","label":"Drive","steps":3}]}
EOT;

        $converted = JsConverter::convertToJson($input);

        $this->assertEquals($expected, $converted);

        $input = <<<EOT
{id:304,name: "Anna's house's room",jobs:[{id:3041,title: "Drive to Anna's house's room.",label: "Drive",steps:3}]}
EOT;
        $expected = <<<EOT
{"id":304,"name":"Anna's house's room","jobs":[{"id":3041,"title":"Drive to Anna's house's room.","label":"Drive","steps":3}]}
EOT;

        $converted = JsConverter::convertToJson($input);

        $this->assertEquals($expected, $converted);
    }

    public function testURIs() {
        $input = <<<EOT
{
    key1: 'http://www.example.com/foo.jpg',
    key2: {
        key22: "https://www.example.com/bar.jpg"
    },
    key3: ['public://path/to/file.jpg', 'https://www.example.com/path/to/file.jpg', 'src/**/*.{jpg,png}'],
    key4: "./path/to/../file.jpg",
}
EOT;
        $expected = <<<EOT
{"key1":"http://www.example.com/foo.jpg","key2":{"key22":"https://www.example.com/bar.jpg"},"key3":["public://path/to/file.jpg","https://www.example.com/path/to/file.jpg","src/**/*.{jpg,png}"],"key4":"./path/to/../file.jpg"}
EOT;

    $converted = JsConverter::convertToJson($input);
    $this->assertEquals($expected, $converted);
    }
}
