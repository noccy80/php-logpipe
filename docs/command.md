LogPipe: CLI Command Reference
==============================












## Dump command

This command will dump all incoming events on a specific transport.

**Command:**

        logpipe dump [options] [filters] [endpoint]

**Options:**

        --no-squelch,-n     Don't show a notification when a message is squelched due to filters
        --

**Filters:**

        --level,-l          Filter by level (only show event at or above the specified level)
        --channels,-c       Only show specified channels
        --exclude,-x        Exclude the specified channels

## Test command

**Command:**

        logpipe test [options] [endpoint]

**Options:**

        --serializer,-s     Specify the serializer (php, json, msgpack)
