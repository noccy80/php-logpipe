<?php


namespace NoccyLabs\LogPipe\Dumper\Output;


use NoccyLabs\LogPipe\Message\MessageInterface;

interface OutputInterface
{
    public function write(MessageInterface $message);
}