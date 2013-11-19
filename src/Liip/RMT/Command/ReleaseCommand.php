<?php
namespace Liip\RMT\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Liip\RMT\Changelog\ChangelogManager;
use Liip\RMT\Information\InformationCollector;
use Liip\RMT\Information\InteractiveQuestion;
use Liip\RMT\Information\InformationRequest;
use Liip\RMT\Context;

/**
 * Main command, used to release a new version
 */
class ReleaseCommand extends BaseCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName('release');
        $this->setDescription('Release a new version of the project');
        $this->setHelp('The <comment>release</comment> interactive task must be used to create a new version of a project');

        $this->loadContext();
        $this->loadInformationCollector();

        // Register the command option
        foreach (Context::get('information-collector')->getCommandOptions() as $option) {
            $this->getDefinition()->addOption($option);
        }
    }

    protected function loadInformationCollector()
    {
        $ic = new InformationCollector();

        // Add a specific option if it's the first release
        try {
            Context::get('version-persister')->getCurrentVersion();
        }
        catch (\Liip\RMT\Exception\NoReleaseFoundException $e){
            $ic->registerRequest(
                new InformationRequest('confirm-first', array(
                    'description' => 'This is the first release for the current branch',
                    'type' => 'confirmation'
                ))
            );
        }
        catch (\Exception $e) {
            echo "Error while trying to read the current version";
        }


            // Register options of the release tasks
        $ic->registerRequests(Context::get('version-generator')->getInformationRequests());
        $ic->registerRequests(Context::get('version-persister')->getInformationRequests());

        // Register options of all lists (prerequistes and actions)
        foreach (array('prerequisites', 'pre-release-actions', 'post-release-actions') as $listName){
            foreach (Context::getInstance()->getList($listName) as $listItem){
                $ic->registerRequests($listItem->getInformationRequests());
            }
        }

        Context::getInstance()->setService('information-collector', $ic);
    }

    /**
     * Always executed
     *
     * @inheritdoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        Context::get('information-collector')->handleCommandInput($input);

        $this->getOutput()->writeBigTitle('Welcome to Release Management Tool');

        $this->executeActionListIfExist('prerequisites');
    }

    /**
     * Executed only when we are in interactive mode
     *
     * @inheritdoc
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);

        // Fill up questions
        if (Context::get('information-collector')->hasMissingInformation()){
            $questions = Context::get('information-collector')->getInteractiveQuestions();
            $this->getOutput()->writeSmallTitle('Information collect ('.count($questions).' questions)');
            $this->getOutput()->indent();
            $count = 1;
            foreach($questions as $name => $question) {
                $answer = $this->getOutput()->askQuestion($question, $count++);
                Context::get('information-collector')->setValueFor($name, $answer);
                $this->getOutput()->writeEmptyLine();
            }
            $this->getOutput()->unIndent();
        }
    }

    /**
     * Always executed, but first initialize and interact have already been called
     *
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get the current version or generate a new one if the user has confirm that this is required
        try {
            $currentVersion = Context::get('version-persister')->getCurrentVersion();
        }
        catch (\Liip\RMT\Exception\NoReleaseFoundException $e){
            if (Context::get('information-collector')->getValueFor('confirm-first') == false){
                throw $e;
            }
            $currentVersion = Context::get('version-generator')->getInitialVersion();
        }
        Context::getInstance()->setParameter('current-version', $currentVersion);

        // Generate and save the new version number
        $newVersion = Context::get('version-generator')->generateNextVersion(
            Context::getParam('current-version')
        );
        Context::getInstance()->setParameter('new-version', $newVersion);

        $this->executeActionListIfExist('pre-release-actions');

        $this->getOutput()->writeSmallTitle('Release process');
        $this->getOutput()->indent();

        $this->getOutput()->writeln("A new version named [<yellow>$newVersion</yellow>] is going to be released");
        Context::get('version-persister')->save($newVersion);
        $this->getOutput()->writeln("Release: <green>Success</green>");

        $this->getOutput()->unIndent();

        $this->executeActionListIfExist('post-release-actions');
    }

    protected function executeActionListIfExist($name, $title=null)
    {
        $actions = Context::getInstance()->getList($name);
        if (count($actions) > 0) {
            $this->getOutput()->writeSmallTitle($title ?: ucfirst($name));
            $this->getOutput()->indent();
            foreach ($actions as $num => $action){
                $this->getOutput()->write(++$num.") ".$action->getTitle().' : ');
                $this->getOutput()->indent();
                $action->execute();
                $this->getOutput()->writeEmptyLine();
                $this->getOutput()->unIndent();
            }
            $this->getOutput()->unIndent();
        }
    }
}

