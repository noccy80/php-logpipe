<?php


namespace NoccyLabs\LogPipe\Application\LogDumper;

use NoccyLabs\LogPipe\Message\MessageInterface;
use NoccyLabs\LogPipe\Posix\SignalListener;
use NoccyLabs\LogPipe\Transport\TransportInterface;
use NoccyLabs\LogPipe\Filter\FilterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use NoccyLabs\LogPipe\Application\Console\CharacterInput;
use NoccyLabs\LogPipe\Application\Buffer\FifoBuffer;

class InteractiveLogDumper extends LogDumper
{

    /**
     *
     */
    public function run()
    {
        $signal = new SignalListener(SIGINT);

        $input  = new CharacterInput(true);

        declare(ticks=5);

        $this->squelched = 0;

        if ($this->getOption("output.title")) { $this->updateTitle(); }

        while (!$signal()) {

            $msg = $this->transport->receive();
            if ($msg) {
                $this->onMessage($msg);
            }
            if (!$this->handleInput($input)) {
                break;
            }
            usleep(10000);
        }
    }

    protected function handleInput($input)
    {
        $ch = $input->readChar();
        switch ($ch) {
            case chr(27):
            case 'q':
                return false;
            case ':':
                $line = $input->readLine(":");
                $this->evalDumperCommand($line);
                break;
            case '/':
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

}
