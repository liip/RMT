<?php
namespace Liip\RD;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Liip\RD\Changelog\ChangelogManager;
use Liip\RD\Config;
use Liip\RD\Context;


class ReleaseCommand extends Command {
    
    protected $context;

    protected function configure()
    {
        $this->setName('release');
        $this->setDescription('Release a new version of the project');
        $this->setHelp('The <comment>release</comment> interactive task must be used to create a new version of a project:');
        $this->addOption('config', null, InputOption::VALUE_REQUIRED, 'Which config do you want to use? (as defined in rd.json)', 'default');

        // Register the option of the pre-action
//        foreach ($this->preActions as $action){
//            foreach($action->getOptions() as $option) {
//                $this->getDefinition()->addOption($option);
//            }
//        }
    }




    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->context = new Context($input, $output);

        // Prerequistes

        // Ask questions
        $questions = $this->context->getUserQuestions();
        $dialog = $this->getHelperSet()->get('dialog');
        foreach($questions as $topic => $question) {
            $answer = $dialog->ask($this->context->getOutput(), $question->getQuestionText(), $question->getDefaultValue());
            $question->setAnswer($answer);
        }

        $version = $this->context->getVersionGenerator()->generateNextVersion($this->context->getCurrentVersion());

        // Pre-release
        foreach ($this->context->getPreActions() as $action){
            $this->context->getOutput()->writeln("Pre-action: ".$action->getTitle());
            $action->execute($this->context);
        }

        $this->context->getVersionPersister()->save($version);

        /*
        // Generate the new version number
        $newVersion = $changelog->getNextVersion($version, $major);
        $this->logInfo("Current version is $version, new version will be $newVersion");

        // Update local files
        $this->logSection('update', 'changelog file');
        $changelog->update($newVersion, $comment, $major);
         */

        // Display comfirmation messages
        $this->logInfo('working!');

    }








    protected function logSection($sectionName, $message) {
    $message = is_array($message) ? implode("\n", $message) : $message;
    $msg = $this->getHelper('formatter')->formatSection($sectionName, $message);
    $this->output->writeln($msg);
}

    protected function logInfo($message) {
        $message = is_array($message) ? implode("\n", $message) : $message;
        $msg = $this->getHelper('formatter')->formatBlock("\n".$message, 'info');
        $this->context->getOutput()->writeln($msg);
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
}
