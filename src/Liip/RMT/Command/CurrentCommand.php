<?php
namespace Liip\RMT\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Liip\RMT\Changelog\ChangelogManager;
use Liip\RMT\Config\Handler;
use Liip\RMT\Context;

/**
 * Outputs current version
 */
class CurrentCommand extends BaseCommand {

    protected function configure()
    {
        $this->setName('current');
        $this->setDescription('Display information about the current release');
        $this->setHelp('The <comment>current</comment> task can be used to display information on the current release');
        $this->addOption('raw', null, InputOption::VALUE_NONE, 'display only the version name');
        $this->addOption('vcs-tag', null, InputOption::VALUE_NONE, 'display the associated vcs-tag');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadContext();
        $version = Context::get('version-persister')->getCurrentVersion();
        if ($input->getOption('vcs-tag')) {
            $vcsTag = Context::get('version-persister')->getCurrentVersionTag();
        }
        if ($input->getOption('raw')==true) {
            $output->writeln($input->getOption('vcs-tag') ? $vcsTag : $version);
        }
        else {
            $msg = "Current release is: <green>$version</green>";
            if ($input->getOption('vcs-tag')) {
                $msg .= " (VCS tag: <green>$vcsTag</green>)";
            }
            $output->writeln($msg);
        }
    }
}

