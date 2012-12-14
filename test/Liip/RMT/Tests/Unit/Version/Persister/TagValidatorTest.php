<?php

namespace Liip\RMT\Tests\Version\Persister;

use Liip\RMT\Version\Persister\TagValidator;

class TagValidatorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider getTagData
     */
    public function testIsValid($tag, $result, $regex, $tagPrefix='')
    {
        $validator = new TagValidator($regex, $tagPrefix);
        $this->assertEquals($result, $validator->isValid($tag));
    }

    public function getTagData()
    {
        $simpleRegEx = '\d+';
        $semanticRegEx = '\d+\.\d+\.\d+';
        return array(
            array('1', true, $simpleRegEx),
            array('23', true, $simpleRegEx),
            array('3d', false, $simpleRegEx),
            array('v_23', true, $simpleRegEx, 'v_'),
            array('v-23',  false, $simpleRegEx, 'v_'),
            array('v_3d',  false, $simpleRegEx, 'v_'),
            array('1.0.3', true, $semanticRegEx ),
            array('3.0.3.7', false, $semanticRegEx),
            array('3.b.3',  false, $semanticRegEx),
            array('dev_3.3.3', true, $semanticRegEx, 'dev_'),
            array('dev_3.3.3.7', false, $semanticRegEx, 'dev_')
        );
    }

    public function testFiltrateList()
    {
        $validator = new TagValidator('\d');
        $this->assertEquals(
            array('1','3'),
            $validator->filtrateList(array('a', '1', '3s', '3')
        ));
    }
}
