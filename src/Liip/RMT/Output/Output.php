<?php

namespace Liip\RMT\Output;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Specific console output to allow indentation
 */
class Output extends ConsoleOutput
{
    protected $indentationLevel;
    protected $indentationSize = 4;
    protected $positionIsALineStart = true;

    public function __construct($verbosity = self::VERBOSITY_NORMAL, $decorated = null, OutputFormatterInterface $formatter = null)
    {
        // Use our own formatter
        parent::__construct($verbosity, $decorated, $formatter);
        // set some custom styles
        $this->getFormatter()->setStyle('error', new OutputFormatterStyle('white', 'red'));
        $this->getFormatter()->setStyle('green', new OutputFormatterStyle('green'));
        $this->getFormatter()->setStyle('yellow', new OutputFormatterStyle('yellow'));
        $this->getFormatter()->setStyle('question', new OutputFormatterStyle('black', 'cyan'));
        $this->getFormatter()->setStyle('title', new OutputFormatterStyle('white', 'blue'));
    }

    public function doWrite($message, $newline)
    {
        // In case the $message is multi lines
        $message = str_replace(PHP_EOL, PHP_EOL.$this->getIndentPadding(), $message);

        if ($this->positionIsALineStart) {
            $message = $this->getIndentPadding().$message;
        }

        $this->positionIsALineStart = $newline;
        parent::doWrite($message, $newline);
    }

    public function indent($repeat = 1)
    {
        $this->indentationLevel += $repeat;
    }

    public function unIndent($repeat = 1)
    {
        $this->indentationLevel -= $repeat;
    }

    public function resetIndentation()
    {
        $this->indentationLevel = 0;
    }

    protected function getIndentPadding()
    {
        return str_pad("", $this->indentationLevel * $this->indentationSize);
    }

}
