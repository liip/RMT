<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Information;

use Symfony\Component\Console\Input\InputOption;

/**
 * Define a user information request
 */
class InformationRequest
{
    protected static $validTypes = array('text', 'yes-no', 'choice', 'confirmation');
    protected static $defaults = array(
        'description' => '',
        'type' => 'text',
        'choices' => array(),
        'choices_shortcuts' => array(),
        'command_argument' => true,
        'command_shortcut' => null,
        'interactive' => true,
        'default' => null,
        'interactive_help' => '',
        'interactive_help_shortcut' => 'h',
        'hidden_answer' => false,
    );

    protected $name;
    protected $options;
    protected $value;
    protected $hasValue = false;

    public function __construct($name, $options = array())
    {
        $this->name = $name;

        // Check for invalid option
        $invalidOptions = array_diff(array_keys($options), array_keys(self::$defaults));
        if (count($invalidOptions) > 0) {
            throw new \Exception('Invalid config option(s) ['.implode(', ', $invalidOptions).']');
        }

        // Set a default false for confirmation
        if (isset($options['type']) && $options['type'] == 'confirmation') {
            $options['default'] = false;
        }

        // Merging with defaults
        $this->options = array_merge(self::$defaults, $options);

        // Type validation
        if (!in_array($this->options['type'], self::$validTypes)) {
            throw new \Exception('Invalid option type ['.$this->options['type'].']');
        }
    }

    public function getName()
    {
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

    public function convertToCommandOption()
    {
        $mode = $this->options['type'] == 'boolean' || $this->options['type'] == 'confirmation' ?
            InputOption::VALUE_NONE :
            InputOption::VALUE_REQUIRED
        ;

        return new InputOption(
            $this->name,
            $this->options['command_shortcut'],
            $mode,
            $this->options['description'],
            (!$this->isAvailableForInteractive() && $this->getOption('type') !== 'confirmation') ? $this->options['default'] : null
        );
    }

    public function convertToInteractiveQuestion()
    {
        $questionOptions = array();
        foreach (array('choices', 'choices_shortcuts', 'interactive_help', 'interactive_help_shortcut') as $optionName) {
            $questionOptions[$optionName] = $this->options[$optionName];
        }

        return new \Liip\RMT\Information\InteractiveQuestion($this);
    }

    public function setValue($value)
    {
        try {
            $value = $this->validate($value);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Validation error for ['.$this->getName().']: '.$e->getMessage());
        }
        $this->value = $value;
        $this->hasValue = true;
    }

    private function validateValue($parameters, $callback, $message)
    {
        if (!is_array($parameters)) {
            $parameters = array($parameters);
        }

        if (!call_user_func_array($callback, $parameters)) {
            throw new \InvalidArgumentException($message);
        }
    }

    public function validate($value)
    {
        switch ($this->options['type']) {
            case 'boolean':
                $this->validateValue($value, 'is_bool', 'Must be a boolean');
                break;
            case 'choice':
                $this->validateValue(array($value, $this->options['choices']), function ($v, $choices) {
                    return in_array($v, $choices);
                }, 'Must be one of '.json_encode($this->options['choices']));
                break;
            case 'text':
                $this->validateValue($value, function ($v) {
                    return is_string($v) && strlen($v) > 0;
                }, 'Test must be provided');
                break;
            case 'yes-no':
                $value = lcfirst($value[0]);
                $this->validateValue($value, function ($v) {
                    return $v === 'y' || $v === 'n';
                }, "Must be 'y' or 'n'");
                break;
        }

        return $value;
    }

    public function getValue()
    {
        if (!$this->hasValue() && $this->options['default'] === null) {
            throw new \Liip\RMT\Exception("No value [{$this->name}] available");
        }

        return $this->hasValue() ? $this->value : $this->options['default'];
    }

    public function hasValue()
    {
        return $this->hasValue;
    }
}
