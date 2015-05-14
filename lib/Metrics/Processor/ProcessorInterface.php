<?php

namespace NoccyLabs\LogPipe\Metrics\Processor;

interface ProcessorInterface
{

    public function process($record);

    public function produce();
}
