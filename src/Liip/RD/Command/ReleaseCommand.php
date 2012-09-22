<?php
namespace Liip\RD\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Liip\RD\Changelog\ChangelogManager;
use Liip\RD\Information\InformationCollector;

class ReleaseCommand extends BaseCommand {

    protected function configure()
    {
        $this->setName('release');
        $this->setDescription('Release a new version of the project');
        $this->setHelp('The <comment>release</comment> interactive task must be used to create a new version of a project:');

        $this->loadContext();
        $this->loadInformationCollector();

        // Register the command option
        foreach ($this->context->getService('information-collector')->getCommandOptions() as $option) {
            $this->getDefinition()->addOption($option);
        }
    }

    protected function loadInformationCollector()
    {
        $ic = new InformationCollector();

        // Register options of the release tasks
        $ic->registerRequests($this->context->getService('version-generator')->getInformationRequests());
        $ic->registerRequests($this->context->getService('version-persister')->getInformationRequests());

        // Register options of all lists (prerequistes and actions)
        foreach (array('prerequisites', 'pre-release-actions', 'post-release-actions') as $listName){
            foreach ($this->context->getList($listName) as $listItem){
                $ic->registerRequests($listItem->getInformationRequests());
            }
        }

        $this->context->setService('information-collector', $ic);
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
        $this->context->setService('output', $output);

        $this->context->getService('information-collector')->handleCommandInput($input);


        // Prerequistes
        foreach ($this->context->getList('prerequisites') as $pr){
            $pr->execute($this->context);
        }

        // Fill up questions
        foreach($this->context->getService('information-collector')->getInteractiveQuestions() as $name => $question) {
            $dialog = $this->getHelperSet()->get('dialog');
            $answer = $dialog->ask($this->context->getService('output'), $question->getQuestionText(), $question->getDefaultValue());
            $this->context->getService('information-collector')->setValueFor($name, $answer);
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
