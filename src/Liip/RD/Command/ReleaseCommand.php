<?php
namespace Liip\RD\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Liip\RD\Changelog\ChangelogManager;
use Liip\RD\Config\Handler;
use Liip\RD\Context;


class ReleaseCommand extends Command {
    
    public static $projectRoot;  // Needed for testing
    protected $context;

    protected function configure()
    {
        $this->setName('release');
        $this->setDescription('Release a new version of the project');
        $this->setHelp('The <comment>release</comment> interactive task must be used to create a new version of a project:');

        $configFile = realpath($this->getProjectRootDir().'/rd.json');
        if (!is_file($configFile)){
            throw new \Exception("Impossible to locate the config file rd.json");
        }

        $env = null;
//        $envGuesser = new \Liip\RD\EnvironmentGuesser\GitBranchGuesser();
//        $env = $envGuesser->getCurrentEnvironment();
        $configHandler = new Handler();
        $this->context = $configHandler->createContext(json_decode(file_get_contents($configFile), true), $env);


        $this->context->setParam('project-root', $this->getProjectRootDir());

        //$this->preActions = $this->getPreActions();
        $this->context->setParam('current-version', $this->context->getService('version-persister')->getCurrentVersion());

        // we need to instantiate the version generator so that it registers its user questions
        $this->context->getService('version-generator');


        // Register the option of the pre-action
        foreach ($this->context->getList('prerequisites') as $pr){
            foreach($pr->getOptions() as $option) {
                $this->getDefinition()->addOption($option);
            }
        }

        foreach ($this->context->getUserQuestions() as $name => $question)
        {
            $this->addOption($name, null, InputOption::VALUE_REQUIRED, $question->getQuestionText(), $question->getDefaultValue());
        }

    }


    protected function interact(InputInterface $input, OutputInterface $output){

        $formatter = $this->getHelperSet()->get('formatter');
        $output->writeln(array(
            '',
            $formatter->formatBlock('Welcome to Release Management Tool', 'bg=blue;fg=white', true),
            ''
        ));
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->context->setService('input', $input);
        $this->context->setService('output', $output);

        // Prerequistes
        foreach ($this->context->getList('prerequisites') as $pr){
            $pr->execute($this->context);
        }

        // Fill up questions
        $questions = $this->context->getUserQuestions();
        foreach($questions as $topic => $question) {
            // Provided by options
            if ($input->getOption($topic) !== null){
                $question->setAnswer($input->getOption($topic));
            }
            // Or direct answers
            else {
                $dialog = $this->getHelperSet()->get('dialog');
                $answer = $dialog->ask($this->context->getService('output'), $question->getQuestionText(), $question->getDefaultValue());
                $question->setAnswer($answer);
            }
        }

        $version = $this->context->getService('version-generator')->generateNextVersion($this->context->getParam('current-version'));

        // Pre-release
        foreach ($this->context->getList('pre-release-actions') as $action){
            $this->context->getOutput()->writeln("Pre-action: ".$action->getTitle());
            $action->execute($this->context);
        }

        $this->context->getService('version-persister')->save($version);

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

    protected function ask($question, $yesOrNo=false) {
        $question = $this->getHelperSet()->get('formatter')->formatBlock($question, 'question', true);
        $question = $question."\n";
        $dialog = $this->getHelperSet()->get('dialog');
        if ($yesOrNo){
            return $dialog->askConfirmation($this->output, $question);
        }
        return $dialog->ask($this->output, $question);

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
