<?php

namespace Liip\RMT\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Liip\RMT\Config\Handler;
use Liip\RMT\Context;
use Liip\RMT\Information\InteractiveQuestion;

/**
 * Wrapper/helper around sf2 command
 */
abstract class BaseCommand extends Command
{
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
        $configHandler = new Handler($this->getApplication()->getConfig(), $this->getApplication()->getProjectRootDir() );
        $config = $configHandler->getBaseConfig();

        // Select a branch specific config if a VCS is in use
        if (isset($config['vcs'])) {
            Context::getInstance()->setService('vcs', $config['vcs']['class'], $config['vcs']['options']);
            $vcs = Context::get('vcs');
            $branch = $vcs->getCurrentBranch();
            $config = $configHandler->getConfigForBranch($branch);
        }

        // Store the config for latter usage
        Context::getInstance()->setParameter('config', $config);

        // Populate the context
        foreach (array("version-generator", "version-persister") as $service){
            Context::getInstance()->setService($service, $config[$service]['class'], $config[$service]['options']);
        }
        foreach (array("prerequisites", "pre-release-actions", "post-release-actions") as $listName){
            Context::getInstance()->createEmptyList($listName);
            foreach ($config[$listName] as $service){
                Context::getInstance()->addToList($listName, $service['class'], $service['options']);
            }
        }

        // Provide the root dir as a context parameter
        Context::getInstance()->setParameter('project-root', $this->getApplication()->getProjectRootDir());
    }

    protected function writeBigTitle($title)
    {
        $this->writeEmptyLine();
        $formatter = $this->getHelperSet()->get('formatter');
        $this->getOutput()->writeln($formatter->formatBlock($title, 'bg=blue;fg=white', true));
    }

    protected function writeSmallTitle($title)
    {
        $this->writeEmptyLine();
        $formatter = $this->getHelperSet()->get('formatter');
        $this->getOutput()->writeln($formatter->formatBlock($title, 'bg=blue;fg=white'));
        $this->writeEmptyLine();
    }

    protected function writeEmptyLine($repeat=1)
    {
        $this->getOutput()->writeln(array_fill(0,$repeat,''));
    }


    protected function write($text)
    {
        $this->getOutput()->write($text);
    }

    protected function askQuestion(InteractiveQuestion $question)
    {
        $dialog = $this->getHelperSet()->get('dialog');
        return $dialog->askAndValidate(
            $this->getOutput(),
            $question->getFormatedText(),
            $question->getValidator(),
            false,
            $question->getDefault()
        );
    }

    /**
     * @return \Liip\RMT\Application
     */
    public function getApplication()
    {
        return \Liip\RMT\Application::$instance;
    }
}

