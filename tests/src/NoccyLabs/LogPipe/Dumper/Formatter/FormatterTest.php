<?php


namespace NoccyLabs\LogPipe\Dumper;

use NoccyLabs\LogPipe\Message\MonologMessage;
use NoccyLabs\LogPipe\Message\ConsoleMessage;
use NoccyLabs\LogPipe\Dumper\Formatter\Formatter;

class FormatterTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {}

    public function teardown()
    {}

    public function getMessages()
    {
        $messages = [
            [ new MonologMessage(array (
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
            )),
                "<b>", "<b>[2015-03-07 04:20:39] main.EMERGENCY: Oh my god! [] []</b>"
            ],
            [ new ConsoleMessage(array(
                'message' => 'MESSAGE',
                'level' => 100
            )),
                "pre|post", "preMESSAGEpost"
            ],
            [ new ConsoleMessage(array(
                'message' => 'MESSAGE',
                'level' => 100
            )),
                function ($s) { return "foo|bar"; }, "fooMESSAGEbar"
            ],
            [ new ConsoleMessage(array(
                'message' => 'MESSAGE',
                'level' => 100
            )),
                NULL, "MESSAGE"
            ]
        ];

        return $messages;
    }

    /**
     * @param $message
     * @dataProvider getMessages
     */
    public function testFormatter($message, $style, $expect)
    {

        $formatter = new Formatter();
        $formatter->setMessageStyle($style);

        $output =  $message->format($formatter);

        $this->assertEquals($expect, $output);

    }

}
