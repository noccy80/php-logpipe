<?php

namespace NoccyLabs\LogPipe\Application\LogDumper\Helper;

class StatusLine
{
    protected $visible = false;

    protected $panels = [];

    protected $buffer = [];

    protected $last = [];
    
    protected $style;

    public function addPanel(callable $panel)
    {
        $this->panels[] = $panel;
        return $this;
    }

    public function setStyle($style)
    {
        $this->style = $style;
    }

    public function erase()
    {
        if (!$this->visible) { return; }
        $this->visible = false;
        $this->write("\r\e[K\e[?25h");
    }

    public function update()
    {
        static $lastCall;
        $thisCall = microtime(true);
        if ($this->visible && (($thisCall-$lastCall)<.1)) {
            return;
        }
        $lastCall = $thisCall;
        
        $this->last = $this->buffer;
        $updated = !$this->visible;
        $this->visible = true;
        foreach ($this->panels as $n => $panel) {
            $this->buffer[$n] = call_user_func($panel);
            if (!array_key_exists($n, $this->last)) {
                $updated = true;
            } elseif ($this->buffer[$n] != $this->last[$n]) {
                $updated = true;
            }
        }
        if (!$updated) {
            return;
        }
        $this->write("\r\e[{$this->style}m\e[K");
        foreach ($this->buffer as $panel) {
            if (is_array($panel)) {
                list($panel, $panel_style) = $panel;
            } else {
                $panel_style = "37";
            }
            $this->write("\e[{$panel_style}m {$panel} \e[0;{$this->style}m");
        }
        $this->write("\e[0m\r\e[?25l");
    }

    public function write($str)
    {
        echo $str;
    }

}
