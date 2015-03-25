<?php

namespace NoccyLabs\LogPipe\Application\LogDumper;

use NoccyLabs\LogPipe\Posix\SignalListener;
use NoccyLabs\LogPipe\Transport\TransportInterface;
use NoccyLabs\LogPipe\Filter\FilterInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LogDumper
{

    protected $transport;

    protected $dumper;

    protected $filter;

    protected $output;

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

    public function run()
    {
        $signal = new SignalListener(SIGINT);

        declare(ticks=5);

        $squelched = 0;

        while (!$signal()) {

            $msg = $this->transport->receive();
            if ($msg) {
                if (($out = $this->filter->filterMessage($msg))) {
                    if (($squelched > 0) && ($squelch_info)) {
                        $this->output->writeln("\r<fg=black;bg=yellow> {$squelched}</fg=black;bg=yellow><fg=black;bg=yellow;options=bold> messages squelched </fg=black;bg=yellow;options=bold>");
                        $squelched = 0;
                    }
                    $this->dumper->dump($out);
                } else {
                    $squelched++;
                    if ($squelch_info) {
                        $this->output->write("\r<fg=black;bg=yellow> {$squelched} </fg=black;bg=yellow>");
                    }
                }
            }

            usleep(100);
        }
    }

}
