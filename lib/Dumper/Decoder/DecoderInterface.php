<?php

namespace NoccyLabs\LogPipe\Dumper\Decoder;

use NoccyLabs\LogPipe\Message\MessageInterface;

interface DecoderInterface
{
    public function decode(MessageInterface $message);
}
