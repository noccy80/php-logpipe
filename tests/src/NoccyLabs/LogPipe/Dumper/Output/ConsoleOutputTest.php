<?php

namespace NoccyLabs\LogPipe\Dumper\Output;

use NoccyLabs\LogPipe\Message\MonologMessage;

use Prophecy\Argument;

require_once __DIR__."/OutputTestAbstract.php";

class ConsoleOutputTest extends OutputTestAbstract
{
    /**
     * @dataProvider getMessages
     */
    public function testThatMessagesAreWrittenToTheOutput($message)
    {
        $mockOutput = $this->prophesize('Symfony\Component\Console\Output\ConsoleOutput');

        $mockOutput->writeln(Argument::any())->shouldBeCalled();

        $dumper = new ConsoleOutput($mockOutput->reveal());
        $dumper->write($message);
    }
}
