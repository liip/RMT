<?php

namespace Liip\RD\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Liip\RD\Config\Handler;
use Liip\RD\Context;
use Liip\RD\Information\InteractiveQuestion;

abstract class BaseCommand extends Command
{
    public static $projectRoot;  // Needed for testing
    protected $context;
    protected $input;
    protected $output;

    public function run(InputInterface $input, OutputInterface $output)
    {
        // Store the input and output for easier usage
        $this->input = $input;
        $this->output = $output;
        parent::run($input, $output);
    }

    /**
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }


    public function loadContext()
    {
        $configFile = $this->getProjectRootDir().'/rd.json';
        if (!is_file($configFile)){
            throw new \Exception("Impossible to locate the config file rd.json at $configFile. If it's the first time you
                are using this tool, you setup your project using the [RD init] command"
            );
        }

        // TODO How to use envGuesser as vcs can be env specific??
        $env = null;
        // $envGuesser = new \Liip\RD\EnvironmentGuesser\GitBranchGuesser();
        // $env = $envGuesser->getCurrentEnvironment();

        $configHandler = new Handler();
        $this->context = $configHandler->createContext(json_decode(file_get_contents($configFile), true), $env);
        $this->context->setParam('project-root', $this->getProjectRootDir());
        $this->context->setParam('current-version', $this->context->getService('version-persister')->getCurrentVersion());

        // we need to instantiate the version generator so that it registers its user questions
        // TODO, remove this
        $this->context->getService('version-generator');

    }

    protected function writeBigTitle($title)
    {
        $this->writeEmptyLine();
        $formatter = $this->getHelperSet()->get('formatter');
        $this->context->getService('output')->writeln($formatter->formatBlock($title, 'bg=blue;fg=white', true));
    }

    protected function writeSmallTitle($title)
    {
        $this->writeEmptyLine();
        $formatter = $this->getHelperSet()->get('formatter');
        $this->context->getService('output')->writeln($formatter->formatBlock($title, 'bg=blue;fg=white'));
        $this->writeEmptyLine();
    }

    protected function writeEmptyLine($repeat=1)
    {
        $this->context->getService('output')->writeln(array_fill(0,$repeat,''));
    }


    protected function write($text)
    {
        $this->context->getService('output')->write($text);
    }

    protected function logSection($sectionName, $message) {
        $message = is_array($message) ? implode("\n", $message) : $message;
        $msg = $this->getHelper('formatter')->formatSection($sectionName, $message);
        $this->output->writeln($msg);
    }

    protected function logInfo($message) {
        $message = is_array($message) ? implode("\n", $message) : $message;
        $msg = $this->getHelper('formatter')->formatBlock("\n".$message, 'info');
        $this->context->getService('output')->writeln($msg);
    }

    protected function askQuestion(InteractiveQuestion $question) {
        $dialog = $this->getHelperSet()->get('dialog');
        return $dialog->askAndValidate(
            $this->context->getService('output'),
            $question->getFormatedText(),
            $question->getValidator(),
            false,
            $question->getDefault()
        );
    }

    protected function askConfirmation($question) {
        return $this->ask($question, true);
    }

    public function getProjectRootDir()
    {
        if (self::$projectRoot !== null){
            return self::$projectRoot;
        }
        else if (defined('RD_CONFIG_DIR')){
            return RD_CONFIG_DIR;
        }
        else {
            return realpath(__DIR__.'/../../../../..');
        }
    }

}
