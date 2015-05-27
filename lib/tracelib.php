<?php

class TraceLib
{
    private static $format;

    public static function init()
    {
        static $initd;
        if ($initd)
            return;
        $initd = true;
        self::find_root();
        self::init_real();
        self::elapsed();
    }
    
    public static function setFormat($format)
    {
        self::$format = $format;
    }
    
    public static function getFormat()
    {
        return self::$format;
    }
    
    public static function format($message, array $trace)
    {
        if (is_string($message)) {
            $trace['message'] = $message;
        } else {
            $trace['message'] = "[".trim(str_replace("\n", "\n  ",print_r($message,true)))."]";
        }
        $output = preg_replace_callback("/(\{(.*?)\})/", function ($match) use ($trace) {
            if (array_key_exists($match[2], $trace)) {
                return $trace[$match[2]];
            }
            return $match[0];
        }, self::$format);
        return $output;
    }
    
    public static function elapsed()
    {
        static $start;
        if (!$start)
            $start = microtime(true);
        return sprintf("%.3f",microtime(true) - $start);
    }

    public static function parseBacktrace(array $bt)
    {
        $file = defined("TRACE_ROOT") ? str_replace(TRACE_ROOT."/","",$bt[0]['file']) : $bt[0]['file'];
        $line = $bt[0]['line'];
        if (count($bt)>1) {
            if (!array_key_exists('type', $bt[1])) {
                $func = $bt[1]['function'];
                if ($func[0] != "{") $func = "(".$func.")";
            } else {
                switch ($bt[1]['type']) {
                    case '::':
                        $func = sprintf('(%s::%s)', $bt[1]['class'], $bt[1]['function']);
                        break;
                    case '->':
                        $func = sprintf('(%s->%s)', $bt[1]['class'], $bt[1]['function']);
                        break;
                }
            }
        } else {
            $func = "{global}";
        }
        
        return [
            "file" => $file,
            "line" => $line,
            "func" => $func,
            "elapsed" => self::elapsed()
        ];
    }

    private static function find_root() {
        $wd = __DIR__;
        while (strlen($wd)>1) {
            if (file_exists($wd."/composer.json")) {
                define("TRACE_ROOT", $wd);
                break;
            }
            $wd = dirname($wd);
        }
    }
    
    private static function init_real() {
        function trace($string) {
            if (!defined("TRACE")) return;

            $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $data = TraceLib::parseBacktrace($bt);
            $out = TraceLib::format($string, $data);

            fprintf(STDERR, "%s\n", $out);
        }
    }
}

if (getenv("TRACE")) {
    define("TRACE", true);
}

TraceLib::init();
TraceLib::setFormat("{file}:{line} {elapsed} {message}");

