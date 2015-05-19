<?php

namespace NoccyLabs\LogPipe\Dumper\Output;

use NoccyLabs\LogPipe\Message\MonologMessage;
use NoccyLabs\LogPipe\Message\ConsoleMessage;
use Prophecy\PhpUnit\ProphecyTestCase;

abstract class OutputTestAbstract extends ProphecyTestCase
{
    public function getMessages()
    {
        $messages = [];

        $messages[] = [
            new MonologMessage(array (
                'message' => 'Oh my god!',
                'context' => array (),
                'level' => 600,
                'level_name' => 'EMERGENCY',
                'channel' => 'main',
                'datetime' => \DateTime::__set_state(array(
                    'date' => '2015-03-07 04:20:39',
                    'timezone_type' => 3,
                    'timezone' => 'Europe/Berlin',
                )),
                'extra' => array (),
                'formatted' => '[2015-03-07 04:20:39] main.EMERGENCY: Oh my god! [] []'
            )) ]
        ;

        $messages[] = [new ConsoleMessage([
            "_client_id" => "tester",
            "message" => "This is a message",
            "channel" => "php.ERROR",
            "level" => 600
        ])];

        $messages[] = [new ConsoleMessage([
            "_client_id" => "long_message",
            "message" => str_repeat("Hello",10000),
            "channel" => "test",
            "level" => 100
        ])];

        $messages[] = [new ConsoleMessage([
            "_client_id" => "long_message",
            "message" => str_repeat("Hello",10000),
            "channel" => "test",
            "level" => 200
        ])];

        $messages[] = [new ConsoleMessage([
            "_client_id" => "long_message",
            "message" => str_repeat("Hello",10000),
            "channel" => "test",
            "level" => 300
        ])];

        $messages[] = [new ConsoleMessage([
            "_client_id" => "long_message",
            "message" => str_repeat("Hello",10000),
            "channel" => "test",
            "level" => 400
        ])];

        $messages[] = [new ConsoleMessage([
            "_client_id" => "long_message",
            "message" => str_repeat("Hello",10000),
            "channel" => "test",
            "level" => 500
        ])];

        $messages[] = [new ConsoleMessage([
            "_client_id" => "long_message",
            "message" => str_repeat("Hello",10000),
            "channel" => "test",
            "level" => 550
        ])];

        return $messages;
    }
}
