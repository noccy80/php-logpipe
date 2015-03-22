<?php


namespace NoccyLabs\LogPipe\Filter;

use NoccyLabs\LogPipe\Message\MessageInterface;

class MessageFilter implements FilterInterface {

    protected $included_channels = [];

    protected $excluded_channels = [];

    protected $minimum_level = 0;

    protected static $level_map = array(
        "debug"     => 100,
        "info"      => 200,
        "notice"    => 250,
        "warning"   => 300,
        "error"     => 400,
        "critical"  => 500,
        "alert"     => 550,
        "emergency" => 600,
    );


    /**
     * Set the included channels.
     *
     * @param array $channels
     * @return mixed
     */
    public function setIncludedChannels($channels)
    {
        if (!is_array($channels)) {
            $channels = explode(",", $channels);
        }
        $this->included_channels = array_filter($channels);
        return $this;
    }

    /**
     * Get the included channels.
     *
     * @return mixed
     */
    public function getIncludedChannels()
    {
        return $this->included_channels;
    }

    /**
     * Set the excluded channels. The list provided is only used if no channels has been added to the include list.
     *
     * @param array $channels
     * @return mixed
     */
    public function setExcludedChannels($channels)
    {
        if (!is_array($channels)) {
            $channels = explode(",", $channels);
        }
        $this->excluded_channels = array_filter($channels);
        return $this;
    }

    /**
     * Get the excluded channels
     *
     * @return mixed
     */
    public function getExcludedChannels()
    {
        return $this->excluded_channels;
    }

    /**
     * Set the minimum level of events to include
     *
     * @param $level
     * @return mixed
     */
    public function setMinimumLevel($level)
    {
        // Check if we got a non-numeric level, such as "info" or "warning"
        if (!is_numeric($level)) {
            $level = strtolower($level);
            if (!array_key_exists($level, self::$level_map)) {
                throw new \InvalidArgumentException("Invalid log level {$level}. Expected numeric 0-600 or one of ".join(", ", array_keys(self::$level_map)));
            }
            $level = self::$level_map[$level];
        }

        if (($level < 0) || ($level > 600)) {
            throw new \InvalidArgumentException("Invalid log level {$level}. Expected numeric 0-600 or one of ".join(", ", array_keys(self::$level_map)));
        }

        $this->level = $level;
        return $this;
    }

    /**
     * Get the minimum level of events to include
     *
     * @return mixed
     */
    public function getMinimumLevel()
    {
        return $this->minimum_level;
    }

    /**
     * Filter a message and return NULL if the message is to be discarded.
     *
     * @param $message
     * @return mixed
     */
    public function filterMessage(MessageInterface $message)
    {
        if ($this->isChannelFiltered($message->getChannel())) {
            return NULL;
        }

        if ($this->isLevelFiltered($message->getLevel())) {
            return NULL;
        }

        return $message;
    }

    public function isChannelFiltered($channel)
    {
        if (count($this->included_channels) > 0) {
            return !in_array($channel, $this->included_channels);
        }

        if (count($this->excluded_channels)) {
            return in_array($channel, $this->excluded_channels);
        }

        return false;
    }

    public function isLevelFiltered($level)
    {
        return ($level < $this->minimum_level);
    }

}
