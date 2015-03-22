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

    protected $record = [];

    protected $client_id;

    public function __construct(array $record=null, $client_id=null)
    {
        $this->record = $record;

        // TODO: This is to handle unserializable extra data. There has to be a better
        // way to do this.
        unset ($this->record['extra']);
        unset ($this->record['datetime']);

        $this->client_id = $client_id?:uniqid();
    }

    public function getData()
    {
        return [ $this->client_id, $this->record ];
    }

    public function setData(array $data)
    {
        $this->client_id = $data[0];
        $this->record = (array)$data[1];
    }

    public function getChannel()
    {
        return array_key_exists("channel", $this->record)
            ? $this->record["channel"]
            : null;
    }

    public function getLevel()
    {
        return array_key_exists("level", $this->record)
            ? $this->record["level"]
            : 0;
    }

    public function getMessage()
    {
        return rtrim($this->record['formatted']);
    }

    public function getClientId()
    {
        return $this->client_id;
    }

    public function format(Formatter $formatter)
    {
        return $formatter->format($this);
    }

}
