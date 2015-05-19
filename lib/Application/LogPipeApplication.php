<?php

namespace NoccyLabs\LogPipe\Application;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

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
        $output = new ConsoleOutput();
        $inst = new self(APP_NAME, APP_VERSION, $output);

        $inst->add(new Command\InstallCommand());
        $inst->add(new Command\DumpLogCommand());
        $inst->add(new Command\DumpLogCommand("dump"));
        $inst->add(new Command\DumpChannelsCommand());

        $inst->add(new Command\LogWriteCommand());
        $inst->add(new Command\LogWriteCommand("write"));
        $inst->add(new Command\LogTestCommand());
        $inst->add(new Command\LogTestCommand("test"));
        $inst->add(new Command\LogPassCommand());

        $inst->add(new Command\MetricsDumpCommand());
        $inst->add(new Command\MetricsShowCommand());


        $inst->run(null, $output);
    }

    public function __construct($app, $version, $output)
    {
        parent::__construct($app, $version);

        $formatter = $output->getFormatter();
        $styles = [
            "command" => new OutputFormatterStyle("cyan", null, []),
            "arguments" => new OutputFormatterStyle("cyan", null, ["bold"]),
            "value" => new OutputFormatterStyle("cyan", null, ["underscore"]),
            "example" => new OutputFormatterStyle("cyan", null, []),
            "header" => new OutputFormatterStyle(null, null, ["bold"]),
            "subheader" => new OutputFormatterStyle(null, null, [ "underscore" ]),
        ];
        foreach ($styles as $name=>$style) {
            $formatter->setStyle($name, $style);
        }



    }
}
