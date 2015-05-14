<?php

namespace NoccyLabs\LogPipe\Metrics;

class ProcessorDefinitionLoader
{
    public function load(Processor $processor, array $definitions)
    {
        foreach ($definitions as $key=>$definition) {
            
            $this->parseDefinitionsForKey($processor, $key, $definition);


        }
    }
    
    protected function parseDefinitionsForKey(Processor $processor, $key, array $definition)
    {
        foreach ($definition as $processor_name=>$config) {
            $data_processor = $this->createDataProcessor($processor_name, $key, $config);
            $processor->addProcessor($data_processor);
        }
    }
    
    protected function createDataProcessor($name, $key, $options)
    {
        $ucname = str_replace(" ","", ucwords( str_replace("_", " ", $name) ) );
        $class = "NoccyLabs\\LogPipe\\Metrics\\Processor\\{$ucname}Processor";
        $inst = new $class($key, $options);
        return $inst;
    }
}
