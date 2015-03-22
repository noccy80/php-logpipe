<?php

namespace NoccyLabs\LogPipe\Posix;

class FifoTest extends \PhpUnit_Framework_TestCase
{
    public function testSendingAndReceivingData()
    {
        $logger = new Fifo("/tmp/test.pipe");
        $dumper = new Fifo("/tmp/test.pipe");

        $dumper->create();
        $logger->open();

        $out = "Foobar";
        $logger->write($out);
        $data = $dumper->read(1024);

        $this->assertEquals($out, $data);

        $logger->close();
        $dumper->destroy();

        $this->assertFalse(file_exists("/tmp/test.pipe"));
    }
}
