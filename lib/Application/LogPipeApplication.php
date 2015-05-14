<?php

namespace NoccyLabs\LogPipe\Application;

use Symfony\Component\Console\Application;

/**
 * Class LogPipeApplication
 * @package NoccyLabs\LogPipe\Application
 */
class LogPipeApplication extends Application
{
    /**
     * Main console application entrypoint
     *
     * @throws \Exception
     */
    public static function main()
    {
        $inst = new self(APP_NAME, APP_VERSION);

        $inst->add(new Command\InstallCommand());
        $inst->add(new Command\DumpLogCommand());
        $inst->add(new Command\DumpLogCommand("dump"));
        $inst->add(new Command\DumpChannelsCommand());

        $inst->add(new Command\LogWriteCommand());
        $inst->add(new Command\LogWriteCommand("write"));
        $inst->add(new Command\LogTestCommand());
        $inst->add(new Command\LogPassCommand());

        $inst->add(new Command\MetricsDumpCommand());
        $inst->add(new Command\MetricsShowCommand());

        $inst->run();
    }
}
