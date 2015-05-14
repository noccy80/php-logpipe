Metrics Definitions
===================

A metrics definition defines how to transform metrics from a log. The output is a yaml files containing
the processed metrics.

## Definitions

The definitions are created using yaml.

### Metrics for examples

    session     key            value
    ----------- -------------- ------
    www:123     page.hit       { route: login }
    www:123     login.timer    173.9
    www:124     page.hit       { route: dashboard }

### Example definitions

    page.hit:
        aggregate: { group:route }
    login.timer:
        aggregate: { stats:true, values:true }

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


