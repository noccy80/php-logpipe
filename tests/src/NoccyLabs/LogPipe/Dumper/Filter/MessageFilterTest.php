<?php


namespace NoccyLabs\LogPipe\Filter;

use NoccyLabs\LogPipe\Dumper\Filter\MessageFilter;
use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Message\MonologMessage;

class MessageFilterTest extends \PhpUnit_Framework_TestCase {

    protected $filter;

    public function setup()
    {
        $this->filter = new MessageFilter();
    }

    /**
     * @dataProvider getMessages
     */
    public function testThatTheFilterDoesNotBlockByDefault($message)
    {
        $out = $this->filter->filterMessage($message);
        $this->assertNotNull($out);
    }

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

        return $messages;
    }
}
