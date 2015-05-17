<?php

namespace NoccyLabs\LogPipe\Decoder;

use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Metrics\MetricsLog;

class MetricsDecoder implements DecoderInterface
{

    const PATTERN_LOG   = "/^!metric\.log (.*?)$/";
    const PATTERN_ITEM  = "/^!metric\.item (.*?) (.*)$/";

    public function __construct(MetricsLog $metrics=null)
    {
        $this->metrics = $metrics;
    }

    public function canDecode(MessageInterface $message)
    {
        return preg_match(self::PATTERN_LOG, $message->getText())
            || preg_match(self::PATTERN_ITEM, $message->getText());
    }

    public function decode(MessageInterface $message)
    {

        if (!$this->metrics) {
            return false;
        }

        $match = null;
        if (preg_match(self::PATTERN_LOG, $message->getText(), $match)) {
            $key = $match[1];
            $data = $message->getExtra();
        } elseif (preg_match(self::PATTERN_ITEM, $message->getText(), $match)) {
            $key = $match[1];
            $data = [ 'value'=>$match[2] ];
        }

        $data['_timestamp'] = $message->getTimestamp();

        $this->metrics->log($message->getClientId(), $key, $data);

        return false;
    }

}
