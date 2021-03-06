<?php


namespace NoccyLabs\LogPipe\Dumper\Formatter;

use NoccyLabs\LogPipe\Message\MessageInterface;

/**
 * Class Formatter
 * @package NoccyLabs\LogPipe\Dumper
 */
class Formatter {

    /**
     * @var
     */
    protected $message_style;

    public function __construct($message_style=null)
    {
        $this->setMessageStyle($message_style);
    }

    /**
     * @param $style
     * @return $this
     */
    public function setMessageStyle($style)
    {
        $this->message_style = $style;
        return $this;
    }

    /**
     * @param MessageInterface $message
     * @return MessageInterface|string
     */
    public function format(MessageInterface $message)
    {

        $message = $this->doFormat($this->message_style, $message);
        return $message;

    }

    /**
     * @param $style
     * @param MessageInterface $message
     * @return string
     */
    protected function doFormat($style, MessageInterface $message)
    {
        if (is_callable($style)) {
            $style = call_user_func($style, $message);
        }
        $ret = null;
        $output = [];
        $text = $message->getMessage();
        if (strpos($style,"|")!==false) {
            list ($pre, $post) = explode("|", $style);
            foreach (explode("\n", $text) as $line)
                $output[] = $pre . $line . $post;
        } elseif (preg_match("/^<(.*)>$/", $style, $ret)) {
            foreach (explode("\n", $text) as $line)
                $output[] = sprintf("<%s>%s</%s>", $ret[1], $line, $ret[1]);
        } else {
            return $text;
        }
        return join("\n",$output);
    }

}
