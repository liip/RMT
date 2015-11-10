<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Command;

use Liip\RMT\Application;
use Liip\RMT\VCS\VCSInterface;
use Liip\RMT\Config\Handler;
use Liip\RMT\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Wrapper/helper around sf2 command
 */
abstract class BaseCommand extends Command
{
    protected $input;
    protected $output;

    /**
     * {@inheritdoc}
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        // Store the input and output for easier usage
        $this->input = $input;
        $this->output = $output;
        $dialogHelper = class_exists('Symfony\Component\Console\Helper\QuestionHelper')
            ? $this->getHelperSet()->get('question')
            : $this->getHelperSet()->get('dialog')
        ;
        $this->output->setDialogHelper($dialogHelper);
        $this->output->setFormatterHelper($this->getHelperSet()->get('formatter'));
        Context::getInstance()->setService('output', $this->output);

        parent::run($input, $output);
    }

    /**
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    public function loadContext()
    {
        $configHandler = new Handler($this->getApplication()->getConfig(), $this->getApplication()->getProjectRootDir());
        $config = $configHandler->getBaseConfig();

        // Select a branch specific config if a VCS is in use
        if (isset($config['vcs'])) {
            Context::getInstance()->setService('vcs', $config['vcs']['class'], $config['vcs']['options']);
            /** @var VCSInterface $vcs */
            $vcs = Context::get('vcs');
            try {
                $branch = $vcs->getCurrentBranch();
            } catch (\Exception $e) {
                echo "\033[31mImpossible to read the branch name\033[37m";
            }
            if (isset($branch)) {
                $config = $configHandler->getConfigForBranch($branch);
            }
        }

        // Store the config for latter usage
        Context::getInstance()->setParameter('config', $config);

        // Populate the context
        foreach (array('version-generator', 'version-persister') as $service) {
            Context::getInstance()->setService($service, $config[$service]['class'], $config[$service]['options']);
        }
        foreach (array('prerequisites', 'pre-release-actions', 'post-release-actions') as $listName) {
            Context::getInstance()->createEmptyList($listName);
            foreach ($config[$listName] as $service) {
                Context::getInstance()->addToList($listName, $service['class'], $service['options']);
            }
        }

        // Provide the root dir as a context parameter
        Context::getInstance()->setParameter('project-root', $this->getApplication()->getProjectRootDir());
    }

    /**
     * @return \Liip\RMT\Application
     */
    public function getApplication()
    {
        return Application::$instance;
    }
}
