LogPipe Use Cases
=================

## Tracing execution of Symfony applications

By enabling the LogPipeHandler in your applications configuration, all Monolog
log output will be sent through logpipe. Then, all you have to do to start
dumping the logs is:

    $ logpipe dump -i

Don't worry if you miss something, with the interactive dumper (`-i`) you can filter
an internal buffer to show events matching a regular expression. While dumping, hit
slash (`/`) to enter filtering mode. Typing `/exception/i` will do a case insensitive
search on the word "exception" and show all messages with a text matching the pattern.


## Monitoring server logs

You can use the `log:pass` command to pass standard input through:

    $ cat /var/log/nginx/access.log | logpipe log:pass --channel nginx

You can even pie dmesg through to logpipe:

    $ dmesg -w | logpipe log:pass --channel dmesg


## Tracking server metrics

Dump metrics from within the application:

    $this->logger->info("!metric stats", [
        "peak" => memory_get_peak_usage(),
        "timer" => $elapsed_time
    ]);

Capture them using the dump command

    $ logpipe dump -m metrics.log -q
    
For more information, see [metrics-format.md](metrics-format.md).

## Adding logging to shell scripts

Logging from shell scripts is as simple. Put this in `logpipe.inc`:

    function log.info   { logpipe write --channel $(basename $0) --info    "$1"; }
    function log.debug  { logpipe write --channel $(basename $0) --debug   "$1"; }
    function log.warn   { logpipe write --channel $(basename $0) --warning "$1"; }
    function log.error  { logpipe write --channel $(basename $0) --error   "$1"; }

Then source it into your script:

    #!/bin/bash
    source logpipe.inc
    log.info "Processing foo"
    
