<?php

namespace Liip\RMT\Tests\Functional;


class ExternalTaskTest extends RMTFunctionalTestBase
{
    public function testInvalidScript()
    {
        $scriptName = 'invalid-script-name.php';
        $this->createJsonConfig("simple", "git", array("pre-release-actions" => array($scriptName)));
        exec('./RMT release -n', $output);
        $output = implode("\n", $output);
//        $this->manualDebug();
        $this->assertContains('Impossible to open', $output);
        $this->assertContains($scriptName, $output);
    }

    public function testExternalTouch()
    {
        $this->initGit();
        file_put_contents('touch-file1.php', '<?php touch("file1");');
        $this->createJsonConfig("simple", "git", array(
            "pre-release-actions" => array("touch-file1.php")
        ));
        exec('./RMT release -n');
        exec('ls', $files);
        $this->assertTrue(in_array('file1', $files), 'file1 in present in ['.implode(', ', $files).']');
    }
}
