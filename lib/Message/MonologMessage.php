<?php

namespace NoccyLabs\LogPipe\Message;

use NoccyLabs\LogPipe\Dumper\Formatter;
use NoccyLabs\LogPipe\Common\ArrayUtils;

/**
 * Class that wraps a Monolog record for serialization
 *
 * @package NoccyLabs\LogPipe\Message
 */
class MonologMessage implements MessageInterface {

    /**
     * @var array
     */
    protected $record = [];

    /**
     * @var string
     */
    protected $client_id;

    /**
     * @param array $record
     * @param null $client_id
     */
    public function __construct(array $record=null, $client_id=null)
    {
        $this->record = $record;

        // TODO: This is to handle unserializable extra data. There has to be a better
        // way to do this.
        if ($this->record) {
            $extra = array_key_exists('context', $this->record) ? $this->record['context'] : [];
            $this->record['context'] = ArrayUtils::sanitize($extra);
        }
        if (isset($this->record['datetime'])) {
            $this->record['datetime'] = $this->record['datetime']->format('U');
        }

        // unset ($this->record['datetime']);

        $this->client_id = $client_id?:uniqid();
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return [ $this->client_id, $this->record ];
    }

    /**
     * {@inheritdoc}
     */
    public function setData(array $data)
    {
        $this->client_id = $data[0];
        $this->record = (array)$data[1];
    }

    /**
     * {@inheritdoc}
     */
    public function getChannel()
    {
        return array_key_exists("channel", $this->record)
            ? $this->record["channel"]
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getLevel()
    {
        return array_key_exists("level", $this->record)
            ? $this->record["level"]
            : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getText()
    {
        return $this->record['message'];
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return rtrim($this->record['formatted']);
    }

    public function setMessage($message)
    {
        $this->record['formatted'] = $message;
    }

    /**
     * {@inheritdoc}
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp()
    {
        return $this->record['datetime'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSource()
    {
        return NULL;
    }

    public function getExtra()
    {
        return (array)$this->record['context'];
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
    public function __toString()
    {
        return $this->getMessage();
    }
}
