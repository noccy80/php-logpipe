LogPipe
=======

LogPipe is a monolog handler, an application and related libraries to pass and display log messages in real time by
sending them over udp or over a named pipe in case there is a dumper listening. If no dumper is listening on the
specified transport, the messages are simply discarded. 

LogPipe is designed to have a minimal impact on performance, and as such it will discard events if it encounters any
issues while sending it.

## Using on command line

To start (listening for and) dumping events on the default port (`udp:127.0.0.1:6999`) just use the **dump** command:

    $ bin/logpipe dump

You can create some test events by using the **test** command in another terminal while the **dump** command is
running:

    $ bin/logpipe test


## Using with Symfony

To use LogPipe with Symfony you only need to register the handler as a service so that it can be used with Monolog.
This is preferably done in the `config_dev.yml` file. If there is a `services:` block, add the sections to it, otherwise
create it:

    services:
        logpipe.handler:
            class:      NoccyLabs\LogPipe\Handler\LogPipeHandler
            arguments:  [ "udp:127.0.0.1:6999" ]

Then define the handler in the same file. By doing it in `config_dev.yml`, your live environment will not use the
LogPipe logger.

    monolog:
        handlers:
            ...
            logpipe:
                type:   service
                id:     logpipe.handler

## Transports

### UDP

The default transport is over UDP port 6999. Messages sent over UDP are tagged with a 6-byte header specifying size
and crc32 of the payload. Messages are serialized, transmitted, and once fully received and with a valid checksum
unserialized and parsed.

**Transport URI:**

    udp:<host>:<port>


### Pipe

The pipe transport is the default when no colon is found in the transport URI. Thus, `/var/run/foo.sock` will be
internally translated to `pipe:/var/run/foo.sock`. The *listen()* method will create the named pipe and start
listening for connections.

 -  **Note:** Only use the pipe transport if you really have to. Concurrency might be an issue, as well as some
    unexpected blocking issues. Instead use the udp transport.

**Transport URI:**

    pipe:<path>