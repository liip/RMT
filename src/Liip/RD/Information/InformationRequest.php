<?php

namespace Liip\RD\Information;

use Symfony\Component\Console\Input\InputOption;

class InformationRequest
{
    static $validTypes = array('text', 'boolean', 'yes-no', 'choice');
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

    public function getOption($name)
    {
        return $this->options[$name];
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
            $this->options['type']!=='boolean' ? $this->options['default'] : null
        );
    }

    public function convertToInteractiveQuestion() {
        $questionOptions = array();
        foreach (array('choices', 'choices_shortcuts', 'interactive_help', 'interactive_help_shortcut') as $optionName){
            $questionOptions[$optionName] = $this->options[$optionName];
        }
        return new \Liip\RD\Information\InteractiveQuestion($this);
    }

    public function setValue($value)
    {
        $value = $this->validate($value);
        $this->value = $value;
    }

    public function validate($value)
    {
        if ($this->options['type'] == 'boolean' && !is_bool($value)) {
            throw new \Exception('Value of type bool, must be a boolean');
        }
        if ($this->options['type'] == 'choice' && !in_array($value, $this->options['choices'])) {
            throw new \Exception('Invalid choice, must be on of '.json_encode($this->options['choices']));
        }
        if ($this->options['type'] == 'text' && strlen($value) < 1) {
            throw new \Exception('Please provide a value');
        }
        return $value;
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
