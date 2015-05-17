<?php

namespace NoccyLabs\LogPipe\Dumper;
use NoccyLabs\LogPipe\Message\MessageInterface;

/**
 * Class DefaultDumper
 * @package NoccyLabs\LogPipe\Dumper
 */
class DefaultDumper extends AbstractDumper
{
    /**
     * @param array $record
     */
    public function dump(MessageInterface $message)
    {
        $client     = $message->getClientId();

        $message = $this->decode($message);
        if (!$message) {
            return;
        }

        printf("%s %s\n",
            $client, (string)$message);
    }
}
