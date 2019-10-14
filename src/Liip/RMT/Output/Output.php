<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Output;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Liip\RMT\Information\InteractiveQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

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
    /** @var DialogHelper|QuestionHelper */
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
        return str_pad('', $this->indentationLevel * $this->indentationSize);
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

    public function writeEmptyLine($repeat = 1)
    {
        $this->writeln(array_fill(0, $repeat, ''));
    }

    // when we drop symfony 2.3 support, we should switch to the new QuestionHelper (since 2.5) and see if we need these methods at all anymore
    // QuestionHelper does about the same as we do here.
    public function askQuestion(InteractiveQuestion $question, $position = null, InputInterface $input = null)
    {
        if (class_exists('Symfony\Component\Console\Helper\QuestionHelper')) {
            $helper = new \Symfony\Component\Console\Helper\QuestionHelper();
            return $helper->ask($input, $this, $question->asSymfonyQuestion());
        }

        $text = ($position !== null ? $position .') ' : null) . $question->getFormatedText();

        if ($this->dialogHelper instanceof QuestionHelper) {
            if (!$input) {
                throw new \InvalidArgumentException('With symfony 3, the input stream may not be null');
            }
            $q = new Question($text, $question->getDefault());
            $q->setValidator($question->getValidator());
            if ($question->isHiddenAnswer()) {
                $q->setHidden(true);
            }

            return $this->dialogHelper->ask($input, $this, $q);
        }

        if ($this->dialogHelper instanceof DialogHelper) {
            if ($question->isHiddenAnswer()) {
                return $this->dialogHelper->askHiddenResponseAndValidate($this, $text, $question->getValidator(), false);
            }

            return $this->dialogHelper->askAndValidate($this, $text, $question->getValidator(), false, $question->getDefault());
        }

        throw new \RuntimeException("Invalid dialogHelper");
    }

    /**
     * @deprecated when we drop symfony 2.3 support, we should switch to the QuestionHelper (since 2.5) and drop this method as it adds no value
     * @param string $text
     * @param InputInterface|null $input
     * @return mixed
     */
    public function askConfirmation($text, InputInterface $input = null)
    {
        @trigger_error(sprintf("The %s() method is deprecated. Use askQuestion instead.", __METHOD__), E_USER_DEPRECATED);
        if ($this->dialogHelper instanceof QuestionHelper) {
            if (!$input) {
                throw new \InvalidArgumentException('With symfony 3, the input stream may not be null');
            }
            return $this->dialogHelper->ask($input, $this, new ConfirmationQuestion($text));
        }

        if ($this->dialogHelper instanceof DialogHelper) {
            return $this->dialogHelper->askConfirmation($this, $text);
        }

        throw new \RuntimeException("Invalid dialogHelper");
    }
}
