<?php

namespace NoccyLabs\LogPipe\Metrics\Processor;

class ValueProcessor implements ProcessorInterface
{
    protected $key;

    protected $result = [];

    protected $columns;

    public function __construct($key, $options)
    {
        $this->key = $key;
        $this->options = $options;

        if (is_array($options)) {
            $this->columns = $options;
        } else {
            $this->columns = true;
        }
    }

    public function process($record)
    {
        if ($record->key == $this->key) {
            if ($this->columns === true) {
                $this->result[] = $record->data;
            } else {
                foreach ($this->columns as $colname) {
                    if (!empty($record->data->{$colname})) {
                        if (!array_key_exists($colname, $this->result)) {
                            $this->result[$colname] = [];
                        }
                        $this->result[$colname][] = $record->data->{$colname};
                    }
                }   
            }
        }
    }

    public function produce()
    {
        return [
            $this->key => [
                "values" => $this->result
            ]
        ];
    }

}
