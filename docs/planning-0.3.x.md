LogPipe Planing: 0.3.x
======================




## Metrics

### Through Monolog

    $logger->info("metrics.hit", [ "client_records.created" ]);

### Through Metrics

    $metrics = $container->get("logpipe.metrics");
    $metrics->hit("client_records.created");
    
### Dumping

While dumping log output:

    $ logpipe dump --trace-metrics trace.db

To process:

    $ logpipe metrics:dump trace.db



## Interactive Dumper

### New commands

#### :save

This command should save the entire fifo buffer to disk, by passing it through the streamdumper.

    :save dump.txt


#### :tee

Start logging displayed events to a file

    :tee dump.txt


#### :level and :channel

This command should control the filter, clear the screen, and output the buffer through the filter
again.

The **level** command sets the minimum level of the messages to display. The parameter can be either
the level name (`debug`, `info`, `warning` etc) or the numeric level (`100`, `200` etc):

    :level debug            set the threshold level to debug
    :level 100              set the threshold level to debug

The **channel** command selects what channels are dumped. If the first letter in the parameter is a
plus- or a minus sign, it is interpreted as include or exclude the specific channel. Otherwise, the
default is to include the parameter. Thus, `*` and `+*` are equivalent, as are `foo` and `+foo`.

    :channel -event         hide channel event
    :channel +event         show channel event
    :channel -*             hide all channels
    :channel +*             show all channels
    :channel *              show all channels
    :channel                list filtered channels


### Processors

 * Parse out f.ex. json and xml, to tidy or pretty-dump.
