#!/bin/bash

( sleep 2; php primecalc.php threads=4 limit=50000 transport=tcp:127.0.0.1:9999 > primes.num ) &

logpipe dump -m primes.met tcp:127.0.0.1:9999

logpipe metrics:dump -d primes.def -f primes.met > primes.yml

cat primes.yml
