<?php


namespace NoccyLabs\LogPipe\Dumper\Filter;

use NoccyLabs\LogPipe\Message\MessageInterface;

/**
 * Interface FilterInterface
 * @package NoccyLabs\LogPipe\Filter
 */
interface FilterInterface {

    /**
     * Filter a message and return NULL if the message is to be discarded.
     *
     * @param MessageInterface $message The message
     * @param bool $filtered If true, the message has been previously filtered (i.e. previous filter returned false)
     * @return bool True if the message should be included, false if it should be discard
     */
    public function filterMessage(MessageInterface $message, $filtered);

}
