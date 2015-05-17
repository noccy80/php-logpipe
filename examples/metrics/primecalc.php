<?php

require_once __DIR__."/../../vendor/autoload.php";

$options = [
    "transport" => "tcp:127.0.0.1:9999",
    "threads" => 1,
    "limit" => 1000
];
foreach ($argv as $arg) {
    if (strpos($arg,"=")!==false) {
        list($k,$v) = explode("=",$arg,2);
        $options[$k] = $v;
    }
}

function createLogger($name) {
    $logger = new Monolog\Logger($name);
    $logger->pushHandler(createHandler());
    return $logger;
}

function createHandler() {
    global $options;
    return new NoccyLabs\LogPipe\Handler\LogPipeHandler($options['transport']);
}

class ForkWaiter {
    private $pids = [];
    public function addChild($pid) {
        $this->pids[$pid] = true;
    }
    public function wait() {
        while (count($this->pids) > 0) {
            $status = null;
            $pid = pcntl_waitpid(-1, $status);
            unset($this->pids[$pid]);
            usleep(10000);
        }
    }
}

function threadMain($start, $end, $thread=0) {
    $logger = createLogger("thread{$thread}");
    $logger->debug("Warming up thread {$thread}");

    $primes = [];

    for ($test = $start; $test <= $end; $test++) {
        $found = true;
        for ($p = 2; $p < $test; $p++) {
            if ($test % $p == 0) {
                $found = false;
                break;
            }
        }
        if ($found == true) {
            $primes[] = $test;
            echo $test."\n";
            $logger->info("!metric.item prime.found {$test}");
        }
    }

    $logger->debug("Thread {$thread} done: found ".count($primes)." primes");
}

$logger = createLogger("main");
$logger->info("Starting main thread...", $options);

$threads = min(8,max(1,intval($options['threads'])));

$limit = max(1000,intval($options['limit']));
$split = ceil($limit/$threads);

if ($threads == 1) {
    threadMain(1,$limit);
} else {
    $start = 1; $end = $split;
    $waiter = new ForkWaiter();
    for ($n = 1; $n <= $threads; $n++) {
        if ($end > $limit) { $end = $limit - $start; }
        $logger->info("Spawning worker thread {$n} for [{$start}..{$end}]");
        $PID = pcntl_fork();
        if ($PID === 0) {
            threadMain($start, $end, $n);
            return;
        } else {
            $logger->info(" -> Worker running with PID {$PID}");
            $waiter->addChild($PID);
        }
        $start += $split;
        $end += $split;
    }
    $waiter->wait();
    $logger->info("You can now press Ctrl-C in the dumper!");
}
