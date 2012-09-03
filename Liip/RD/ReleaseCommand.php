<?php
namespace Liip\RD;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Liip\RD\Changelog\ChangelogManager;
use Liip\RD\Config;


class ReleaseCommand extends Command {
    
    protected $output;
    
    protected function configure() {
        $this->setName('release');
        $this->setDescription('Release a new version of the project');
        $this->setHelp(<<<HELP
The <comment>release</comment> interactive task must be used to create a new version of a project, this task does:

 * Check the current git status (you must have a clean copy)
 * Increment the current version number
 * Update the changelog file
 * Update the version number in the app.yml file
 * Update the asset version id CSS or JS have been updated
 * Commit the change and create a new git tag
 * [Optionnal] Push the change to origin and jlc
 * [Optionnal] Direct deploy if the parameter --and-deploy-to is provide

HELP
);
        $this->addOption('and-deploy-to', null, InputOption::VALUE_REQUIRED, 'Directly lauch the deployment on the given server');
        $this->addOption('ignore-check', null, InputOption::VALUE_NONE, 'Do not process the check for clean working directory');
        $this->addOption('config', null, InputOption::VALUE_REQUIRED, 'Which config do you want to use? (as defined in rd.json)');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new Config();

        $env = $input->getOption('config');
        $config->setEnv($env);

        $this->output = $output;
        $changelog = new ChangelogManager(__DIR__.'/../../CHANGELOG');

        if ( ! $input->getOption('ignore-check') ){
            $output->writeln('<info>Check that your working copy is clean</info>');
            $this->checkGitStatus();
            $this->logSection("git", 'Check OK !');
        }

        // Display list of changes
        $version = $changelog->getCurrentVersion();
        $this->logInfo("Here is the list of change you are going to released:");
        $output->writeln(">>>");
        passthru("git log --oneline v$version..master --color=always");
        $output->writeln("<<<");
        
        // Ask the version type
        $major = $this->askConfirmation("Is those changes brings a new functionality (major version) ? (y/n)");

        // Ask the comment
        $comment = '';
        while (strlen($comment) < 1){
            $hint = $major ? 'add reference to the story ticket JLC-XX' : 'small hint why you have to do this minor version';
            $comment = $this->ask("Please provide a description ($hint):");
        }

        // Ask if the CSS of JS has been updated
        $updateAssets = $this->askConfirmation("Does this version include modification of the JS or CSS? (y/n)", 'QUESTION_LARGE');

        // Generate the new version number
        $newVersion = $changelog->getNextVersion($version, $major);
        $this->logInfo("Current version is $version, new version will be $newVersion");

        // Update local files
        $this->logSection('update', 'changelog file');
        $changelog->update($newVersion, $comment, $major);
        $this->logSection('update', 'app.yml file');
        $this->updateAppYml($newVersion, $updateAssets);

        // Save a new commit and associated tag
        $this->logInfo('Execute the git commands');
        $gitTagName = 'v'.$newVersion;
        $this->gitExec('add CHANGELOG');
        $this->gitExec('add config/app.yml');
        $this->gitExec('commit -m "New version '.$newVersion.': '.$comment.'"');
        $this->gitExec("tag $gitTagName");
        $messages = array("A new version $newVersion with corresponding git tag $gitTagName have be created");

        // Propose to push the new release to origin
        if ($this->askConfirmation("Do you want me to push it to origin? (y/n)")){
            $this->gitExec("push origin master");
            $this->gitExec("push origin $gitTagName");
            $messages[] = "Theses changes have been pushed to origin";
        }
        else {
            $messages[] = "You can push them when you are ready with:";
            $messages[] = "   > git push origin master && git push origin $gitTagName";
        }

        // Auto deploy if require
        if ( isset($options['and-deploy-to']) ){
            $output->logInfo($messages);
            $this->runTask('git:deploy', array($options['and-deploy-to']));
            return;
        }
        else {
            $messages[] = "";
            $messages[] = "You can now deploy this version with the command:";
            $messages[] = "   > symfony git:deploy [server] --ver=$newVersion";
        }

        // Display comfirmation messages
        $this->logInfo($messages);

    }


    protected function updateAppYml($version, $updateAssets){
        return;
        $file = sfConfig::get('sf_config_dir').'/app.yml';
        $config = file_get_contents($file);
        $config = preg_replace('#\s\sversion:\s"\d+\.\d+"#', '  version: "'.$version.'"', $config);
        if ($updateAssets){
            $config = preg_replace('#\s\s\s\sasset_version:\s"\d+\.\d+"#', '    asset_version: "'.$version.'"', $config);
        }
        file_put_contents($file, $config);
    }

    protected function checkGitStatus(){
        $this->gitExec('fetch origin');
        $statLines = $this->gitExec('status', true);
        if ($statLines[0] !== '# On branch master'){
            throw new \Exception('Sorry, but you must be on the master branch to generate a new version');
        }
        if (strpos($statLines[1], 'Your branch is behind') !== false ) {
            throw new \Exception("Your master branch is not up to date, please rebase your work\n (".$statLines[1].')');
        }
        if (strpos($statLines[1], 'Your branch is ahead') !== false ) {
            throw new \Exception("Please push your change before tagging \n (".$statLines[1].')');
        }
        if ($statLines[1] !== 'nothing to commit (working directory clean)') {
            $this->gitExec('status');
            throw new \Exception('Your working directory must be clean to generate a new version. Please commit or stash your change and push everything to origin');
        }
    }

    protected function gitExec($cmd, $returnResult=false){
        $cmd = 'git '.$cmd;
        $this->logSection('exec', $cmd);
        if ($returnResult){
            //exec($cmd, $result);
            //return $result;
        } else {
            //system($cmd);
        }
    }

    protected function logSection($sectionName, $message) {
        $message = is_array($message) ? implode("\n", $message) : $message;
        $msg = $this->getHelper('formatter')->formatSection($sectionName, $message);
        $this->output->writeln($msg);
    }

    protected function logInfo($message) {
        $message = is_array($message) ? implode("\n", $message) : $message;
        $msg = $this->getHelper('formatter')->formatBlock("\n".$message, 'info');
        $this->output->writeln($msg);
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
