<?php

namespace Liip\RMT;

define('RMT_VERSION', '0.9.15');

use Liip\RMT\Command\ChangesCommand;
use Liip\RMT\Command\ReleaseCommand;
use Liip\RMT\Command\CurrentCommand;
use Liip\RMT\Command\InitCommand;
use Liip\RMT\Output\Output;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Yaml\Yaml;

class Application extends BaseApplication
{
    // This ugly hack is mandatory to allow command to access application at configure() time
    // See Liip\Command\BaseCommand::getApplication()
    static $instance;

    /**
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function __construct()
    {
        // Creation
        parent::__construct('Release Management Tool', RMT_VERSION);
        self::$instance = $this;

        // Change the current directory in favor of the project root folder,
        // this allow to run the task from outside the project like:
        //     $/home/www> myproject/RMT release
        chdir($this->getProjectRootDir());

        // Add all command, in a controlled way and render exception if any
        try {
            // Add the default command
            $this->add(new InitCommand());
            // Add command that require the config file
            if (file_exists($this->getConfigFilePath())){
                $this->add(new ReleaseCommand());
                $this->add(new CurrentCommand());
                $this->add(new ChangesCommand());
            }
        }
        catch (\Exception $e) {
            $output = new \Liip\RMT\Output\Output();
            $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
            $this->renderException($e, $output);
            exit(1);
        }
    }

    /**
     * @inheritdoc
     */
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        return parent::run($input, new \Liip\RMT\Output\Output());
    }

    public function getProjectRootDir()
    {
        if (defined('RMT_ROOT_DIR')){
            return RMT_ROOT_DIR;
        }
        else {
            return getcwd();
        }
    }

    public function getConfigFilePath()
    {
        $validConfigFileName = array('.rmt.yml', '.rmt.json', 'rmt.yml', 'rmt.json');
        foreach($validConfigFileName as $filename){
            if (file_exists($path = $this->getProjectRootDir().DIRECTORY_SEPARATOR.$filename)){
                return $path;
            }
        }
        return null;
    }

    public function getConfig()
    {
        $configFile = $this->getConfigFilePath();
        if (!is_file($configFile)){
            throw new \Exception("Impossible to locate the config file rmt.xxx at $configFile. If it's the first time you
                are using this tool, you setup your project using the [RMT init] command"
            );
        }

        if (pathinfo($configFile, PATHINFO_EXTENSION) == 'json') {
            $config = json_decode(file_get_contents($configFile), true);
            if (!is_array($config)){
                throw new \Exception("Impossible to parse your config file ($configFile), you probably have an error in the JSON syntax");
            }
        }
        else {
            try {
                $config = Yaml::parse(file_get_contents($configFile), true);
            }
            catch(\Exception $e) {
                throw new \Exception("Impossible to parse your config file ($configFile), you probably have an error in the YML syntax: ".$e->getMessage());
            }
        }

        return $config;
    }


    /**
     * @inheritdoc
     */
    public function asText($namespace = null, $raw = false)
    {
        $messages = array();

        // Title
        $title = 'RMT '.$this->getLongVersion();
        $messages[] = '';
        $messages[] = $title;
        $messages[] = str_pad('', 41, '-'); // strlen is not working here...
        $messages[] = '';

        // Usage
        $messages[] = '<comment>Usage:</comment>';
        $messages[] = '  RMT command [arguments] [options]';
        $messages[] = '';

        // Commands
        $messages[] = '<comment>Available commands:</comment>';
        $commands = $this->all();
        $width = 0;
        foreach ($commands as $command) {
            $width = strlen($command->getName()) > $width ? strlen($command->getName()) : $width;
        }
        $width += 2;
        foreach ($commands as $name => $command) {
            if (in_array($name, array('list', 'help'))){
                continue;
            }
            $messages[] = sprintf("  <info>%-${width}s</info> %s", $name, $command->getDescription());
        }
        $messages[] = '';

        // Options
        $messages[] = '<comment>Common options:</comment>';
        foreach ($this->getDefinition()->getOptions() as $option) {
            if (in_array($option->getName(), array('help', 'ansi', 'no-ansi', 'no-interaction', 'version'))){
                continue;
            }
            $messages[] = sprintf('  %-29s %s %s',
                '<info>--'.$option->getName().'</info>',
                $option->getShortcut() ? '<info>-'.$option->getShortcut().'</info>' : '  ',
                $option->getDescription()
            );
        }
        $messages[] = '';

        // Help
        $messages[] = '<comment>Help:</comment>';
        $messages[] = '   To get more information about a given command, you can use the help option:';
        $messages[] = sprintf('     %-26s %s %s','<info>--help</info>', '<info>-h</info>', 'Provide help for the given command');
        $messages[] = '';

        return implode(PHP_EOL, $messages);
    }

}
