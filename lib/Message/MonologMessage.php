<?php

namespace NoccyLabs\LogPipe\Message;

use NoccyLabs\LogPipe\Dumper\Formatter;

/**
 * Monolog records:
 * array(8) {
 *   'message' => string(10) "Oh my god!"
 *  'context' => array(0) {}
 *  'level' => int(600)
 *  'level_name' => string(9) "EMERGENCY"
 *  'channel' => string(4) "main"
 *  'datetime' => class DateTime#6 (3) {
 *      public $date => string(19) "2015-03-07 03:38:45"
 *      public $timezone_type => int(3)
 *      public $timezone => string(13) "Europe/Berlin"
 *  }
 *  'extra' => array(0) {}
 *  'formatted' => string(55) "[2015-03-07 03:38:45] main.EMERGENCY: Oh my god! [] []"
 * }
 *
 *
 * Class MonologMessage
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
        unset ($this->record['extra']);
        unset ($this->record['datetime']);

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

    /**
     * @param Formatter $formatter
     * @return MessageInterface|string
     */
    public function format(Formatter $formatter)
    {
        return $formatter->format($this);
    }

}
