<?php

namespace NoccyLabs\LogPipe\Decoder;

use NoccyLabs\LogPipe\Message\MessageInterface;

interface DecoderInterface
{
    public function decode(MessageInterface $message);
}
