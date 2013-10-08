<?php

namespace Liip\RMT\Version\Generator;

use Liip\RMT\Context;

/**
 * Generator based on the Semantic Versioning defined by Tom Preston-Werner
 * Description available here: http://semver.org/
 */
class SemanticGenerator implements GeneratorInterface
{
    public function __construct($options = array())
    {
    }

    /**
     * {@inheritDoc}
     * @throws \InvalidArgumentException
     */
    public function generateNextVersion($currentVersion, $options = array())
    {
        if (isset($options['type'])) {
            $type = $options['type'];
        }
        else {
            $type = Context::get('information-collector')->getValueFor('type');
        }

        // Type validation
        $validTypes = array('patch', 'minor', 'major');
        if (!in_array($type, $validTypes)){
            throw new \InvalidArgumentException(
                "The option [type] must be one of: {".implode($validTypes, ', ')."}, \"$type\" given"
            );
        }

        if (!preg_match('#^'.$this->getValidationRegex().'$#', $currentVersion) ){
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

    public function getInformationRequests()
    {
        return array('type');
    }

    public function getValidationRegex()
    {
        return '\d+\.\d+\.\d+';
    }

    public function getInitialVersion()
    {
        return '0.0.0';
    }

    public function compareTwoVersions($a, $b)
    {
        $a = explode('.', $a);
        $b = explode('.', $b);
        $length = count($a);
        for($i = 0; $i < $length; ++$i) {
            if ($a[$i] !== $b[$i]) {
                return $a[$i] < $b[$i] ? -1 : 1;
            }
        }
        return 0;
    }
}

