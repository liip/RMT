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
        $configHandler = new Handler($this->getApplication()->getConfig(), $this->getApplication()->getProjectRootDir() );
        $config = $configHandler->getBaseConfig();
        $context = new Context();

        // Select a branch specific config if a VCS is in use
        if (isset($config['vcs'])) {
            $context->setService('vcs', $config['vcs']['class'], $config['vcs']['options']);
            $vcs = $context->getService('vcs');
            $branch = $vcs->getCurrentBranch();
            $config = $configHandler->getConfigForBranch($branch);
        }

        // Store the config for latter usage
        $context->setParam('config', $config);

        // Populate the context
        foreach (array("version-generator", "version-persister") as $service){
            $context->setService($service, $config[$service]['class'], $config[$service]['options']);
        }
        foreach (array("prerequisites", "pre-release-actions", "post-release-actions") as $listName){
            $context->createEmptyList($listName);
            foreach ($config[$listName] as $service){
                $context->addToList($listName, $service['class'], $service['options']);
            }
        }

        // Provide the root dir as a context parameter
        $context->setParam('project-root', $this->getApplication()->getProjectRootDir());

        $this->context = $context;
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

    protected function logSection($sectionName, $message) {
        $message = is_array($message) ? implode("\n", $message) : $message;
        $msg = $this->getHelper('formatter')->formatSection($sectionName, $message);
        $this->output->writeln($msg);
    }

    protected function logInfo($message) {
        $message = is_array($message) ? implode("\n", $message) : $message;
        $msg = $this->getHelper('formatter')->formatBlock("\n".$message, 'info');
        $this->getOutput()->writeln($msg);
    }

    protected function askQuestion(InteractiveQuestion $question) {
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
     * @return \Liip\RD\Application
     */
    public function getApplication()
    {
        return \Liip\RD\Application::$instance;
    }

}
