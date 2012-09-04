<?php

namespace Liip\RD\Version\Generator;

/**
 * Generator based on the Semantic Versioning defined by Tom Preston-Werner
 * Description available here: http://semver.org/
 */
class SemanticGenerator implements GeneratorInterface
{

    /**
     * {@inheritDoc}
     * @throws \InvalidArgumentException
     */
    public function getNextVersion($currentVersion, $options = array())
    {
        // Type validation
        $validTypes = array('patch', 'minor', 'major');
        if (isset($options['type']) && !in_array($options['type'], $validTypes)){
            throw new \InvalidArgumentException(
                'The option "type" must one of ['.implode($validTypes, ', ').'], "'.$options['type'].'" given'
            );
        }

        // Increment
        list($full, $major, $minor) = explode('.', $currentVersion);
        if (!isset($options['type']) || $options['type'] == 'patch') {
            $minor += 1;
        }
        else if ($options['type'] == 'minor') {
            $major += 1;
            $minor = 0;
        }
        else {
            $full += 1;
            $major = $minor = 0;
        }

        return implode(array($full, $major, $minor), '.');
    }
}
