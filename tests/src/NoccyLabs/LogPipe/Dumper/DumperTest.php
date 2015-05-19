<?php

namespace NoccyLabs\LogPipe\Dumper;

use NoccyLabs\LogPipe\Message\MonologMessage;

class DumperTest extends \PhpUnit_Framework_TestCase
{
    public function setup()
    {
    }

    public function teardown()
    {
    }

    public function testThatADefaultDumperWorksOutOfTheBox()
    {
        $dumper = new Dumper();
        $dumper->updateTransports();
        $dumper->dumpMessage(new MonologMessage([]));
        $this->assertTrue(true);
    }
}
