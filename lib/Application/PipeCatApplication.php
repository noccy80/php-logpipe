<?php

namespace NoccyLabs\LogPipe\Application;

use Symfony\Component\Console\Application;

class PipeCatApplication extends Application
{
    public static function main()
    {
        $inst = new self("pipecat", "0.1");
        //$inst->add(new Command\DumpCommand);
        $inst->add(new Command\ChannelsCommand);
        $inst->add(new Command\TestCommand);
        $inst->add(new Command\DumpLogCommand);
        $inst->add(new Command\DumpLogCommand("dump"));
        $inst->run();
    }
}
