<?php

namespace NoccyLabs\LogPipe\Application\LogDumper;

use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Message\MessageEvent;
use NoccyLabs\LogPipe\Application\LogDumper\DumperEvent;
use NoccyLabs\LogPipe\Posix\SignalListener;
use NoccyLabs\LogPipe\Transport\TransportInterface;
use NoccyLabs\LogPipe\Dumper\Filter\FilterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use NoccyLabs\LogPipe\Common\FifoBuffer;
use NoccyLabs\LogPipe\Dumper\Decoder\ExceptionDecoder;
use NoccyLabs\LogPipe\Dumper\Decoder\MetricsDecoder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use NoccyLabs\LogPipe\Application\LogPipeApplication;

/**
 * Class LogDumper
 * @package NoccyLabs\LogPipe\Application\LogDumper
 */
class LogDumper
{

    protected $app;

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

    protected $eventDispatcher;
    
    public function __construct(LogPipeApplication $app)
    {
        $this->app = $app;
    }
    
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
     * @param \NoccyLabs\LogPipe\Dumper\Filter\FilterInterface $filter
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

        $inBatch = false;;
        
        $this->eventDispatcher->dispatch(DumperEvent::DUMPING, new DumperEvent());

        while (!$signal()) {

            while ($msg = $this->transport->receive()) {
                if (!$inBatch) {
                    $this->eventDispatcher->dispatch(DumperEvent::BEFORE_BATCH, new DumperEvent());
                    $inBatch = true;
                }
                $this->onMessage($msg);
                usleep(100);
            }
            if ($inBatch) {
                $this->eventDispatcher->dispatch(DumperEvent::AFTER_BATCH, new DumperEvent());
                $inBatch = false;
            }
            usleep(10000);

            if ($break_at && ($break_at < time())) {
                break;
            }

        }

        $this->eventDispatcher->dispatch(DumperEvent::TERMINATING, new DumperEvent());
        
    }

    protected function onMessage(MessageInterface $msg)
    {
        if ($this->eventDispatcher) {
            $evt = new MessageEvent($msg);
            $this->eventDispatcher->dispatch("message.pre_filter", $evt);
        }
        if (!($this->filter->filterMessage($msg, false))) {
            $this->dumper->dump($msg);
        } else {
            $evt = new MessageEvent($msg);
            $this->eventDispatcher->dispatch(MessageEvent::SQUELCHED, $evt);
        }
    }

    public function setEventDispatcher(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }
    
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

}
