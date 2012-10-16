<?php

namespace Liip\RD\VCS;

class Git extends BaseVCS
{
/*
    public function checkStatus(){
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

    protected function saveNewVersion(){
        // Save a new commit and associated tag
        $this->logInfo('Execute the git commands');
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
    }
*/
    protected $dryRun = false;


    public function getAllModificationsSince($tag)
    {
        return $this->executeGitCommand("log --oneline $tag..HEAD --color=always");
    }

    public function getLocalModifications(){
        return $this->executeGitCommand('status -s');
    }


    public function getTags()
    {
        return $this->executeGitCommand("tag");
    }

    public function createTag($tagName)
    {
        return $this->executeGitCommand("tag $tagName");
    }

    public function publishTag($tagName)
    {
        $this->executeGitCommand("push origin $tagName");
    }

    public function publishChanges()
    {
        $this->executeGitCommand("push origin ".$this->getCurrentBranch());
    }

    public function saveWorkingCopy($commitMsg='')
    {
        $this->executeGitCommand("add --all");
        $this->executeGitCommand("commit -m \"$commitMsg\"");
    }

    public function getCurrentBranch(){
        $branches = $this->executeGitCommand('branch');
        foreach ($branches as $branch){
            if (strpos($branch, '* ') == 0){
                return substr($branch,2);
            }
        }
        throw new \Exception("Not currently on any branch");
    }

    protected function executeGitCommand($cmd)
    {
        // Avoid using some commands in dry mode
        if ($this->dryRun){
            if ($cmd !== 'tag'){
                $cmdWords = explode(' ',$cmd);
                if (in_array($cmdWords[0], array('tag', 'push', 'add', 'commit'))){
                    return;
                }
            }
        }

        // Execute
        $cmd = 'git '.$cmd;
        exec($cmd, $result, $exitCode);
        if ($exitCode !== 0){
            throw new \Exception('Error while executing git command: '.$cmd);
        }
        return $result;
    }
}

