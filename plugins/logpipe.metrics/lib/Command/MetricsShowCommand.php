<?php


namespace NoccyLabs\LogPipe\Application\Command;

use NoccyLabs\LogPipe\Handler\LogPipeHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use NoccyLabs\LogPipe\Metrics\MetricsLog;

/**
 * Class LogTestCommand
 * @package NoccyLabs\LogPipe\Application\Command
 */
class MetricsShowCommand extends AbstractCommand
{

    /**
     * @var string
     */
    protected $cmdname;

    /**
     * @param string $cmdname
     */
    public function __construct($cmdname="metrics:show")
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
        $this->setDescription("Show raw data from a metrics dump");
        $this->addArgument("file", InputArgument::OPTIONAL, "The filename containing captured data");
        $this->addArgument("match", InputArgument::OPTIONAL, "Only display keys matching pattern");
        $this->setHelp(self::HELP_TEXT);
    }


    /**
     * {@inheritdoc}
     */
    protected function exec()
    {
        $file = $this->input->getArgument("file");
        if (!$file) {
            $this->output->write("<error>Error: No file specified.</error>\n");
            return 1;
        }

        $log = new MetricsLog($file, "r");
        $sess = null;
        $dumper = new \Symfony\Component\VarDumper\VarDumper();
        while (($item = $log->read())) {
            if ($item->session != $sess) {
                $sess = $item->session;
                $this->output->writeln("<options=bold>{$sess}</options=bold>");
            }
            $this->output->write("  <comment>{$item->key}</comment>: ");
            if (!is_array($item->data)) {
                    $this->output->write(json_encode($item->data)."\n");
            } else {
                foreach ($item->data as $k=>$v) {
                    $this->output->write("    <info>{$k}</info>: ".json_encode($v)."\n");
                }
            }
        }
    }

    const HELP_TEXT = <<<EOT

EOT;

}
