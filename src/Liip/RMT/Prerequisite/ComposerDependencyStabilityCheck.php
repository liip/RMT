<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2014, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Prerequisite;

use Liip\RMT\Action\BaseAction;
use Liip\RMT\Context;
use Liip\RMT\Information\InformationRequest;

/**
 * Test if only allowed dependencies use unstable versions.
 */
class ComposerDependencyStabilityCheck extends BaseAction
{

    const SKIP_OPTION = 'skip-composer-dependency-stability-check';
    const DEPENDENCY_LISTS = array('require', 'require-dev');

    private $whitelist;
    private $dependencyListWhitelists;

    public function __construct($options)
    {
        $this->options = array_merge(
            array(
                'ignore-require-dev' => false,
                'ignore-require' => false,
                'whitelist' => array(),
            ),
            $options
        );

        $this->whitelist = array();

        foreach ($options['whitelist'] as $listing) {
            if (!isset($listing[1])) {
                $this->whitelist[] = $listing[0];
            } else {
                $elementSize = sizeof($listing);
                for ($index = 1 ; $index < $elementSize ; ++$index) {
                    $element = $listing[$index];
                    if (!isset($this->dependencyListWhitelists[$element])) {
                        $this->dependencyListWhitelists[$element] = array();
                    }
                    $this->dependencyListWhitelists[$element][] = $listing[0];
                }
            }
        }
    }

    public function execute()
    {
        if (Context::get('information-collector')->getValueFor(self::SKIP_OPTION)) {
            Context::get('output')->writeln('<error>composer dependency-stability check skipped</error>');
            return;
        }

        if (!file_exists('composer.json')) {
            Context::get('output')->writeln('<error>composer.json does not exist, skipping check</error>');
            return;
        }

        if (!is_readable('composer.json')) {
            throw new \Exception(
                'composer.json can not be read (permissions?), (you can force a release with option --'
                . self::SKIP_OPTION.')'
            );
        }

        $contents = json_decode(file_get_contents('composer.json'), true);

        foreach (self::DEPENDENCY_LISTS as $dependencyList) {
            if (!$this->isListIgnored($dependencyList) && $this->listExists($contents, $dependencyList)) {
                $specificWhitelist = $this->generateListSpecificWhitelist($dependencyList);
                $this->checkDependencies($contents[$dependencyList], $specificWhitelist);
            }
        }

        $this->confirmSuccess();
    }

    /**
     * @param $dependencyList
     * @return mixed
     */
    private function isListIgnored($dependencyList)
    {
        return isset($this->options['ignore-' . $dependencyList]) && $this->options['ignore-' . $dependencyList] === true;
    }

    /**
     * @param $contents
     * @param $dependencyList
     * @return bool
     */
    private function listExists($contents, $dependencyList)
    {
        return isset($contents[$dependencyList]);
    }

    /**
     * @param $dependencyList
     * @return array
     */
    private function generateListSpecificWhitelist($dependencyList)
    {
        if (isset($this->dependencyListWhitelists[$dependencyList])) {
            return array_merge($this->whitelist, $this->dependencyListWhitelists[$dependencyList]);
        } else {
            return $this->whitelist;
        }
    }

    /**
     * check every element inside this array for composer version strings and throw an exception if the dependency is
     * not stable
     *
     * @param $dependencyList array
     * @param $whitelist array
     * @throws \Exception
     */
    private function checkDependencies($dependencyList, $whitelist = array()) {
        foreach ($dependencyList as $dependency => $version) {
            if (($this->startsWith($version, 'dev-') || $this->endsWith($version, '@dev'))
                && !in_array($dependency, $whitelist)) {
                throw new \Exception(
                    $dependency
                    . ' uses dev-version but is not listed on whitelist '
                    . ' (you can force a release with option --'.self::SKIP_OPTION.')'
                );
            }
        }
    }

    /**
     * @param $haystack string
     * @param $needle string
     * @return bool
     */
    private function startsWith($haystack, $needle)
    {
        return $haystack[0] === $needle[0]
            ? strncmp($haystack, $needle, strlen($needle)) === 0
            : false;
    }

    /**
     * @param $haystack string
     * @param $needle string
     * @return bool
     */
    private function endsWith($haystack, $needle) {
        return $needle === '' || substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }

    public function getInformationRequests()
    {
        return array(
            new InformationRequest(
                self::SKIP_OPTION,
                array(
                    'description' => 'Do not check composer.json for minimum-stability before the release',
                    'type' => 'confirmation',
                    'interactive' => false,
                )
            ),
        );
    }
}
