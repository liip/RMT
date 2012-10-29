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
        $this->setHelp('The <comment>release</comment> interactive task must be used to create a new version of a project');

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

    // Always executed
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->context->setParam('current-version', $this->context->getService('version-persister')->getCurrentVersion());

        $this->context->setService('output', $this->output);
        $this->context->getService('information-collector')->handleCommandInput($input);

        $this->writeBigTitle('Welcome to Release Management Tool');

        $this->executeActionListIfExist('prerequisites');
    }



    // Executed only when we are in interactive mode
    protected function interact(InputInterface $input, OutputInterface $output){

        // Fill up questions
        if ($this->context->getService('information-collector')->hasMissingInformation()){
            $this->writeSmallTitle('Information collect');
            $this->getOutput()->indent();
            foreach($this->context->getService('information-collector')->getInteractiveQuestions() as $name => $question) {
                $answer = $this->askQuestion($question);
                $this->context->getService('information-collector')->setValueFor($name, $answer);
                $this->writeEmptyLine();
            }
            $this->getOutput()->unIndent();
        }
    }

    // Always executed, but first initialize and interact have already been called
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Generate and save the new version number
        $newVersion = $this->context->getService('version-generator')->generateNextVersion($this->context->getParam('current-version'));
        $this->context->setParam('new-version', $newVersion);

        $this->executeActionListIfExist('pre-release-actions');

        $this->writeSmallTitle('Release process');
        $this->getOutput()->indent();

        // TODO Can we say than when it's vcs-tag persister we have to force commit first?
        $this->getOutput()->writeln("A new version named [$newVersion] is going to be released");
        $this->context->getService('version-persister')->save($newVersion);
        $this->getOutput()->writeln("Release: <info>Success</info>");

        $this->getOutput()->unIndent();

        $this->executeActionListIfExist('post-release-actions');

    }

    protected function executeActionListIfExist($name, $title=null){
        $actions = $this->context->getList($name);
        if (count($actions) > 0) {
            $this->writeSmallTitle($title ?: ucfirst($name));
            $this->getOutput()->indent();
            foreach ($actions as $num => $action){
                $this->write($num++.") ".$action->getTitle().' : ');
                $this->getOutput()->indent();
                $action->execute($this->context);
                $this->writeEmptyLine();
                $this->getOutput()->unIndent();
            }
            $this->getOutput()->unIndent();
        }
    }

}
