<?php

namespace NoccyLabs\LogPipe\Dumper;

use NoccyLabs\LogPipe\Message\MessageInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConsoleDumper
 * @package NoccyLabs\LogPipe\Dumper
 */
class ConsoleDumper extends AbstractDumper
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Formatter
     */
    protected $formatter;

    /**
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->formatter = new Formatter();
        $this->formatter->setMessageStyle(array($this,"getRecordMarkup"));
    }

    /**
     * @param MessageInterface $message
     */
    public function dump(MessageInterface $message)
    {
        $client     = $message->getClientId();

        $message = $this->decode($message);
        if (!$message) {
            return;
        }

        $output = $this->formatter->format($message);
        $output = str_replace("\n", "\n".str_repeat(" ",strlen($client)+1),$output);

        $this->output->writeln(
            sprintf("%s %s", $client, $output)
        );
    }

    /**
     * @param $message
     * @return string
     */
    public function getRecordMarkup($message)
    {
        $level      = $message->getLevel();

        if ($level < 200) {
            $style = "<fg=green>";
        } elseif ($level < 300) {
            $style = "<fg=green;options=bold>";
        } elseif ($level < 400) {
            $style = "<fg=yellow;options=bold>";
        } elseif ($level < 500) {
            $style = "<fg=red;options=bold>";
        } elseif ($level < 550) {
            $style = "<fg=yellow;bg=red>";
        } elseif ($level < 600) {
            $style = "<fg=yellow;bg=red;options=bold>";
        } else {
            $style = "<fg=red;bg=yellow;options=bold>";
        }

        return $style;

    }
}
