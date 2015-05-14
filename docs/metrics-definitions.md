Metrics Definitions
===================

A metrics definition defines how to transform metrics from a log. The output is a yaml files containing
the processed metrics.

## Definitions

The definitions are created using yaml and specify aggreations and transformations to perform
on the data.

### Metrics for examples

    session     key            value
    ----------- -------------- ------
    www:123     page.hit       { route: login }
    www:123     login.timer    173.9
    www:124     page.hit       { route: dashboard }

### Example definitions

    page.hit:
        group:
            by: route
    login.timer:
        aggregate:
            values: true
            stats: true

### Example output

    page.hit:
        login:
            count: 5
            percent: 25
            group: [ { route:login }, { route:login }, ...
        dashboard:
            count:15
            percent:75
            group: [ { route:dashboard }, ...
    login.timer:
        values:
            - 159.9
            - 198.4
            - 166.1
            - 173.9
            - 191.1
        count: 5
        total: 769.4
        average: 166.1
        max: 198.4
        min: 159.9

## Reference

Each definition consist of a key (normally the key to match in the metrics), and a body:

    <key-name>:
        <body>

The body defines the operations to perform on the data:


### Grouping and Sorting

    group: { by: <data-key>, values: <bool> }
    sort: [ <sort-key[:<order>]>, ... ]

**Example:**

    page.hit:
        group:
            by: route
        sort:
            - route:asc

### Including values

    values: [ <data-key>, ... ]

**Example:**

    page.hit:
        values: [ route, timer, status ]

### Aggregating stats

    aggregate: { <operation>: <operation-params>, ... }

**Example:**

    page.hit:
        aggregate:
            stats: [ timer ]


