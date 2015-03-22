<?php


namespace NoccyLabs\LogPipe\Filter;

use NoccyLabs\LogPipe\Message\MessageInterface;

/**
 * Interface FilterInterface
 * @package NoccyLabs\LogPipe\Filter
 */
interface FilterInterface {

    /**
     * Filter a message and return NULL if the message is to be discarded.
     *
     * @param $message
     * @return mixed
     */
    public function filterMessage(MessageInterface $message);

}
