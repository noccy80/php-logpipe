<?php


namespace NoccyLabs\LogPipe\Filter;


/**
 * Interface FilterInterface
 * @package NoccyLabs\LogPipe\Filter
 */
interface FilterInterface {

    /**
     * Set the included channels.
     *
     * @param array $channels
     * @return mixed
     */
    public function setIncludedChannels(array $channels);

    /**
     * Get the included channels.
     *
     * @return mixed
     */
    public function getIncludedChannels();

    /**
     * Set the excluded channels. The list provided is only used if no channels has been added to the include list.
     *
     * @param array $channels
     * @return mixed
     */
    public function setExcludedChannels(array $channels);

    /**
     * Get the excluded channels
     *
     * @return mixed
     */
    public function getExcludedChannels();

    /**
     * Set the minimum level of events to include
     *
     * @param $level
     * @return mixed
     */
    public function setMinimumLevel($level);

    /**
     * Get the minimum level of events to include
     *
     * @return mixed
     */
    public function getMinimumLevel();

    /**
     * Filter a message and return NULL if the message is to be discarded.
     *
     * @param $message
     * @return mixed
     */
    public function filterMessage($message);

}