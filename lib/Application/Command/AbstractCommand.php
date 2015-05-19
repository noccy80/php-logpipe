<?php

namespace NoccyLabs\LogPipe\Application\Command;

use NoccyLabs\LogPipe\Common\StringBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class provides an abstract base for the console commands.
 *
 * @package NoccyLabs\LogPipe\Application\Command
 */
abstract class AbstractCommand extends Command
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    public function __construct()
    {
        parent::__construct();
        $bt = debug_backtrace(0, 1);
        $file = dirname($bt[0]['file'])."/".basename($bt[0]['file'], ".php").".man";
        if (file_exists($file)) {
            $this->loadHelp($file);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->exec();
    }

    /**
     * @return mixed
     */
    abstract protected function exec();

    protected function loadHelp($filename)
    {
        $helpstr = new StringBuilder([ "wrap"=>100, "markup"=>true ]);

        $blocks = explode("\n\n",file_get_contents($filename));
        $vars = [
            "DEFAULT_ENDPOINT" => DEFAULT_ENDPOINT
        ];

        foreach ($blocks as $block) {
            $lines = explode("\n",$block);
            $indents = [];
            foreach ($lines as $line) {
                $indents[] = strlen($line) - strlen(ltrim($line));
            }
            $indents = array_unique($indents);
            $indent = reset($indents);
            if ($indent < 6) {
                $blockString = join(" ", array_map("trim", $lines)) . "\n\n";
            } else {
                $blockString = join("\n", array_map("trim", $lines))."\n\n";
            }
            if (trim($blockString)) {
                $helpstr->append($blockString, $vars, ["indent" => $indent]);
            }
        }

        $this->setHelp((string)$helpstr);
    }
}
