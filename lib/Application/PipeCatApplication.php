<?php

namespace NoccyLabs\LogPipe\Application;

use Symfony\Component\Console\Application;

class PipeCatApplication extends Application
{
    public static function main()
    {
        $inst = new self("pipecat", "0.1");
        $inst->add(new Command\DumpLogCommand());
        $inst->add(new Command\DumpLogCommand("dump"));
        $inst->add(new Command\LogWriteCommand());
        $inst->add(new Command\LogWriteCommand("write"));
        $inst->add(new Command\LogTestCommand());
        $inst->add(new Command\ChannelsCommand());
        $inst->run();
    }
}
