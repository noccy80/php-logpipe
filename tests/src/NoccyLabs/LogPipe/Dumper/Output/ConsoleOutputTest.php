<?php

namespace NoccyLabs\LogPipe\Dumper\Output;

use NoccyLabs\LogPipe\Message\MonologMessage;

use Prophecy\Argument;
use Prophecy\Prophet;

require_once __DIR__."/OutputTestAbstract.php";

class ConsoleOutputTest extends OutputTestAbstract
{
    /**
     * @dataProvider getMessages
     */
    public function testThatMessagesAreWrittenToTheOutput($message)
    {
        $prophet = new Prophet();

        $mockOutput = $prophet->prophesize('Symfony\Component\Console\Output\ConsoleOutput');

        $mockOutput->writeln(Argument::any())->shouldBeCalled();

        $dumper = new ConsoleOutput($mockOutput->reveal());
        $dumper->write($message);
    }
}
