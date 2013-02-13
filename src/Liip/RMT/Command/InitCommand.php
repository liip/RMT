<?php
namespace Liip\RMT\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use Liip\RMT\Information\InformationRequest;
use Liip\RMT\Helpers\JSONHelper;

/**
 * Create json settings file and rmt executable
 */
class InitCommand extends BaseCommand
{
    protected $informationCollector;
    protected $executablePath;
    protected $commandPath;
    protected $configPath;

    protected function buildPaths()
    {
        $projectDir = $this->getApplication()->getProjectRootDir();
        $this->executablePath = $projectDir.'/RMT';
        $this->configPath = $projectDir.'/rmt.json';
        $this->commandPath = realpath(__DIR__.'/../../../../command.php');

        // If possible try to generate a relative link for the command if RMT is installed inside the project
        if (strpos($this->commandPath, $projectDir) === 0) {
            $this->commandPath = substr($this->commandPath, strlen($projectDir)+1);
        }
    }

    protected function configure()
    {
        $this->setName('init');
        $this->setDescription('Setup a new project configuration in the current directory');
        $this->setHelp('The <comment>init</comment> interactive task can be used to setup a new project');

        // Add an option to force re-creation of the config file
        $this->getDefinition()->addOption(new InputOption('force', null, InputOption::VALUE_NONE, 'Force update of the config file'));

        // Create an information collector and configure the different information request
        $this->informationCollector = new \Liip\RMT\Information\InformationCollector();
        $this->informationCollector->registerRequests(array(
            new InformationRequest('vcs', array(
                'description' => 'The VCS system to use',
                'type' => 'choice',
                'choices' => array('git', 'hg', 'none'),
                'choices_shortcuts' => array('g'=>'git', 'h'=>'hg', 'n'=>'none'),
                'default' => 'none'
            )),
            new InformationRequest('generator', array(
                'description' => 'The generator to use for version incrementing',
                'type' => 'choice',
                'choices' => array('semantic-versioning', 'basic-increment'),
                'choices_shortcuts' => array('s'=>'semantic-versioning', 'b'=>'basic-increment'),
            )),
            new InformationRequest('persister', array(
                'description' => 'The strategy to use to persist the current version value',
                'type' => 'choice',
                'choices' => array('vcs-tag', 'changelog'),
                'choices_shortcuts' => array('t'=>'vcs-tag', 'c'=>'changelog'),
                'command_argument' => true,
                'interactive' => true
            ))
        ));
        foreach ($this->informationCollector->getCommandOptions() as $option) {
            $this->getDefinition()->addOption($option);
        }
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->informationCollector->handleCommandInput($input);
        $this->writeBigTitle('Welcome to Release Management Tool Initialization');
        $this->writeEmptyLine();

        // Guessing elements path
        $this->buildPaths();

        // Security check
        if (file_exists($this->configPath) && $input->getOption('force')!==true) {
            throw new \Exception("A rmt.json file already exist, if you want to regenerate it, use the --force option");
        }
    }

    protected function interact(InputInterface $input, OutputInterface $output){

        // Fill up questions
        if ($this->informationCollector->hasMissingInformation()){
            foreach($this->informationCollector->getInteractiveQuestions() as $name => $question) {
                $answer = $this->askQuestion($question);
                $this->informationCollector->setValueFor($name, $answer);
                $this->writeEmptyLine();
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Create the executable task inside the project home
        $this->getOutput()->writeln("Creation of the new executable <info>{$this->executablePath}</info>");
        file_put_contents($this->executablePath,
            "#!/usr/bin/env php\n".
            "<?php define('RMT_ROOT_DIR', __DIR__); ?>\n".
            "<?php require '{$this->commandPath}'; ?>\n"
        );
        exec('chmod +x RMT');

        // Create the config file
        $this->getOutput()->writeln("Creation of the config file <info>{$this->configPath}</info>");
        file_put_contents(
            $this->configPath,
            JSONHelper::format(json_encode($this->getConfigData()))
        );

        // Confirmation
        $this->writeBigTitle('Success, you can start using RMT by calling <info>RMT release</info>');
        $this->writeEmptyLine();
    }

    public function getConfigData() {
        $config = array();

        $vcs = $this->informationCollector->getValueFor('vcs');
        if ($vcs !== 'none'){
            $config['vcs'] = $vcs;
        }

        $generator = $this->informationCollector->getValueFor('generator');
        $config['version-generator'] = $generator == 'semantic-versioning' ? 'semantic' : 'simple';

        $config['version-persister'] = $this->informationCollector->getValueFor('persister');

        return $config;
    }
}

