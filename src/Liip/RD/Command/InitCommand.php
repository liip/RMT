<?php
namespace Liip\RD\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use Liip\RD\Information\InformationRequest;

class InitCommand extends BaseCommand
{
    protected $informationCollector;

    protected $executablePath;
    protected $commandPath;
    protected $configPath;


    protected function buildPaths()
    {
        $projectDir = $this->getApplication()->getProjectRootDir();
        $this->executablePath = $projectDir.'/RD';
        $this->commandPath = realpath(__DIR__.'/../../../../command.php');
        $this->configPath = $projectDir.'/rd.json';
    }

    protected function configure()
    {
        $this->setName('init');
        $this->setDescription('Setup a new project configuration in the current directory');
        $this->setHelp('The <comment>init</comment> interactive task can be used to setup a new project');

        // Add an option to force re-creation of the config file
        $this->getDefinition()->addOption(new InputOption('force', null, InputOption::VALUE_NONE, 'Force update of the config file'));

        // Create an information collector and configure the different information request
        $this->informationCollector = new \Liip\RD\Information\InformationCollector();
        $this->informationCollector->registerRequests(array(
            new InformationRequest('vcs', array(
                'description' => 'The VCS system to use',
                'type' => 'choice',
                'choices' => array('git', 'none'),
                'choices_shortcuts' => array('g'=>'git', 'n'=>'none'),
                'default' => 'g'
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
            throw new \Exception("A rd.json file already exist, if you want to regenerate it, use the --force option");
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
            "<?php define('RD_ROOT_DIR', __DIR__); ?>\n".
            "<?php require '{$this->commandPath}'; ?>\n"
        );
        exec('chmod +x RD');

        // Create the config file
        $this->getOutput()->writeln("Creation of the config file <info>{$this->configPath}</info>");
        file_put_contents($this->configPath, json_encode($this->getConfigData()));

        // Confirmation
        $this->writeBigTitle('Success, you can start using RD by calling <info>RD release</info>');
        $this->writeEmptyLine();
    }

    public function getConfigData() {
        $config = array();

        $vcs = $this->informationCollector->getValueFor('vcs');
        if ($vcs !== 'none'){
            $config['vcs'] = $vcs;
        }

        $generator = $this->informationCollector->getValueFor('vcs');
        $config['version-generator'] = $generator=='semantic-versioning' ? 'sementic' : 'simple';

        $config['version-persister'] = $this->informationCollector->getValueFor('persister');

        return $config;
    }
}
