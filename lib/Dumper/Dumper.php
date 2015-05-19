<?php


namespace NoccyLabs\LogPipe\Dumper;


use NoccyLabs\LogPipe\Dumper\Decoder\DecoderInterface;
use NoccyLabs\LogPipe\Dumper\Filter\ExpressionFilter;
use NoccyLabs\LogPipe\Dumper\Filter\FilterInterface;
use NoccyLabs\LogPipe\Dumper\Filter\UserFilter;
use NoccyLabs\LogPipe\Dumper\Output\OutputInterface;
use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Transport\TransportFactory;
use NoccyLabs\LogPipe\Transport\TransportInterface;

/**
 * Dumper class, for simplified dumping of messages from transports via filters to outputs.
 *
 * Messages can be dumped manually:
 *
 *      $dumper->addOutput(new ConsoleOutput());
 *      if (($msg = $transport->read())) {
 *          $dumper->dumpMessage($msg);
 *      }
 *
 * Or the dumper can do the dumping for you:
 *
 *      ...
 *      $dumper->addTransport($transport);
 *      $dumper->update();
 *
 *
 *
 * @package NoccyLabs\LogPipe\Dumper
 */
class Dumper
{
    /**
     * @var FilterInterface[] The filters to apply
     */
    protected $filters = [];

    /**
     * @var OutputInterface[] The outputs to be called with data not filtered
     */
    protected $outputs = [];

    /**
     * @var TransportInterface[] Transports
     */
    protected $transports = [];

    /**
     * @var \NoccyLabs\LogPipe\Dumper\Decoder\DecoderInterface[] Decoders
     */
    protected $decoders = [];

    /**
     * Create a transport from a URI
     *
     * @param null $endpoint
     */
    public function createTransport($endpoint=null)
    {
        $transport = TransportFactory::create($endpoint?:DEFAULT_ENDPOINT);
        $transport->listen();
        $this->addTransport($transport);
    }

    /**
     * @param TransportInterface $transport
     */
    public function addTransport(TransportInterface $transport)
    {
        $this->transports[] = $transport;
    }

    /**
     * @param FilterInterface $filter
     * @param int $priority Priority of this filter (negative=earlier, positive=later)
     */
    public function addFilter(FilterInterface $filter, $priority=0)
    {
        $this->filters[] = [ $priority, $filter ];
        uasort(
            $this->filters,
            function ($a, $b) {
                return $a[0]-$b[0];
            });
    }

    /**
     * @param callable $filter_func
     * @param int $priority Priority of this filter (negative=earlier, positive=later)
     */
    public function addUserFilter(callable $filter_func, $priority=0)
    {
        $filter = new UserFilter($filter_func);
        $this->filters[] = [ $priority, $filter ];
        uasort(
            $this->filters,
            function ($a, $b) {
                return $a[0]-$b[0];
            });
    }

    /**
     * @param $expression
     * @param int $priority Priority of this filter (negative=earlier, positive=later)
     */
    public function addExpressionFilter($expression, $priority=0)
    {
        $filter = new ExpressionFilter($expression);
        $this->filters[] = [ $priority, $filter ];
        uasort(
            $this->filters,
            function ($a, $b) {
                return $a[0]-$b[0];
            });
    }

    /**
     * @param DecoderInterface $decoder
     * @param int $priority Priority of this decoder (negative=earlier, positive=later)
     */
    public function addDecoder(DecoderInterface $decoder, $priority=0)
    {
        $this->decoders[] = [ $priority, $decoder ];
        uasort(
            $this->decoders,
            function ($a, $b) {
                return $a[0]-$b[0];
            });
    }

    /**
     * @param OutputInterface $output
     */
    public function addOutput(OutputInterface $output)
    {
        $this->outputs[] = $output;
    }

    /**
     * @param MessageInterface $message
     */
    public function dumpMessage(MessageInterface $message)
    {
        // Filters return true or false, with false indicating the message should be discarded
        $filtered = false;
        foreach ($this->filters as $filter) {
            if (!$filter[1]->filterMessage($message, $filtered)) {
                $filtered = true;
            }
        }
        if ($filtered) {
            return;
        }

        // Decoders will always receive all messages, and can have them discarded after processing by returning false
        $discard = false;
        foreach ($this->decoders as $decoder) {
            $decoded = $decoder[1]->decode($message);
            if (false === $decoded) {
                $discard = true;
            } else {
                $message = $decoded;
            }
        }

        // Discard message if flagged
        if ($discard) {
            return;
        }

        // All outputs are called
        foreach ($this->outputs as $output) {
            $output->write($message);
        }
    }

    /**
     *
     */
    public function updateTransports()
    {
        foreach ($this->transports as $transport) {
            while (($read = $transport->receive(false))) {
                $this->dumpMessage($read);
            }
        }
    }

}