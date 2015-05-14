




Automating metrics collection:

*dump-metrics.sh*

    #!/bin/bash

    ENDPOINT="tcp:0.0.0.0:6601"
    SNAPSHOT_DIR="/var/metrics/snapshot"

    SNAPSHOT="${SNAPSHOT_DIR}/metric-$(date +%F-%H)"
    # This will dump silently for 1 hour
    logpipe dump --timeout 3600 -m "${SNAPSHOT}.msd" -q

*daily-metrics.sh*

    #!/bin/bash

    SNAPSHOT_DIR="/var/metrics/snapshot"
    DAILY_DIR="/var/metrics/daily"

    DAILY="${DAILY_DIR}/daily-$(date +%F)"
    DEFINITION="/var/metrics/metrics.def"

    # Process the output and delete the .msd file on success
    logpipe metrics:dump -d ${DEFINITION} -i "${SNAPSHOT_DIR}/*.msd" -o "${DAILY}.yml" --clean-up
    logpipe metrics:html -i "${DAILY}.yml" -o "${DAILY}.html"

Then, add to your crontab:

    */1 0 0 0 0 /path/to/dump-metrics.sh
    0 0 0 0 0 /path/to/daily-metrics.sh

You will now have the following:

    /var
      /metrics
        /snapshot
          snapshot-2015-06-07-08.msd        <- Metrics being captured for hour 8 of the 7th of June
          snapshot-2015-06-07-07.msd        <- Previously captured metric
          ..
        /daily
          daily-2015-06-06.yml              <- Aggregated stats for the 6th of June
          daily-2015-06-06.html             <- HTML report with aggregated stats
