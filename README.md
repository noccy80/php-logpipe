LogPipe
=======

![Build status](https://travis-ci.org/noccy80/php-logpipe.svg?branch=master)

LogPipe is a library and an application to monitor PHP applications and monolog logs in real time. This is done by
serializing the data and sending the serialized blobs over one of the supported transports. The transport doesn't
need to have a listener in the other end; LogPipe is designed to fail quietly without affecting your application.
Once you want to get an insight into what is going on, fire up the dumper.

LogPipe is designed to have a minimal impact on performance, and as such it will discard events if it encounters any
issues while sending it.


## Installing

To install into a project using composer:

    $ composer require noccylabs/logpipe:@stable

Install globally for using with shell scripts etc:

    $ composer global require noccylabs/logpipe:@stable


## Using LogPipe


### Using with Monolog

To use with Monolog, push a `LogPipeHandler` onto your `Logger`.


### Using with Symfony

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


### Using elsewhere

LogPipe can be set up to automatically log exceptions and errors:

    use NoccyLabs\LogPipe\Handler\ConsoleHandler;

    $handler = new ConsoleHandler("tcp:127.0.0.1:9999:serializer=json");
    $handler->setExceptionReporting(true);
    $handler->setErrorReporting(true);

You can also write events manually: **(not implemented)**

    $handler->debug("This is a debug message!");
    $handler->warning("Danger! Danger!");


### Dumping events

To start (listening for and) dumping events on the default port (`tcp:127.0.0.1:6601`) just use the **dump** command:

    $ bin/logpipe dump

You can also specify to explicityly listen on a specific transport by providing it as an argument.

    $ bin/logpipe dump tcp:0.0.0.0:9999

You can create some test events by using the **test** command in another terminal while the **dump** command is
running:

    $ bin/logpipe test

To save the log while viewing it, try using tee:

    $ bin/logpipe dump --no-ansi | tee messages.log

You can also write events from the console, or from scripts:

    $ bin/logpipe write -c "cron" --error "Setup failed"

Or pass events straight through from stdin:

    $ some_command | bin/logpipe log:pass 

## Configuration and connection strings

The connection is set up using a simple connection string, consisting of the desired transport and any parameters
needed to set it up separated by colon (`:`). Additional configuration can be added in HTML query-string style after
the last parameter:

    tcp:127.0.0.1:12345:serializer=json


### Transports

#### UDP

    udp:<host>:<port>

The default transport is over UDP port 6999. Messages sent over UDP are tagged with a 6-byte header specifying size
and crc32 of the payload. Messages are serialized, transmitted, and once fully received and with a valid checksum
unserialized and parsed. Note that due to how UDP works, if you spawn another dumper on the same port, the first one
will stop receiving data without indicating an error.

#### TCP

    tcp:<host>:<port>

The TCP transport works kinda like the UDP transport. However, since TCP is connection-oriented some complications
may occur if no dumper is available. This needs more testing. It should however be able to handle bigger messages.

#### Pipe

    pipe:<path>

The pipe transport is the default when no colon is found in the transport URI. Thus, `/var/run/foo.sock` will be
internally translated to `pipe:/var/run/foo.sock`. The *listen()* method will create the named pipe and start
listening for connections. Only use the pipe transport if you really have to. Concurrency might be an issue, as well 
as some unexpected blocking issues.


### Serializers

What serializer is used is set in the sending transport. The serialization format is embedded in the message frame
(together with checksum, size and flags) to that the appropriate unserializer can be invoked. The supported
serializers are:

 -  `php`: The built-in PHP serializer
 -  `json`: Uses Json to serialize the data
 -  `msgpack`: Like binary json, should result in smaller messages.

To use a custom serializer, provide it with the endpoint URI: `udp:127.0.0.1:6999:serializer=msgpack` etc.
As the policy is *fail and forget*, you will not receive any errors if the serializer is not supported. Calling
on a non-existing serializer will throw an exception.


## The Interactive Dumper

When launching the dumper in interactive mode (by passing `-i` or `--interactive`) some additional tools are available.

The last bunch of messages (normally 1000, but can be set with `-Cbuffer.size=N` on the command line or `:set buffer.size N`
while in the dumper) are stored in a buffer. You can at any time search this buffer and dump any matches. To do this, just
press slash (`/`) and start typing. The input will be parsed as a regular expression, so you can add modifiers to the end:

    /exception/i  <- will perform a case independent match

Currently the only supported command is `set`, but you can go ahead and invoke it by pressing colon (`:`) while in the
dumper:

    :set                  <- list all settings
    :set buffer.size      <- show the value of buffer.size
    :set buffer.size 999  <- set buffer size to 999


## Frequently Asked Questions (FAQ)

**Q: I can't see all logged messages!!!**

LogPipe will fail quietly if anything goes wrong. This includes serialization of the log
event, transport errors and more. This is done so that a problematic logger or transport
will not cause the application being diagnosed to misbehave.

**Q: LogPipe is causing my application to misbehave!**

Please report this ASAP, unless you are able to fix the issue and commit a pull request.
As previously mentioned, the strategy is *fail and forget*, meaning that ALL AND ANY errors
that occur should be silently consumed, as to prevent the application from failing or 
misbehaving due to an auxillary logger.

**Q: The interactive mode doesn't work!**

LogPipe uses Stty to switch the terminal from line-buffered mode to raw character mode in
order to implement custom readline functionality. In the long run, this will mean that you can
enter commands or filter expressions while the log keep updating, but today it means that certain
platforms may encounter issues.


## Version history

    [!] Improvement  [*] Milestone  [+] New feature  [-] Removed feature

    0.2.5   [!] Reduced memory load when using the standard (non-interactive) dumper by moving
                the buffer and configuration logic to the interactive dumper.
            [+] Exceptions are now unwrapped and displayed in a more useful format.
            [!] Dumping should now lag less when a lot of data is received
            [+] Added --timeout option to dump command to limit execution time for scripting
            [+] Implemented metrics logging in the dumper (not yet in master)
    0.2.4   [!] CPU load during dumping reducded
    0.2.3   [*] The PipeV1Protocol header increased to 16 bytes.
            [+] Separated the standard dumper and the interactive dumper.
            [!] Added lots of phpdoc comments, and code cleanup.
    0.2.2   [!] Increased message header size to prevent large messages from creating corrupted
                payloads.
    0.2.1   [+] Implemented fifo-buffer to hold last N requests for quick searching.
            [+] Both Escape and Q can now be used to exit the dumper while in interactive mode.
            [*] TCP is now the default transport (tcp:127.0.0.1:6601)
    0.2     [!] Data encapsulation stuff handled by PipeV1Protocol class.
            [!] Pipe transport considered fully functional.
    0.1.5   [+] Added more unit tests.
    0.1.3   [+] Added serializers.
            [!] Updated message- and transport structure.
            [!] Improved console commands.
    0.1.2   [!] TCP transport considered functional.
            [+] Added support for using the envvar APP_ID, or the define() APP_ID to specify
                the prefix to use. Setting neither will use the hostname as app id.
    0.1     [*] Initial release.
