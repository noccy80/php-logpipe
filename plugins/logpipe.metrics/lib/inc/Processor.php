<?php

namespace NoccyLabs\LogPipe\Metrics;

use NoccyLabs\LogPipe\Metrics\Processor\ProcessorInterface;
use Symfony\Component\Yaml\Yaml;

class Processor
{
    /**
     * Set the log to read
     *
     */
    public function setMetricsLog(MetricsLog $log)
    {
        $this->metrics = $log;
    }

    public function addProcessor(ProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    /**
     * Process the events in the log, and write the resulting parsed data to
     * the filename in $output as yaml.
     *
     * @param string $output The yaml file to write
     */
    public function process($output=null)
    {
    
        while (($record = $this->metrics->read())) {
            foreach ($this->processors as $key=>$processor) {
                $processor->process($record);
            }
        }
        
        $produced = [];
        foreach ($this->processors as $processor) {
            $produced = array_merge($produced, (array)$processor->produce());
        }
        
        $yaml = Yaml::dump($produced, 3);

        echo $yaml."\n";
    
    }
}
