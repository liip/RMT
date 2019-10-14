<?php


namespace Liip\RMT\Tests\Functional;

use Liip\RMT\Information\InformationRequest;
use Liip\RMT\Information\InteractiveQuestion;
use Liip\RMT\Output\Output;
use Symfony\Component\Console\Input\StreamableInputInterface;

class OutputTest extends RMTFunctionalTestBase
{

    public function confirmationProvider()
    {
        return [
            ["y", true],
            ["n", false],
            ["z", false],
        ];
    }

    /**
     * @dataProvider confirmationProvider
     * @throws \Exception
     */
    public function testAskQuestionsConfirmation($input, $result)
    {
        $question = new InteractiveQuestion(new InformationRequest('foo', array('type' => 'confirmation')));
        $output = new Output();
        $response = $output->askQuestion($question, 2, $this->generateAnswer($input));
        $this->assertSame($result, $response);
    }

    public function choicesShortcutProvider()
    {
        return [[[
                "a" => "apple",
                "b" => "banana",
                "c" => "cherry",
                0 => "cerise",
        ]]];
    }

    public function testAskQuestionYesNo(){
        $question = new InteractiveQuestion(new InformationRequest('foo', array('type' => 'yes-no')));
        $output = new Output();
        foreach(["yes","no"] as $yesno) {
            $response = $output->askQuestion($question, 2, $this->generateAnswer($yesno));
            $this->assertSame($yesno, $response);
        }
    }

    /**
     * @dataProvider choicesShortcutProvider
     * @param array $shortcuts
     * @throws \Exception
     */
    public function testAskQuestionsChoice($shortcuts)
    {
        $question = new InteractiveQuestion(new InformationRequest('fruit', array(
            'type' => 'choice',
            'choices' => array_values($shortcuts),
            'choices_shortcuts' => $shortcuts,
        )));
        $output = new Output();
        foreach ($shortcuts as $short => $value) {
            $response = $output->askQuestion($question, 2, $this->generateAnswer($short));
            $this->assertEquals($value, $response);
        }
    }

    /**
     * @throws \Exception
     */
    public function testAskQuestionsText()
    {
        $ir = new InformationRequest('foo', array('type' => 'text'));
        $question = new InteractiveQuestion($ir);
        $output = new Output();
        $response = $output->askQuestion($question, 2, $this->generateAnswer("42 is always a good answer"));
        $this->assertSame("42 is always a good answer", $response);
    }

    /**
     * @param string $input
     * @return \PHPUnit_Framework_MockObject_MockObject|StreamableInputInterface
     */
    protected function generateAnswer($input)
    {
        return $this->createStreamableInputInterfaceMock($this->getInputStream($input));
    }

    /**
     * @param null $stream
     * @param bool $interactive
     * @return \PHPUnit_Framework_MockObject_MockObject|StreamableInputInterface
     */
    protected function createStreamableInputInterfaceMock($stream = null, $interactive = true)
    {
        $mock = $this->getMockBuilder(StreamableInputInterface::class)->getMock();
        $mock->expects($this->any())
            ->method('isInteractive')
            ->will($this->returnValue($interactive));

        if ($stream) {
            $mock->expects($this->any())
                ->method('getStream')
                ->willReturn($stream);
        }

        return $mock;
    }

    /**
     * @param $input
     * @return bool|resource
     */
    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fwrite($stream, $input);
        rewind($stream);
        return $stream;
    }
}
