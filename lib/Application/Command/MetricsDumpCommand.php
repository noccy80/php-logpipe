<?php


namespace NoccyLabs\LogPipe\Application\Command;

use NoccyLabs\LogPipe\Handler\LogPipeHandler;
use Monolog\Logger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use NoccyLabs\LogPipe\Metrics\Processor;
use NoccyLabs\LogPipe\Metrics\ProcessorDefinitionLoader;
use Symfony\Component\Yaml\Yaml;
use NoccyLabs\LogPipe\Metrics\MetricsLog;

/**
 * Class LogTestCommand
 * @package NoccyLabs\LogPipe\Application\Command
 */
class MetricsDumpCommand extends AbstractCommand
{

    /**
     * @var string
     */
    protected $cmdname;

    /**
     * @param string $cmdname
     */
    public function __construct($cmdname="metrics:dump")
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
        $this->setDescription("Parse and process captured metrics");
        $this->addOption("file", "f", InputOption::VALUE_REQUIRED, "The filename containing captured data");
        $this->addOption("definition", "d", InputOption::VALUE_REQUIRED, "The parsing definition");
        $this->addOption("output", "o", InputOption::VALUE_REQUIRED, "The output filename");
        $this->setHelp(self::HELP_TEXT);
    }


    /**
     * {@inheritdoc}
     */
    protected function exec()
    {
        $definition_file = $this->input->getOption("definition");
        $metrics_file = $this->input->getOption("file");

        if (!$metrics_file) {
            $this->output->writeln("<error>Error: No metrics file specified (-f,--file)</error>");
            return 1;
        }

        $processor = new Processor();
    
        if ($definition_file) {
            $loader = new ProcessorDefinitionLoader();
            $yaml = file_get_contents($definition_file);
            $definitions = Yaml::parse($yaml);
            $loader->load($processor, $definitions);
        }
        
        $metrics_log = new MetricsLog($metrics_file, "r");
        $processor->setMetricsLog($metrics_log);

        $processor->process();
        
    }

    const HELP_TEXT = <<<EOT
This command parses captured metrics. First, capture letrics using the <comment>dump</comment> command:

    $ <comment>logpipe dump -m metrics.log</comment>

Then, create a simple manifest:

    $ <comment>cat > metrics.def</comment>
    <info>page.hit:
        aggregate: { group: route, totals: true }</info>
    <comment>^D</comment>

Finally, parse your metrics:

    $ <comment>logpipe metrics:dump -d metrics.def metrics.log</comment>

EOT;

}
