<?php

namespace Liip\RD\Output;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class Formatter extends \Symfony\Component\Console\Formatter\OutputFormatter
{
    public function __construct($decorated = null, array $styles = array())
    {
        parent::__construct(true, array(
            'error'     => new OutputFormatterStyle('white', 'red'),
            'green'     => new OutputFormatterStyle('green'),
            'yellow'    => new OutputFormatterStyle('yellow'),
            'question'  => new OutputFormatterStyle('black', 'cyan')
        ));
    }
}
