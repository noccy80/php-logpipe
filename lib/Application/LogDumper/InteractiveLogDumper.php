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
        "buffer.size"   => 1000,
        "output.wrap"   => 1,
        "output.title"  => 0
    ];
    
    protected $status_line;

    /**
     * @param $options
     */
    public function __construct(LogPipeApplication $app, $options)
    {
        parent::__construct($app);
        $this->options = array_merge($this->options, $options);
        $this->buffer = new FifoBuffer($this->getOption("buffer.size"));
        $this->status_line = new Helper\StatusLine();
        $this->status_line->setStyle("44;37");
        $this->status_line
            ->addPanel([ $this, "getTotalPanel" ])
            ->addPanel([ $this, "getSquelchPanel" ])
            ->addPanel([ $this, "getDebugPanel" ])
            ->addPanel([ $this, "getInfoPanel" ])
            ;
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

        while (!$signal()) {
            if ($this->getOption("output.title")) { $this->updateTitle(); }

            $msg = $this->transport->receive();
            if ($msg) {
                $this->status_line->erase();
                $this->onMessage($msg);
            }

            if (!$this->handleInput($input)) {
                break;
            }

            if ($break_at && ($break_at < time())) {
                break;
            }

            if (!$msg) {
                $this->status_line->update();
                usleep(10000);
            }
        }
        
        $this->status_line->erase();
    }
    
    public function getSquelchPanel()
    {
        return [ Helper\Unicode::char(0x26D5). " " . $this->squelched, "30;43" ];
    }

    public function getTotalPanel()
    {
        return [ Helper\Unicode::char(0x27F3). " " . $this->buffer->getTotal(), "32;1" ];
    }
        
    public function getDebugPanel()
    {
        $load = sys_getloadavg();
        $blobs = [
            Helper\Unicode::char(0x26A1) => sprintf("%.2f", $load[0]),
            Helper\Unicode::char(0x26c3) => sprintf("%.2fKiB", memory_get_usage(true)/1024)
        ];
        $state = ($load[0]<0.7)?"42;37":"41;37";
        $text = [];
        foreach ($blobs as $k=>$v) {
            $text[] = sprintf("%s \e[1m%s\e[21m", $k, $v);
        }
        $text = join(" \e[34m/\e[37m ",$text);
        
        return [ $text, $state ];
    }
    
    public function getInfoPanel()
    {
        return [ "Press \e[1m:\e[21m for command mode, and \e[1m/\e[21m for search mode. \e[1mq\e[21m or \e[1m^C\e[21m exits", "37" ];
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
                $this->status_line->erase();
                $line = $input->readLine(":");
                $this->evalDumperCommand($line);
                break;
            case '/':
                $this->status_line->erase();
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

    /**
     *
     */
    protected function updateTitle()
    {
        $received = $this->buffer->getTotal();
        echo "\e]0;LogPipe [{$received}]\x07";
    }

}
