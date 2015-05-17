<?php

namespace NoccyLabs\LogPipe\Dumper\Decoder;

use NoccyLabs\LogPipe\Dumper\Decoder\DecoderInterface;
use NoccyLabs\LogPipe\Message\MessageInterface;

class ExceptionDecoder implements DecoderInterface
{

    public function canDecode(MessageInterface $message)
    {
        return preg_match("/exception '(.+?)' with message '(.+?)' in .* Stack trace: (.+?)$/", $message);
    }

    public function decode(MessageInterface $message)
    {

        $match = null;
        if (!preg_match("/exception '(.+?)' with message '(.+?)' in .* Stack trace: (.+?)$/", $message, $match)) {
            return false;
        }

        $trace = $match[3];
        $lines = explode(" #", $trace);

        $msgstring = sprintf(
            "Exception: '%s'\nMessage: '%s'\nTrace:\n    %s",
            $match[1],
            $match[2],
            join("\n    #", $lines)
        );

        $message->setMessage($msgstring);

        return $message;
    }

}
