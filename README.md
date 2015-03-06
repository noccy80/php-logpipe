LogPipe
=======

## Using on command line

    $ bin/logpipe dump



## Using with Symfony

You need to define the LogPipeHandler as a service so that it can be used with Monolog:

    services:
        logpipe.handler:
            class:      NoccyLabs\LogPipe\Handler\LogPipeHandler
            arguments:  [ "udp:127.0.0.1:6999" ]

Then define the handler:

    monolog:
        handlers:
            ...
            logpipe:
                type:   service
                id:     logpipe.handler

