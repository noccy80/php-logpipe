Relay Plugin for LogPipe
========================

This plugin adds a `relay` command to LogPipe.

## Usage

To enable the plugin:

    $ logpipe plugins --enable logpipe.relay

With an application suite configured to log over `tcp:127.0.0.1:9876`:

    $ logpipe relay --endpoint tcp:127.0.0.1:9876 tcp:127.0.0.1:9875 tcp:127.0.0.1:9874

The messages dumped by the applications over port 9876 will now also be available for dumping
from port 9875 and 9874
