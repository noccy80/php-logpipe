<?php


namespace NoccyLabs\LogPipe\Dumper;


use NoccyLabs\LogPipe\Decoder\DecoderInterface;
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
     * @var DecoderInterface[] Decoders
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
     */
    public function addFilter(FilterInterface $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * @param callable $filter_func
     */
    public function addUserFilter(callable $filter_func)
    {
        $this->filters[] = new UserFilter($filter_func);
    }

    /**
     * @param $expression
     */
    public function addExpressionFilter($expression)
    {
        $this->filters[] = new ExpressionFilter($expression);
    }

    /**
     * @param DecoderInterface $decoder
     */
    public function addDecoder(DecoderInterface $decoder)
    {
        $this->decoders[] = $decoder;
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
            if (!$filter->filterMessage($message, $filtered)) {
                $filtered = true;
            }
        }
        if ($filtered) {
            return;
        }

        // Decoders will always receive all messages, and can have them discarded after processing by returning false
        $discard = false;
        foreach ($this->decoders as $decoder) {
            $decoded = $decoder->decode($message);
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