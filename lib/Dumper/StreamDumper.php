<?php


namespace NoccyLabs\LogPipe\Dumper;


class StreamDumper {

    public function __construct($output)
    {
        if (!is_resource($output)) {
            throw new \InvalidArgumentException("First argument to StreamDumper constructor must be a valid stream");
        }
        $this->output = $output;
        $this->formatter = new Formatter();
        $this->formatter->setMessageStyle(null);
    }

    public function dump(MessageInterface $message)
    {
        $client     = $message->getClientId();

        $output = $this->formatter->format($message);

        fprintf($this->output, "%s %s", $client, $output)
    }

}
