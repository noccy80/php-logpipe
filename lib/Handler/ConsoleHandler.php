<?php

namespace NoccyLabs\LogPipe\Handler;

use NoccyLabs\LogPipe\Message\ConsoleMessage;
use NoccyLabs\LogPipe\Transport\TransportInterface;
use NoccyLabs\LogPipe\Transport\TransportFactory;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * The ConsoleHandler is poorly named, but relays errors and exceptions that would normally end up in the console
 * over a transport.
 *
 * @package NoccyLabs\LogPipe\Handler
 */
class ConsoleHandler implements LoggerInterface
{
    /**
     * @var
     */
    protected $transport_uri;
    /**
     * @var
     */
    protected $transport;
    /**
     * @var
     */
    protected $initialized;
    /**
     * @var
     */
    protected $client_id;

    /**
     * @param $transport
     */
    public function __construct($transport=null, $client_id=null)
    {
        $this->setClientId($client_id);
        $this->transport_uri = $transport?:DEFAULT_ENDPOINT;
    }

    /**
     * @param $client_id
     * @param null $request_id
     */
    public function setClientId($client_id, $request_id=null)
    {
        if (!$client_id) {
            $client_id = (getenv("APP_ID") ? : (defined("APP_ID") ? APP_ID : gethostname()));
        }
        if (!$request_id) {
            $request_id = sprintf("%04x%04x", rand(0,0xFFFF), rand(0,0xFFFF));
        }
        $this->client_id = sprintf("%s:%s", $client_id, $request_id);
    }

    /**
     * @param $enable
     * @param int $error_types
     */
    public function setErrorReporting($enable, $error_types = E_ALL)
    {
        static $enabled;
        static $previous;
        if ($enable && !$enabled) {
            // enable
            $previous = set_error_handler(array($this, "_onError"), $error_types);
        } elseif (!$enable && $enabled) {
            // disable
            set_error_handler($previous);
        }
    }

    /**
     * @param $enable
     */
    public function setExceptionReporting($enable)
    {
        static $enabled;
        static $previous;
        if ($enable && !$enabled) {
            // enable
            $previous = set_exception_handler(array($this, "_onException"));
        } elseif (!$enable && $enabled) {
            // disable
            set_exception_handler($previous);
        }
    }

    /**
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @param $errcontext
     * @return bool
     */
    public function _onError($errno, $errstr, $errfile, $errline, $errcontext)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        switch ($errno) {
            case E_ERROR:
            case E_USER_ERROR:
                $prefix="ERROR";
                $level=500; break;
            case E_WARNING:
            case E_USER_WARNING:
                $prefix="WARNING";
                $level=400; break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $prefix="NOTICE";
                $level=300; break;
            case E_STRICT:
                $prefix="STRICT";
                $level=100; break;
            default:
                $prefix="INFO";
                $level=200; break;
        }

        $record = [
            "channel"   => "php.error",
            "level"     => $level,
            "message"   => sprintf("[%s] %d: %s (in %s line %d)", $prefix, $errno, $errstr, $errfile, $errline),
            "_client_id"=> $this->client_id,
        ];

        $message = new ConsoleMessage($record, $this->client_id);
        $this->transport->send($message);
        return false;
    }

    /**
     * @param \Exception $e
     */
    public function _onException(\Exception $e)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $record = [
            "channel"   => "php.exception",
            "level"     => 500,
            "message"   => (string)$e,
            "_client_id"=> $this->client_id,
        ];

        $message = new ConsoleMessage($record, $this->client_id);
        $this->transport->send($message);
    }

    /**
     * @param array $record
     */
    protected function write(array $record)
    {

        if (!$this->initialized) {
            $this->initialize();
        }

        $message = new ConsoleMessage($record, $this->client_id);
        $this->transport->send($message);
    }

    /**
     *
     */
    private function initialize()
    {
        if ($this->transport_uri instanceof TransportInterface) {
            $this->transport = $this->transport_uri;
        } elseif (strpos($this->transport_uri, ":") !== false) {
            $this->transport = TransportFactory::create($this->transport_uri);
        } else {
            throw new \InvalidArgumentException("Unable to initialize transport from ".trim(print_r($this->transport_uri,true)));
        }

        $this->transport->connect();

        $this->initialized = true;
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array())
    {
        $this->log(700, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array())
    {
        $this->log(600, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array())
    {
        $this->log(550, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array())
    {
        $this->log(500, $message, $context);
    }


    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array())
    {
        $this->log(400, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array())
    {
        $this->log(300, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array())
    {
        $this->log(200, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array())
    {
        $this->log(100, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $record = [
            "channel"   => "php.error",
            "level"     => $level,
            "message"   => $message,
            "context"   => $context,
            "_client_id"=> $this->client_id,
        ];

        $message = new ConsoleMessage($record, $this->client_id);
        $this->transport->send($message);
    }

}
