<?php

namespace Liip\RMT\Tests\Unit\Helpers;

use Liip\RMT\Helpers\JSONHelper;

class JSONHelperTest extends \PHPUnit_Framework_TestCase
{

    public function testIndent()
    {
        $this->assertEquals(<<<JSON
{
   "key1": "val1",
   "key2": [
      "item1",
      2,
      "item[{2"
   ],
   "key3": {
      "bool": false,
      "int": 17,
      "float": 17.9
   }
}
JSON
,
            JSONHelper::format('{"key1":"val1","key2":["item1",2,"item[{2"],"key3":{"bool":false,"int":    17    ,"float":17.9}}'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalid()
    {
        JSONHelper::format('{invalidKey: false}');
    }
}

