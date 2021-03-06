<?php


namespace NoccyLabs\LogPipe\Application\LogDumper;

use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Posix\SignalListener;
use NoccyLabs\LogPipe\Transport\TransportInterface;
use NoccyLabs\LogPipe\Dumper\Filter\FilterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use NoccyLabs\LogPipe\Application\Console\CharacterInput;
use NoccyLabs\LogPipe\Common\FifoBuffer;
use NoccyLabs\LogPipe\Application\LogPipeApplication;

class InteractiveLogDumper extends LogDumper
{

    /**
     * @var FifoBuffer
     */
    protected $buffer;

    /**
     * @var array
     */
    protected $options = [
        "buffer.size"   => 100,
        "output.wrap"   => 1,
        "output.title"  => 0
    ];
    
    /**
     * @param $options
     */
    public function __construct(LogPipeApplication $app, $options)
    {
        parent::__construct($app);
        $this->options = array_merge($this->options, $options);
        $this->buffer = new FifoBuffer($this->getOption("buffer.size"));
    }

    /**
     *
     */
    public function run()
    {
        $signal = new SignalListener(SIGINT);

        $input  = new CharacterInput(true);

        declare(ticks=5);

        $this->squelched = 0;

        $break_at = ($this->timeout) ? time()+$this->timeout : null;

        $this->eventDispatcher->dispatch(DumperEvent::DUMPING, new DumperEvent());
        
        $inBatch = false;
        $ticks = 0;

        while (!$signal()) {
            if ($this->getOption("output.title")) { $this->updateTitle(); }

            $msg = $this->transport->receive();
            if ($msg) {
                if (!$inBatch) {
                    $inBatch = true;
                    $this->eventDispatcher->dispatch(DumperEvent::BEFORE_BATCH, new DumperEvent());
                }
                $this->onMessage($msg);
            } else {
                if ($inBatch) {
                    $inBatch = false;
                    $this->eventDispatcher->dispatch(DumperEvent::AFTER_BATCH, new DumperEvent());
                } else {
                    if (microtime(true)>$ticks) {
                        $this->eventDispatcher->dispatch(DumperEvent::IDLE_REFRESH, new DumperEvent());
                        $ticks = microtime(true)+1;
                    }
                }
                
            }

            if (!$this->handleInput($input)) {
                break;
            }

            if ($break_at && ($break_at < time())) {
                break;
            }

            if (!$msg) {
                usleep(25000);
            } else {
                usleep(1000);
            }
        }
        
        $this->eventDispatcher->dispatch(DumperEvent::TERMINATING, new DumperEvent());
    }
    
    protected function onMessage(MessageInterface $msg)
    {
        $this->buffer->push($msg);
        parent::onMessage($msg);
    }

    protected function handleInput($input)
    {
        $ch = $input->readChar();
        switch ($ch) {
            case chr(27):
            case 'q': // quiet
                return false;
            case 'f': // freeze
                // dump buffer to file and invoke pager
                return true;
            case ':':
                $this->eventDispatcher->dispatch(DumperEvent::SUSPEND, new DumperEvent());
                $line = $input->readLine(":");
                $this->evalDumperCommand($line);
                $this->eventDispatcher->dispatch(DumperEvent::DUMPING, new DumperEvent());
                break;
            case '/':
                $this->eventDispatcher->dispatch(DumperEvent::SUSPEND, new DumperEvent());
                $find = $input->readLine("/");
                if ($find) {
                    if (strpos($find,"/") === false) {
                        $find = "/{$find}/";
                    } elseif ($find[0] != "/") {
                        $find = "/{$find}";
                    }
                    $this->output->writeln("<info>preg_match(</info><comment>{$find}</comment></info>)</info>:");
                    $items = $this->buffer->match($find);
                    foreach ($items as $item) { $this->dumper->dump($item); }
                }
                $this->eventDispatcher->dispatch(DumperEvent::DUMPING, new DumperEvent());
                break;
        }
        return true;

    }

    /**
     * @param $command
     */
    protected function evalDumperCommand($command)
    {
        $args = (array)str_getcsv($command," ");
        $cmd = array_shift($args);
        switch ($cmd) {
            case 'help':
                break;
            case 'set':
                if (count($args) > 1) {
                    $this->setOption($args[0], $args[1]);
                } elseif (count($args) > 0) {
                    $this->dumpOption($args[0]);
                } else {
                    $this->dumpOption();
                }
                break;
        }
    }

    /**
     * @param null $key
     */
    public function dumpOption($key=null)
    {
        if (null == $key) {
            foreach ($this->options as $key=>$value) {
                $this->dumpOption($key);
            }
        } else {
            if (!array_key_exists($key, $this->options)) {
                $this->output->write("<error>No such option: {$key}</error>");
                return;
            }
            $value = $this->options[$key];
            $this->output->write("<options=bold>{$key}</options=bold> = '{$value}'\n");
        }
    }

    /**
     * @param $key
     * @param $value
     */
    public function setOption($key, $value)
    {
        switch ($key) {
            case 'buffer.size':
                $this->buffer = new FifoBuffer($value);
                break;
        }
        $this->options[$key] = $value;
    }

    /**
     * @param $key
     */
    public function getOption($key)
    {
        if (!array_key_exists($key, $this->options)) {
            $this->output->write("<error>No such option: {$key}</error>");
            return null;
        }
        return $this->options[$key];
    }

    
}
