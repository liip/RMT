<?php

namespace Liip\RD\Information;

use Symfony\Component\Console\Input\InputOption;

class InformationRequest
{
    static $validTypes = array('text', 'boolean', 'yes-no', 'enum');
    static $defaults = array(
        'description' => '',
        'type' => 'text',
        'choices' => array(),
        'choices_shortcuts' => array(),
        'command_argument' => true,
        'command_shortcut' => null,
        'interactive' => true,
        'default' => null,
        'interactive_help' => '',
        'interactive_help_shortcut' => 'h'
    );

    protected $name;
    protected $options;
    protected $value;

    public function __construct($name, $options = array())
    {
        $this->name = $name;

        // Check for invalid option
        $invalidOptions = array_diff(array_keys($options),array_keys(self::$defaults));
        if (count($invalidOptions) > 0){
            throw new \Exception('Invalid config option(s) ['.implode(', ',$invalidOptions).']');
        }

        // Merging with defaults
        $this->options = array_merge(self::$defaults, $options);

        // Type validation
        if (!in_array($this->options['type'], self::$validTypes)){
            throw new \Exception('Invalid option type ['.$this->options['type'].']');
        }
    }

    public function getName(){
        return $this->name;
    }

    public function isAvailableAsCommandOption()
    {
        return $this->options['command_argument'];
    }

    public function isAvailableForInteractive()
    {
        return $this->options['interactive'];
    }



    public function convertToCommandOption() {
        return new InputOption(
            $this->name,
            $this->options['command_shortcut'],
            $this->options['type']=='boolean' || $this->options['type']=='yes-no' ? InputOption::VALUE_NONE : InputOption::VALUE_REQUIRED,
            $this->options['description'],
            $this->options['default']
        );
    }

    public function convertToInteractiveQuestion() {
        return new \Liip\RD\UserQuestion\SimpleQuestion(
            'Please provide the '.strtolower($this->options['description']),
            $this->options['default']
        );
    }

    public function setValue($value)
    {
        if ($this->isValid($value)) {
            $this->value = $value;
        }
        else {
            throw new \Liip\RD\Config\Exception('Invalid value');
        }
    }

    public function isValid($value)
    {
        if ($this->options['type'] == 'boolean' && !is_bool($value)) {
            return false;
        }
        return true;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function hasValue()
    {
        return $this->value !== null;
    }


}
