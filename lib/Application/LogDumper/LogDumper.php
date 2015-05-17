<?php

namespace NoccyLabs\LogPipe\Application\LogDumper;

use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Posix\SignalListener;
use NoccyLabs\LogPipe\Transport\TransportInterface;
use NoccyLabs\LogPipe\Filter\FilterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use NoccyLabs\LogPipe\Common\FifoBuffer;
use NoccyLabs\LogPipe\Decoder\ExceptionDecoder;
use NoccyLabs\LogPipe\Decoder\MetricsDecoder;

/**
 * Class LogDumper
 * @package NoccyLabs\LogPipe\Application\LogDumper
 */
class LogDumper
{

    /**
     * @var
     */
    protected $transport;

    /**
     * @var
     */
    protected $dumper;

    /**
     * @var
     */
    protected $filter;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var bool
     */
    protected $squelch_info = true;

    protected $squelched = 0;

    protected $timeout = null;

    /**
     * @param TransportInterface $transport
     */
    public function setTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param $dumper
     */
    public function setDumper($dumper)
    {
        $this->dumper = $dumper;
        $dumper->clearDecoders();
        $dumper->addDecoder(new ExceptionDecoder());
        return $dumper;
    }

    /**
     * @param FilterInterface $filter
     */
    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param $show
     */
    public function setShowSquelchInfo($show)
    {
        $this->squelch_info = $show;
    }

    public function setTimeout($timeout=null)
    {
        $this->timeout = $timeout;
    }

    /**
     *
     */
    public function run()
    {
        $signal = new SignalListener(SIGINT);

        declare(ticks=5);

        $squelched = 0;

        $break_at = ($this->timeout) ? time()+$this->timeout : null;

        while (!$signal()) {

            $msg = $this->transport->receive();
            if ($msg) {
                $this->onMessage($msg);
            } else {
                usleep(10000);
            }

            if ($break_at && ($break_at < time())) {
                break;
            }

        }

    }

    protected function onMessage(MessageInterface $msg)
    {
        if (($out = $this->filter->filterMessage($msg))) {
            if (($this->squelched > 0) && ($this->squelch_info)) {
                $this->output->writeln("\r<fg=black;bg=yellow> {$this->squelched}</fg=black;bg=yellow><fg=black;bg=yellow;options=bold> messages squelched </fg=black;bg=yellow;options=bold>");
                $this->squelched = 0;
            }
            $this->output->write("\r\e[K");
            $this->dumper->dump($out);
        } else {
            $this->squelched++;
            if ($this->squelch_info) {
                $this->output->write("\r<fg=black;bg=yellow> {$this->squelched} </fg=black;bg=yellow>");
            }
        }
    }

}
