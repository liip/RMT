<?php
/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Version\Generator;

use Liip\RMT\Context;
use vierbergenlars\SemVer\version;

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
        } else {
            $type = Context::get('information-collector')->getValueFor('type');
        }

        if (isset($options['label'])) {
            $label = $options['label'];
        } else {
            $label = Context::get('information-collector')->getValueFor('label');
        }

        // Type validation
        $validTypes = array('patch', 'minor', 'major');
        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException(
                "The option [type] must be one of: {".implode($validTypes, ', ')."}, \"$type\" given"
            );
        }

        if (!preg_match('#^'.$this->getValidationRegex().'$#', $currentVersion)) {
            throw new \Exception('Current version format is invalid (' . $currentVersion . '). It should be major.minor.patch');
        }

        $matches = null;
        $returnValue = preg_match('$(?:(\d+\.\d+\.\d+)(?:(-)([a-zA-Z]+)(\d+)?)?)$', $currentVersion, $matches);
        // if last version is with label
        if (count($matches) > 3) {
            list($major, $minor, $patch) = explode('.', $currentVersion);
            $patch = substr($patch, 0, strpos($patch, "-"));

            if ($label != 'none') {
                // increment label
                if (array_key_exists(3, $matches)) {
                    $oldLabel = $matches[3];
                    $labelVersion = 2;

                    // if label is new clear version
                    if ($label !== $oldLabel) {
                        $labelVersion = false;
                    } else {
                        // if version exists increment it
                        if (array_key_exists(4, $matches)) {
                            $labelVersion = intval($matches[4])+1;
                        }
                    }
                }

                return implode(array($major, $minor, $patch), '.').'-'.$label.$labelVersion;
            }

            return implode(array($major, $minor, $patch), '.');
        }

        list($major, $minor, $patch) = explode('.', $currentVersion);
        // Increment
        switch ($type) {
            case 'major':
                $major += 1;
                $patch = $minor = 0;
                break;
            case 'minor':
                $minor += 1;
                $patch = 0;
                break;
            default:
                $patch += 1;
                break;
        }

        // new label
        if ($label != 'none') {
            return implode(array($major, $minor, $patch), '.').'-'.$label;
        }

        return implode(array($major, $minor, $patch), '.');
    }

    public function getInformationRequests()
    {
        return array('type', 'label');
    }

    public function getValidationRegex()
    {
        return '(?:(\d+\.\d+\.\d+)(?:(-)([a-zA-Z]+)(\d+)?)?)';
    }

    public function getInitialVersion()
    {
        return '0.0.0';
    }

    public function compareTwoVersions($a, $b)
    {
        return version::compare($a, $b);
    }
}
