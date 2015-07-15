Metrics Plugin for LogPipe
==========================



## Sending metrics to LogPipe

There are two ways to do this:

 1. Tag the message with "metric" and provide the metrics as userdata "metric"
 2. Prefix the message body with !metric and provide the metrics as a string
 
 
### Using tags

Example:

        function createMetricValue($key, $value) {
            $message = new Message();
            $message->addTag("metric");
            $message->setData("metric", [
                "key" => $key,
                "value" => $value,
            ]);
            return $message;
        }
        
        function createMetricHit($key) {
            $message = new Message();
            $message->addTag("metric");
            $message->setData("metric", [
                "key" => $key,
                "type" => "incr"
            ]);
            return $message;
        }
        
        $logpipe->push(createMetricHit("page.hit"));
        $logpipe->push(createMetricValue("rendertime", [ .. ]));

Example using Monolog:

        $this->logger->debug("metric. text is not important.", [
            "tag" => [ "metric" ],
            "metric" => [ "key" => "page.hit", "type" => "incr" ]
        ]);

