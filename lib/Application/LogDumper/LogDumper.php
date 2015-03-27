<?php

namespace NoccyLabs\LogPipe\Application\LogDumper;

use NoccyLabs\LogPipe\Posix\SignalListener;
use NoccyLabs\LogPipe\Transport\TransportInterface;
use NoccyLabs\LogPipe\Filter\FilterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use NoccyLabs\LogPipe\Application\Console\CharacterInput;

class LogDumper
{

    protected $transport;

    protected $dumper;

    protected $filter;

    protected $output;

    protected $buffer;

    protected $options = [
        "buffer.size"   => 1000,
        "output.wrap"   => 1,
        "output.title"  => 0
    ];

    protected $interactive = false;

    public function __construct($options)
    {
        $this->options = array_merge($this->options, $options);
        $this->buffer = new AddressableFifoBuffer($this->getOption("buffer.size"));
    }

    public function setTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    public function setDumper($dumper)
    {
        $this->dumper = $dumper;
    }

    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function setInteractive($state)
    {
        $this->interactive = (bool)$state;
    }

    public function run()
    {
        $signal = new SignalListener(SIGINT);

        if ($this->interactive) {
            $input  = new CharacterInput(true);
        } else {
            $input = NULL;
        }

        declare(ticks=5);

        $squelched = 0;

        if ($this->getOption("output.title")) { $this->updateTitle(); }

        while (!$signal()) {

            $msg = $this->transport->receive();
            if ($msg) {
                $this->buffer->push($msg);
                if (($out = $this->filter->filterMessage($msg))) {
                    if (($squelched > 0) && ($squelch_info)) {
                        $this->output->writeln("\r<fg=black;bg=yellow> {$squelched}</fg=black;bg=yellow><fg=black;bg=yellow;options=bold> messages squelched </fg=black;bg=yellow;options=bold>");
                        $squelched = 0;
                    }
                    $this->output->write("\r\e[J");
                    $this->dumper->dump($out);
                } else {
                    $squelched++;
                    if ($squelch_info) {
                        $this->output->write("\r<fg=black;bg=yellow> {$squelched} </fg=black;bg=yellow>");
                    }
                }
                if ($this->getOption("output.title")) { $this->updateTitle(); }
            }
            if ($input) {
                $ch = $input->readChar();
                switch ($ch) {
                    case 'q':
                        break(2);
                    case ':':
                        $line = $input->readLine(":");
                        $this->evalDumperCommand($line);
                        break;
                    case '/':
                        $find = $input->readLine("/");
                        if ($find) {
                            if (strpos($find,"/") === false) {
                                $find = "/{$find}/";
                            } elseif ($find[0] != "/") {
                                $find = "/{$find}";
                            }
                            $this->output->writeln("<info>preg_match(</info><comment>{$find}</comment></info>)</info>:");
                            $items = $this->buffer->match($find);
                            foreach ($items as $item) { $this->dumper->dump($item); }
                        }
                        break;
                    case chr(27):
                        break(2);
                }
            }
            usleep(100);
        }
    }

    protected function updateTitle()
    {
        $received = $this->buffer->getTotal();
        echo "\e]0;LogPipe [{$received}]\x07";
    }

    protected function evalDumperCommand($command)
    {
        $args = (array)str_getcsv($command," ");
        $cmd = array_shift($args);
        switch ($cmd) {
            case 'help':
                break;
            case 'set':
                if (count($args) > 1) {
                    // set k=v
                    $this->setOption($args[0], $args[1]);
                } elseif (count($args) > 0) {
                    $this->dumpOption($args[0]);
                } else {
                    $this->dumpOption();
                }
                break;
        }
    }

    public function setOption($key, $value)
    {
        switch ($key) {
            case 'buffer.size':
                $this->buffer = new AddressableFifoBuffer($value);
                break;
        }
        $this->options[$key] = $value;
    }

    public function getOption($key)
    {
        if (!array_key_exists($key, $this->options)) {
            $this->output->write("<error>No such option: {$key}</error>");
            return;
        }
        return $this->options[$key];
    }

    public function dumpOption($key=null)
    {
        if (null == $key) {
            foreach ($this->options as $key=>$value) {
                $this->dumpOption($key);
            }
        } else {
            if (!array_key_exists($key, $this->options)) {
                $this->output->write("<error>No such option: {$key}</error>");
                return;
            }
            $value = $this->options[$key];
            $this->output->write("<options=bold>{$key}</options=bold> = '{$value}'\n");
        }
    }

}
