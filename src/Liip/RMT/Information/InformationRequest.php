<?php

namespace Liip\RMT\Information;

use Symfony\Component\Console\Input\InputOption;

/**
 * Define a user information request
 */
class InformationRequest
{
    static $validTypes = array('text', 'yes-no', 'choice', 'confirmation');
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
    protected $hasValue = false;

    public function __construct($name, $options = array())
    {
        $this->name = $name;

        // Check for invalid option
        $invalidOptions = array_diff(array_keys($options),array_keys(self::$defaults));
        if (count($invalidOptions) > 0){
            throw new \Exception('Invalid config option(s) ['.implode(', ',$invalidOptions).']');
        }

        // Set a default false for confirmation
        if (isset($options['type']) && $options['type'] == 'confirmation'){
            $options['default'] = false;
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
            $this->options['type']=='boolean' || $this->options['type']=='confirmation' ? InputOption::VALUE_NONE : InputOption::VALUE_REQUIRED,
            $this->options['description'],
            $this->options['type']!=='confirmation' ? $this->options['default'] : null
        );
    }

    public function convertToInteractiveQuestion() {
        $questionOptions = array();
        foreach (array('choices', 'choices_shortcuts', 'interactive_help', 'interactive_help_shortcut') as $optionName){
            $questionOptions[$optionName] = $this->options[$optionName];
        }
        return new \Liip\RMT\Information\InteractiveQuestion($this);
    }

    public function setValue($value)
    {
        try {
            $value = $this->validate($value);
        }
        catch (\Exception $e){
            throw new \InvalidArgumentException("Validation error for [".$this->getName()."]: ".$e->getMessage());
        }
        $this->value = $value;
        $this->hasValue = true;
    }

    public function validate($value)
    {
        if ($this->options['type'] == 'boolean' && !is_bool($value)) {
            throw new \InvalidArgumentException('Must be a boolean');
        }
        if ($this->options['type'] == 'choice' && !in_array($value, $this->options['choices'])) {
            throw new \InvalidArgumentException('Must be on of '.json_encode($this->options['choices']));
        }
        if ($this->options['type'] == 'text') {
            if (!is_string($value) || strlen($value) < 1) {
                throw new \InvalidArgumentException('Text must be provided');
            }
        }
        if ($this->options['type'] == 'yes-no') {
            if ($value === 'yes'){
                $value = 'y';
            }
            if ($value === 'no'){
                $value = 'n';
            }
            if ($value !== 'y' && $value !== 'n' ){
                throw new \InvalidArgumentException('Value should be [y] or [n]');
            }
        }
        return $value;
    }

    public function getValue()
    {
        if ( !$this->hasValue() && $this->options['default'] === null ){
            throw new \Liip\RMT\Exception("No value available");
        }

        return $this->hasValue() ? $this->value : $this->options['default'];
    }

    public function hasValue()
    {
        return $this->hasValue;
    }
}

