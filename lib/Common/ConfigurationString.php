<?php


namespace NoccyLabs\LogPipe\Common;


class ConfigurationString {

    protected $options;

    protected $type;

    protected $resource;

    public function __construct($uri)
    {
        $this->parseString($uri);
    }

    public function parseString($uri)
    {
        if (strpos($uri,":")===false) {
            $uri = "pipe:{$uri}";
        }

        list ($type, $resource) = explode(":", $uri, 2);

        $options = [];
        if (strpos($resource,"?")!==false) {
            list ($resource, $attr) = explode("?", $resource, 2);
            parse_str($attr, $options);
        }

        $this->type = $type;
        $this->resource = $resource;
        $this->options = $options;

    }

    public function getType()
    {
        return $this->type;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function getOption($key, $def=null)
    {
        return array_key_exists($key, $this->options)
            ? $this->options[$key]
            : $def;
    }

    public function getAllOptions()
    {
        return $this->options;
    }

}