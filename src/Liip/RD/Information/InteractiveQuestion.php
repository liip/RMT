<?php

namespace Liip\RD\Information;

class InteractiveQuestion
{
    protected $informationRequest;

    public function __construct(InformationRequest $ir)
    {
        $this->informationRequest = $ir;
    }

    public function getFormatedText()
    {
        $text = '<info>Please provide '.strtolower($this->informationRequest->getOption('description'))."</info>";
        if ($this->informationRequest->getOption('type') == 'choice') {
            $text .= "\n". $this->formatChoices(
                $this->informationRequest->getOption('choices'),
                $this->informationRequest->getOption('choices_shortcuts')
            );
        }

        // print the default if exist
        if ($this->informationRequest->getOption('default') !== null){
            $text .= ' (default: <info>'.$this->informationRequest->getOption('default').'</info>)';
        }

        return $text.": ";
    }

    public function formatChoices($choices, $shortcuts){
        if (count($shortcuts) > 0){
            $shortcuts = array_flip($shortcuts);
            foreach ($shortcuts as $choice => $shortcut){
                $shortcuts[$choice] = '<info>'.$shortcut.'</info>';
            }
            foreach ($choices as $pos => $choice){
                $choices[$pos] = $choice.'['.$shortcuts[$choice].']';
            }
        }
        $text = "Select one of: ".implode(', ', $choices);
        return $text;
    }

    public function getDefault()
    {
        return $this->informationRequest->getOption('default');
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

