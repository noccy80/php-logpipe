<?php


namespace NoccyLabs\LogPipe\Dumper;

use NoccyLabs\LogPipe\Message\MessageInterface;

class Formatter {

    protected $message_style;

    public function setMessageStyle($style)
    {
        $this->message_style = $style;
        return $this;
    }

    public function format(MessageInterface $message)
    {

        $message = $this->doFormat($this->message_style, $message);
        return $message;

    }

    protected function doFormat($style, MessageInterface $message)
    {
        if (is_callable($style)) {
            $style = call_user_func($style, $message);
        }
        $ret = null;
        if (strpos($style,"|")!==false) {
            list ($pre, $post) = explode("|", $style);
            $output = $pre . $message->getMessage() . $post;
        } elseif (preg_match("/^<(.*)>$/", $style, $ret)) {
            $output = sprintf("<%s>%s</%s>", $ret[1], $message->getMessage(), $ret[1]);
        } else {
            $output = $message->getMessage();
        }
        return $output;
    }

}
