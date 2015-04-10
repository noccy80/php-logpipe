<?php


namespace NoccyLabs\LogPipe\Dumper;


use NoccyLabs\LogPipe\Message\MessageInterface;

/**
 * Class StreamDumper
 * @package NoccyLabs\LogPipe\Dumper
 */
class StreamDumper {

    /**
     * @param $output
     */
    public function __construct($output)
    {
        if (!is_resource($output)) {
            throw new \InvalidArgumentException("First argument to StreamDumper constructor must be a valid stream");
        }
        $this->output = $output;
        $this->formatter = new Formatter();
        $this->formatter->setMessageStyle(null);
    }

    /**
     * @param MessageInterface $message
     */
    public function dump(MessageInterface $message)
    {
        $client     = $message->getClientId();

        $output = $this->formatter->format($message);

        fprintf($this->output, "%s %s", $client, $output);
    }

}
