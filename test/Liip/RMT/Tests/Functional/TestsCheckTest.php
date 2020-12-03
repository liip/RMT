<?php

namespace Liip\RMT\Tests\Functional;

use Liip\RMT\Context;
use Liip\RMT\Prerequisite\TestsCheck;
use Liip\RMT\Information\InformationCollector;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class TestsCheckTest extends TestCase
{
    protected function setUp(): void
    {
        $informationCollector = $this->createMock(InformationCollector::class);
        $informationCollector->method('getValueFor')->with(TestsCheck::SKIP_OPTION)->willReturn(false);

        $output = $this->createMock(OutputInterface::class);
        $output->method('write');

        $context = Context::getInstance();
        $context->setService('information-collector', $informationCollector);
        $context->setService('output', $output);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function succeeds_when_command_finished_within_the_default_configured_timeout_of_60s(): void
    {
        $check = new TestsCheck(array('command' => 'echo OK'));
        $check->execute();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function succeeds_when_command_finished_within_configured_timeout(): void
    {
        $check = new TestsCheck(array('command' => 'echo OK', 'timeout' => 0.100));
        $check->execute();
    }

    /**
     * @test
     */
    public function fails_when_the_command_exceeds_the_timeout(): void
    {
        $this->expectException(Throwable::class);
        $this->expectExceptionMessageMatches('~process.*time.*out~');

        $check = new TestsCheck(array('command' => 'sleep 1', 'timeout' => 0.100));
        $check->execute();
    }
}
