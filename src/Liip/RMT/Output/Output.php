<?php

namespace Liip\RMT\Output;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Output\ConsoleOutput;

use Liip\RMT\Information\InteractiveQuestion;


/**
 * Specific console output to allow indentation
 */
class Output extends ConsoleOutput
{
    protected $indentationLevel;
    protected $indentationSize = 4;
    protected $positionIsALineStart = true;

    /** @var FormatterHelper */
    protected $formatterHelper = null;
    /** @var DialogHelper */
    protected $dialogHelper = null;

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


    public function setDialogHelper($dh)
    {
        $this->dialogHelper = $dh;
    }

    public function setFormatterHelper($fh)
    {
        $this->formatterHelper = $fh;
    }

    public function writeTitle($title, $large = true)
    {
        $this->writeEmptyLine();
        $this->writeln($this->formatterHelper->formatBlock($title, 'title', $large));
    }

    public function writeBigTitle($title)
    {
        $this->writeTitle($title, true);
    }

    public function writeSmallTitle($title)
    {
        $this->writeTitle($title, false);
        $this->writeEmptyLine();
    }

    public function writeEmptyLine($repeat=1)
    {
        $this->writeln(array_fill(0,$repeat,''));
    }

    public function askQuestion(InteractiveQuestion $question, $position = null)
    {
        $text = ($position!==null?$position.') ':null).$question->getFormatedText();

        if ($question->isHiddenAnswer()) {
            return $this->dialogHelper->askHiddenResponseAndValidate($this, $text, $question->getValidator(), false);
        }

        return $this->dialogHelper->askAndValidate($this, $text, $question->getValidator(), false, $question->getDefault());
    }

    public function askConfirmation($text)
    {
        return $this->dialogHelper->askConfirmation($this, $text);
    }

}
