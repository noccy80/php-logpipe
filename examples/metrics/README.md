Metrics example for LogPipe: Primes
===================================

This example consist of a few different parts:

 *  `primecalc.php` calculates primes, while logging via monolog and tracing statistics over logpipe.
 *  `primedump.php` dumps the primes calculated.
 *  `primes.def` is the statistics parsing definition for the prime calculation
 *  `run.sh` will run through the whole process of calculating a bunch of primes and collecting the
    numbers afterwards.

To test it all out; 

 1. Start the `run.sh` script
 2. Let it run until you see the message about pressing Ctrl-C
 3. Enjoy the parsed output
