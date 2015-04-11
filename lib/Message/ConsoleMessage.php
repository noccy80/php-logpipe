<?php

namespace NoccyLabs\LogPipe\Message;

use NoccyLabs\LogPipe\Dumper\Formatter;

/**
 * Class that wraps a simple message for serialization
 *
 * @package NoccyLabs\LogPipe\Message
 */
class ConsoleMessage implements MessageInterface {

    /**
     * @var array
     */
    protected $message = [];

    /**
     * @param array $message
     */
    public function __construct(array $message=null)
    {
        $this->message = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data)
    {
        $this->message = $data;
    }

    /**
     * @return mixed
     */
    public function getChannel()
    {
        return $this->message['channel'];
    }

    /**
     * {@inheritdoc}
     */
    public function getLevel()
    {
        return $this->message['level'];
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message['message'];
    }

    /**
     * {@inheritdoc}
     */
    public function getText()
    {
        return $this->getMessage();
    }

    /**
     * {@inheritdoc}
     */
    public function getClientId()
    {
        return $this->message["_client_id"];
    }

    /**
     * @param Formatter $formatter
     * @return MessageInterface|string
     */
    public function format(Formatter $formatter)
    {
        return $formatter->format($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp()
    {
        return array_key_exists('timestamp', $this->message)
            ? $this->message['timestamp']
            : NULL;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource()
    {
        return NULL;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getMessage();
    }
}
