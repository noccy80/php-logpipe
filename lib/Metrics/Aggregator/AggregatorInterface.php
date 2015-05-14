<?php

namespace NoccyLabs\LogPipe\Metrics\Aggregator;

interface AggregatorInterface
{

    /**
     * Process a record
     *
     * @param object $record The record to process
     */
    public function process($record);

    /**
     *
     * @return array The parsing output
     */
    public function aggregate();

}
