<?php
namespace Liip\RD\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Liip\RD\Changelog\ChangelogManager;

class ReleaseCommand extends BaseCommand {

    protected function configure()
    {
        $this->setName('release');
        $this->setDescription('Release a new version of the project');
        $this->setHelp('The <comment>release</comment> interactive task must be used to create a new version of a project:');

        $this->loadContext();

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

        // Generate and save the new version number
        $newVersion = $this->context->getService('version-generator')->generateNextVersion($this->context->getParam('current-version'));
        $this->context->setParam('new-version', $newVersion);

        // Pre-release
        foreach ($this->context->getList('pre-release-actions') as $action){
            $this->context->getOutput()->writeln("Pre-action: ".$action->getTitle());
            $action->execute($this->context);
        }

        // TODO Can we say than when it's vcs-tag persister we have to force commit first?
        $this->context->getService('version-persister')->save($newVersion);

        // Post-release
        foreach ($this->context->getList('post-release-actions') as $action){
            $this->context->getService('output')->writeln("Pre-action: ".$action->getTitle());
            $action->execute($this->context);
        }

    }

}
