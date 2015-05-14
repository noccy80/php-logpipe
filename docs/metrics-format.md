Metrics DataStream Format
=========================

The `DataStream` format is a simple line-oriented format to store metrics data for later processing.
Data is streamed into the file, and afterwards streamed from it for aggregation and statistics
generation.

## Line format

Every record begins with an `@`, followed by the client ID (referred to as `session` in the stream).
Directly following is a hash-sign (`#`) and the metric key. The data follows after an equals-sign (`=`),
serialized as json.

        @www:52b07d2c#page.hit={"route":"homepage"}
       '------.------'----.---'--------.----------'
              |           |            |
         Session ID       |          Data
                     Metric key
