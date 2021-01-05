<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Tests\Functional;

class ChangelogTest extends RMTFunctionalTestBase
{
    public function testSimple(): void
    {
        $this->createChangelog('simple');
        $this->createConfig('simple', 'changelog');
        $this->executeTest(null, 'comment1', '1');
        $this->executeTest(null, 'comment2', '2');
    }

    public function testSemantic(): void
    {
        $this->createChangelog('semantic');
        $this->createConfig('semantic', 'changelog');
        $this->executeTest('major', 'First major', '1.0.0');
        $this->executeTest('patch', 'First patch', '1.0.1');
        $this->executeTest('minor', 'First minor', '1.1.0');
        $this->executeTest('major', 'Second major', '2.0.0');
        $this->executeTest('minor', 'test_minor', '2.1.0');
    }

    /**
     * Execute changelog test
     *
     * @param String [major/minor/patch]
     * @param String comment
     * @param String expected version number (ie 2.0.0)
     */
    protected function executeTest($semanticType, $comment, $expectedVersion): void
    {
        //        $this->manualDebug();
        if (is_null($semanticType)) {
            exec('./RMT release -n --comment="' . $comment . '"');
        } else {
            exec('./RMT release -n --type='.$semanticType . ' --comment="' . $comment . '"');
        }
        $changelog = file_get_contents($this->tempDir . '/CHANGELOG');
        self::assertMatchesRegularExpression('/' . $expectedVersion . '/', $changelog);
        self::assertMatchesRegularExpression('/' . $comment . '/', $changelog);
    }
}
