<?php

namespace Liip\RMT\Information;

/**
 * Represents the question asked to the user (formatter for InformationRequest)
 */
class InteractiveQuestion
{
    protected $informationRequest;

    public function __construct(InformationRequest $ir)
    {
        $this->informationRequest = $ir;
    }

    public function getFormatedText()
    {
        if ($this->informationRequest->getOption('type') == 'confirmation') {
            $text = 'Please confirm that ';
        }
        else {
            $text = 'Please provide ';
        }

        $text .= strtolower($this->informationRequest->getOption('description'));

        if ($this->informationRequest->getOption('type') == 'choice') {
            $text .= "\n". $this->formatChoices(
                $this->informationRequest->getOption('choices'),
                $this->informationRequest->getOption('choices_shortcuts')
            );
        }

        // print the default if exist
        if ($this->hasDefault()) {
            $defaultVal = $this->getDefault();
            if ( is_bool($defaultVal) ) {
                $defaultVal = $defaultVal===true ? 'true' : 'false';
            }
            $text .= ' (default: <info>'.$defaultVal.'</info>)';
        }

        return $text . ": ";
    }

    public function formatChoices($choices, $shortcuts)
    {
        if (count($shortcuts) > 0){
            $shortcuts = array_flip($shortcuts);
            foreach ($shortcuts as $choice => $shortcut){
                $shortcuts[$choice] = '<info>'.$shortcut.'</info>';
            }
            foreach ($choices as $pos => $choice){
                $choices[$pos] = '['.$shortcuts[$choice].'] '. $choice ;
            }
        }
        $text = '    '.implode(PHP_EOL.'    ', $choices);
        return $text."\nYour choice";
    }

    public function hasDefault()
    {
        return $this->informationRequest->getOption('default') !== null;
    }

    public function getDefault()
    {
        $default = $this->informationRequest->getOption('default');
        if (count($shortcuts = $this->informationRequest->getOption('choices_shortcuts')) > 0) {
            foreach ($shortcuts as $shortcut => $value) {
                if ($default == $value){
                    return $shortcut;
                }
            }
        }
        return $default;
    }

    public function getValidator()
    {
        return array($this, 'validate');
    }

    public function validate($value)
    {
        // Replace potential shortcuts
        if (count($shortcuts = $this->informationRequest->getOption('choices_shortcuts')) > 0) {
            if (in_array($value, array_keys($shortcuts))){
                $value = $shortcuts[$value];
            }
            else {
                throw new \Exception("Please select a value in ".json_encode(array_keys($shortcuts)));
            }
        }

        // Validation
        return $this->informationRequest->validate($value);
    }
}

