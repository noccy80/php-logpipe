LogPipe
=======

LogPipe is used to send logging information over a named pipe to a client that is
dumping it to the console or wherever.

 1. The `pipecat` utility is used to create the named pipe.
 2. The application will look to see if the expected pipe exists, and if so dump
    the logging output to the pipe.
 3. `pipecat` will parse the logs and output it as desired.
 4. When `pipecat` is closed, the named pipe is removed, and subsequent application
    runs will not attempt to log to the pipe.


## Using

    use LogPipe\Logger\SimpleLogger;

    $logger = new SimpleLogger(__DIR__."/logpipe");
    $logger->debug("hello world");

And pipecat:

    $ pipecat dump logpipe
    hello world
    ^C
    $
