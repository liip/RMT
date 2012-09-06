<?php

namespace Liip\RD\Version\Generator;

use Liip\RD\UserQuestion\SimpleQuestion;

/**
 * Generator based on the Semantic Versioning defined by Tom Preston-Werner
 * Description available here: http://semver.org/
 */
class SemanticGenerator implements GeneratorInterface
{
    protected $context;

    public function __construct($context, $options = array())
    {
        $this->context = $context;
        $this->registerUserQuestions();
    }

    /**
     * {@inheritDoc}
     * @throws \InvalidArgumentException
     */
    public function generateNextVersion($currentVersion, $options = array())
    {
        $typeQuestion = $this->context->getUserQuestionByTopic('versionType');
        $type = $typeQuestion->getAnswer();

        // Type validation
        $validTypes = array('patch', 'minor', 'major', '');
        if (!in_array($type, $validTypes)){
            throw new \InvalidArgumentException(
                'The option "type" must one of ['.implode($validTypes, ', ').'], "'.$type.'" given'
            );
        }

        if (!preg_match('#^\d+\.\d+\.\d+$#', $currentVersion) ){
            throw new \Exception('Current version format is invalid (' . $currentVersion . '). It should be major.minor.patch');
        }

        // Increment
        list($major, $minor, $patch) = explode('.', $currentVersion);
        if ($type === 'major') {
            $major += 1;
            $patch = $minor = 0;
        }
        else if ($type === 'minor') {
            $minor += 1;
            $patch = 0;
        }
        else {
            $patch += 1;
        }

        return implode(array($major, $minor, $patch), '.');
    }

    public function registerUserQuestions()
    {
        $question = new SimpleQuestion('Is it a major, minor or patch version?');
        $this->context->addUserQuestion('versionType', $question);
    }
}

