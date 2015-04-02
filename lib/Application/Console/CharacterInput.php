<?php

namespace NoccyLabs\LogPipe\Application\Console;

class CharacterInput
{
    const SEQ_CLEAR_LINE = "\r\e[J";

    protected $enabled;

    protected $stty;

    public function __construct($enable=true)
    {
        $this->stty = new Stty();
        $this->enable($enable);
    }

    public function enable($enable)
    {
        if ($enable && (!$this->enabled)) {
            $this->stty->set("-echo cbreak");
            stream_set_blocking(STDIN, false);
        } elseif ($this->enabled && (!$enable)) {
            echo exec('stty +echo -cbreak');
            $this->stty->reset();
            stream_set_blocking(STDIN, true);
        }
    }

    public function __destruct()
    {
        $this->enable(false);
    }

    public function readChar()
    {
        static $buffer;
        $buffer .= fread(STDIN,8192);
        if (strlen($buffer) > 0) {
            if ($buffer[0] == chr(27)) {
                $char = substr($buffer, 0, 3);
                $buffer = substr($buffer, 3);
            } else {
                $char = $buffer[0];
                $buffer = substr($buffer, 1);
            }
            return $char;
        }
        return NULL;
    }

    public function readLine($prompt, array $history=null, callable $tick_callback=null)
    {
        $input = null;
        $cursor = 0;
        $history = array_reverse((array)$history);
        $history_index = null;
        $redraw = true;

        while (true) {
            if ($redraw) {
                echo self::SEQ_CLEAR_LINE . $prompt . $input;
                $pos = strlen($prompt) + $cursor + 1;
                echo "\e[{$pos}G";
                $redraw = false;
            }
            $ch = $this->readChar();
            if ($ch !== null) {
                switch ($ch) {
                    case "\e[A": // up
                        break;
                    case "\e[B": // down
                        break;
                    case "\e[C": // right
                        if ($cursor < strlen($input)) { $cursor++; $redraw=true; }
                        break;
                    case "\e[D": // left
                        if ($cursor > 0) { $cursor--; $redraw=true; }
                        break;
                    case "\x0A":
                        break(2);
                    case chr(27):
                        $input = null;
                        break(2);
                    case chr(8):
                    case chr(127):
                        if ($cursor > 0) {
                            $input = substr($input, 0, $cursor-1) . substr($input, $cursor--);
                            $redraw = true;
                        }
                        break;
                    default:
                        if ((ord($ch) < 32) || (ord($ch) > 127)) {
                            break;
                        }
                        if ($cursor < strlen($input)) {
                            $input = substr($input,0,$cursor) . $ch . substr($input, $cursor++);
                        } else {
                            $input.= $ch;
                            $cursor = strlen($input);
                        }
                        $redraw = true;
                }
            }
            usleep(1000);
        }

        echo self::SEQ_CLEAR_LINE;
        return $input;
    }

}
