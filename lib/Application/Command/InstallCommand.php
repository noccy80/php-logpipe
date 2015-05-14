<?php


namespace NoccyLabs\LogPipe\Application\Command;

use NoccyLabs\LogPipe\Handler\LogPipeHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class LogTestCommand
 * @package NoccyLabs\LogPipe\Application\Command
 */
class InstallCommand extends AbstractCommand
{

    /**
     * @var string
     */
    protected $cmdname;

    /**
     * @param string $cmdname
     */
    public function __construct($cmdname="install")
    {
        $this->cmdname = $cmdname;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName($this->cmdname);
        $this->setDescription("Show installation instructions");
        $this->setHelp(self::HELP_TEXT);
    }


    /**
     * {@inheritdoc}
     */
    protected function exec()
    {
        $this->output->write(self::HELP_TEXT);

    }

    const HELP_TEXT = <<<EOT
<options=bold>Installing in Symfony</options=bold>

    To use LogPipe with Symfony you only need to register the handler as a service so that it can be used with Monolog.
    This is preferably done in the `config_dev.yml` file. If there is a `services:` block, add the sections to it, otherwise
    create it:

        <comment>services:
            logpipe.handler:
                class:      NoccyLabs\LogPipe\Handler\LogPipeHandler
                arguments:  [ "tcp:127.0.0.1:6601" ]</comment>

    Then define the handler in the same file. By doing it in `config_dev.yml`, your live environment will not use the
    LogPipe logger.

        <comment>monolog:
            handlers:
                ...
                logpipe:
                    type:   service
                    id:     logpipe.handler</comment>

<options=bold>Installing elsewhere</options=bold>

    LogPipe can be set up to automatically log exceptions and errors:

        <comment>use NoccyLabs\LogPipe\Handler\ConsoleHandler;

        \$handler = new ConsoleHandler("tcp:127.0.0.1:6601:serializer=json");
        \$handler->setExceptionReporting(true);
        \$handler->setErrorReporting(true);</comment>

    You can also write events manually: **(not implemented)**

        <comment>\$handler->debug("This is a debug message!");
        \$handler->warning("Danger! Danger!");</comment>



EOT;

}
