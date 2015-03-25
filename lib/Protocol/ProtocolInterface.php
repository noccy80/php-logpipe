<?php

namespace NoccyLabs\LogPipe\Protocol;

use NoccyLabs\LogPipe\Message\MessageInterface;

interface ProtocolInterface
{
    public function getTag();

    public function unpack(&$buffer);

    public function pack(MessageInterface $message);
}
