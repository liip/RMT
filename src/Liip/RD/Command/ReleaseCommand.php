<?php
namespace Liip\RD\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Liip\RD\Changelog\ChangelogManager;
use Liip\RD\Information\InformationCollector;
use Liip\RD\Information\InteractiveQuestion;
use Liip\RD\Information\InformationRequest;
use Liip\RD\Context;

class ReleaseCommand extends BaseCommand {

    protected function configure()
    {
        $this->setName('release');
        $this->setDescription('Release a new version of the project');
        $this->setHelp('The <comment>release</comment> interactive task must be used to create a new version of a project');

        $this->loadContext();
        $this->loadInformationCollector();

        // Register the command option
        foreach (Context::getInstance()->getService('information-collector')->getCommandOptions() as $option) {
            $this->getDefinition()->addOption($option);
        }
    }

    protected function loadInformationCollector()
    {
        $ic = new InformationCollector();

        // Add a specific option if it's the first release
        try {
            Context::getInstance()->getService('version-persister')->getCurrentVersion();
        }
        catch (\Liip\RD\Exception\NoReleaseFoundException $e){
            $ic->registerRequest(
                new InformationRequest('confirm-first', array(
                    'description' => 'This is the first release for the current branch',
                    'type' => 'confirmation'
                ))
            );
        }

        // Register options of the release tasks
        $ic->registerRequests(Context::getInstance()->getService('version-generator')->getInformationRequests());
        $ic->registerRequests(Context::getInstance()->getService('version-persister')->getInformationRequests());

        // Register options of all lists (prerequistes and actions)
        foreach (array('prerequisites', 'pre-release-actions', 'post-release-actions') as $listName){
            foreach (Context::getInstance()->getList($listName) as $listItem){
                $ic->registerRequests($listItem->getInformationRequests());
            }
        }

        Context::getInstance()->setService('information-collector', $ic);
    }

    // Always executed
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        Context::getInstance()->setService('output', $this->output);
        Context::getInstance()->getService('information-collector')->handleCommandInput($input);

        $this->writeBigTitle('Welcome to Release Management Tool');

        $this->executeActionListIfExist('prerequisites');
    }



    // Executed only when we are in interactive mode
    protected function interact(InputInterface $input, OutputInterface $output)
    {

        // Fill up questions
        if (Context::getInstance()->getService('information-collector')->hasMissingInformation()){
            $this->writeSmallTitle('Information collect');
            $this->getOutput()->indent();
            foreach(Context::getInstance()->getService('information-collector')->getInteractiveQuestions() as $name => $question) {
                $answer = $this->askQuestion($question);
                Context::getInstance()->getService('information-collector')->setValueFor($name, $answer);
                $this->writeEmptyLine();
            }
            $this->getOutput()->unIndent();
        }
    }

    // Always executed, but first initialize and interact have already been called
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get the current version or generate a new one if the user has confirm that this is required
        try {
            $currentVersion = Context::getInstance()->getService('version-persister')->getCurrentVersion();
        }
        catch (\Liip\RD\Exception\NoReleaseFoundException $e){
            if (Context::getInstance()->getService('information-collector')->getValueFor('confirm-first')==false){
                throw $e;
            }
            $currentVersion = Context::getInstance()->getService('version-generator')->getInitialVersion();
        }
        Context::getInstance()->setParam('current-version', $currentVersion);

        // Generate and save the new version number
        $newVersion = Context::getInstance()->getService('version-generator')->generateNextVersion(
            Context::getInstance()->getParam('current-version')
        );
        Context::getInstance()->setParam('new-version', $newVersion);

        $this->executeActionListIfExist('pre-release-actions');

        $this->writeSmallTitle('Release process');
        $this->getOutput()->indent();

        // TODO Can we say than when it's vcs-tag persister we have to force commit first?
        $this->getOutput()->writeln("A new version named [<yellow>$newVersion</yellow>] is going to be released");
        Context::getInstance()->getService('version-persister')->save($newVersion);
        $this->getOutput()->writeln("Release: <green>Success</green>");

        $this->getOutput()->unIndent();

        $this->executeActionListIfExist('post-release-actions');

    }

    protected function executeActionListIfExist($name, $title=null){
        $actions = Context::getInstance()->getList($name);
        if (count($actions) > 0) {
            $this->writeSmallTitle($title ?: ucfirst($name));
            $this->getOutput()->indent();
            foreach ($actions as $num => $action){
                $this->write($num++.") ".$action->getTitle().' : ');
                $this->getOutput()->indent();
                $action->execute();
                $this->writeEmptyLine();
                $this->getOutput()->unIndent();
            }
            $this->getOutput()->unIndent();
        }
    }

}
